<?php

namespace Modules\Launchpad\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use Modules\Launchpad\Jobs\RunLaunch;
use Modules\Launchpad\Models\Launch;
use Tests\TestCase;

class RunLaunchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['launchpad.fake' => false]);
    }

    private function makeLaunch(): Launch
    {
        return Launch::create([
            'user_id' => $this->createUser()->id,
            'name' => 'Groomly',
            'slug' => 'groomly',
            'tagline' => 'Bookings for dog groomers',
            'modules' => ['blog', 'coming-soon'],
            'theme' => 'kiwi',
            'services' => ['postgres'],
        ]);
    }

    public function test_it_runs_every_step_with_the_selections_and_goes_live(): void
    {
        Process::fake();
        $launch = $this->makeLaunch();

        RunLaunch::dispatchSync($launch);

        Process::assertRan(fn (PendingProcess $process) => str_contains($process->command, "saucebase new 'groomly'")
            && str_contains($process->command, '--modules=auth,billing,themes,blog,coming-soon'));
        Process::assertRan(fn (PendingProcess $process) => str_contains($process->command, "saucebase:theme:apply 'kiwi'"));
        Process::assertRan(fn (PendingProcess $process) => str_contains($process->command, 'stripe projects init')
            && str_contains($process->command, '--yes'));
        Process::assertRan(fn (PendingProcess $process) => str_contains($process->command, "stripe projects add 'Neon/free'"));
        Process::assertRan(fn (PendingProcess $process) => str_contains($process->command, "stripe projects add 'neon/postgres'"));
        Process::assertRan(fn (PendingProcess $process) => str_contains($process->command, "auth:make-admin '{$launch->user->email}'"));

        $launch->refresh();
        $this->assertSame('live', $launch->status);
        $this->assertStringContainsString('Apply theme', $launch->log);
    }

    public function test_a_failing_step_stops_the_launch_and_keeps_the_error(): void
    {
        Process::fake([
            '*saucebase:theme:apply*' => Process::result(errorOutput: 'Theme not found', exitCode: 1),
            '*' => Process::result(),
        ]);
        $launch = $this->makeLaunch();

        RunLaunch::dispatchSync($launch);

        Process::assertDidntRun(fn (PendingProcess $process) => str_contains($process->command, 'stripe projects'));
        $launch->refresh();
        $this->assertSame('failed', $launch->status);
        $this->assertSame('Apply theme', $launch->step);
        $this->assertStringContainsString('Theme not found', $launch->log);
    }

    public function test_demo_mode_replays_the_launch_without_running_anything_real(): void
    {
        config(['launchpad.fake' => true, 'launchpad.fake_step_seconds' => 0]);
        Process::fake(['*' => Process::result('REAL COMMAND RAN')]);
        $launch = $this->makeLaunch();

        RunLaunch::dispatchSync($launch);

        $launch->refresh();
        $this->assertSame('live', $launch->status);
        $this->assertStringNotContainsString('REAL COMMAND RAN', $launch->log);
        $this->assertStringContainsString('simulated', $launch->log);
        $this->assertSame('https://groomly.laravel.cloud', $launch->url);
    }
}
