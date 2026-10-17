<?php

namespace Modules\Launchpad\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia;
use Modules\Launchpad\Jobs\RunLaunch;
use Modules\Launchpad\Models\Launch;
use Tests\TestCase;

class LaunchControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function validLaunch(array $overrides = []): array
    {
        return [
            'idea' => 'A booking app for dog groomers',
            'name' => 'Groomly',
            'tagline' => 'Bookings for dog groomers',
            'modules' => ['blog'],
            'theme' => 'kiwi',
            'services' => ['email'],
            ...$overrides,
        ];
    }

    public function test_guests_are_sent_to_login(): void
    {
        $this->get(route('launchpad.index'))->assertRedirect();
    }

    public function test_the_wizard_offers_modules_themes_and_services(): void
    {
        $this->actingAs($this->createUser())
            ->get(route('launchpad.index'))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Launchpad::Index', false)
                ->where('catalog.modules', config('launchpad.optional_modules'))
                ->where('catalog.themes', fn ($themes) => collect($themes)->contains('name', 'kiwi'))
                ->has('catalog.services', count(config('launchpad.services'))));
    }

    public function test_launching_saves_the_selections_and_queues_the_build(): void
    {
        Queue::fake();
        $user = $this->createUser();

        $response = $this->actingAs($user)->post(route('launchpad.store'), $this->validLaunch());

        $launch = Launch::sole();
        $response->assertRedirect(route('launchpad.show', $launch));
        $this->assertSame($user->id, $launch->user_id);
        $this->assertSame('groomly', $launch->slug);
        $this->assertSame(['blog'], $launch->modules);
        Queue::assertPushed(RunLaunch::class, fn (RunLaunch $job) => $job->launch->is($launch));
    }

    public function test_only_offered_modules_themes_and_services_are_accepted(): void
    {
        Queue::fake();

        $this->actingAs($this->createUser())
            ->post(route('launchpad.store'), $this->validLaunch([
                'modules' => ['evil'],
                'theme' => '../../etc/passwd',
                'services' => ['Supabase/pro'],
            ]))
            ->assertSessionHasErrors(['modules.0', 'theme', 'services.0']);

        Queue::assertNothingPushed();
    }

    public function test_a_launch_is_visible_only_to_its_owner(): void
    {
        Queue::fake();
        $owner = $this->createUser();
        $this->actingAs($owner)->post(route('launchpad.store'), $this->validLaunch());
        $launch = Launch::sole();

        $this->actingAs($owner)->get(route('launchpad.show', $launch))
            ->assertOk()
            ->assertInertia(fn (AssertableInertia $page) => $page->component('Launchpad::Show', false)->where('launch.id', $launch->id));
        $this->actingAs($this->createUser())->get(route('launchpad.show', $launch))->assertForbidden();
    }
}
