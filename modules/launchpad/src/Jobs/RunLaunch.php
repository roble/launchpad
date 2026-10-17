<?php

namespace Modules\Launchpad\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Modules\Launchpad\Models\Launch;

/**
 * Builds, provisions, and deploys one launch by running the Saucebase, Stripe Projects,
 * and Laravel Cloud CLIs in order, stopping at the first failing step.
 */
class RunLaunch implements ShouldQueue
{
    use Queueable;

    public int $timeout = 1800;

    public int $tries = 1;

    public function __construct(public Launch $launch) {}

    public function handle(): void
    {
        $launch = $this->launch;
        File::ensureDirectoryExists(dirname($launch->workspacePath()));
        $launch->forceFill(['status' => 'running', 'log' => ''])->save();

        if (config('launchpad.fake')) {
            $this->simulate();
        }

        foreach ($this->steps() as $label => [$command, $path]) {
            $launch->forceFill(['step' => $label, 'log' => $launch->log."▶ {$label}\n$ {$command}\n"])->save();

            $result = Process::path($path)->timeout($this->timeout)->run($command);
            $launch->forceFill(['log' => $launch->log.$result->output().$result->errorOutput()."\n"])->save();

            if ($result->failed()) {
                $launch->forceFill(['status' => 'failed'])->save();

                return;
            }
        }

        // ponytail: assumes Laravel Cloud's default hostname; read it from `cloud ship --json`
        // once the first real deploy shows the output shape.
        $launch->forceFill(['status' => 'live', 'step' => null, 'url' => "https://{$launch->slug}.laravel.cloud"])->save();
    }

    /** Demo mode: every command "succeeds" after a pause, and nothing real runs. */
    private function simulate(): void
    {
        $seconds = config('launchpad.fake_step_seconds');

        Process::fake(function () use ($seconds) {
            sleep($seconds);

            return Process::result("ok (simulated)\n");
        });
    }

    /**
     * Each step's label, shell command, and working directory.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    private function steps(): array
    {
        $launch = $this->launch;
        $app = $launch->workspacePath();
        $modules = implode(',', array_unique([...config('launchpad.required_modules'), ...$launch->modules]));
        $email = $launch->user->email;
        $createOwner = sprintf(
            '\App\Models\User::forceCreate(["name" => %s, "email" => %s, "password" => bcrypt(\Illuminate\Support\Str::random(40)), "email_verified_at" => now()]);',
            var_export($launch->user->name, true),
            var_export($email, true),
        );

        $steps = [
            'Create app' => [sprintf(
                'saucebase new %s --stack=vue --driver=native --modules=%s --app-name=%s --no-interaction',
                escapeshellarg($launch->slug), $modules, escapeshellarg($launch->name),
            ), dirname($app)],
            'Apply theme' => ['php artisan saucebase:theme:apply '.escapeshellarg($launch->theme), $app],
            'Create Stripe project' => ['stripe projects init '.escapeshellarg($launch->slug).' --non-interactive --accept-tos --skip-skills --yes', $app],
        ];

        foreach ($launch->services as $service) {
            foreach (config("launchpad.services.{$service}.slugs") as $slug) {
                $steps["Provision {$slug}"] = ['stripe projects add '.escapeshellarg($slug).' --non-interactive --accept-tos', $app];
            }
        }

        // ponytail: migrates and creates the owner locally against the pulled remote
        // credentials, so no remote command is needed; switch to `cloud command:run` if
        // the pulled .env doesn't point at the production database.
        return $steps + [
            'Pull credentials' => ['stripe projects env --pull --non-interactive', $app],
            'Migrate database' => ['php artisan migrate --force', $app],
            'Create admin' => ['php artisan tinker --execute '.escapeshellarg($createOwner).' && php artisan auth:make-admin '.escapeshellarg($email), $app],
            'Deploy' => ['cloud ship --name='.escapeshellarg($launch->slug).' --no-interaction', $app],
        ];
    }
}
