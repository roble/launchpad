<?php

namespace Modules\Launchpad\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Launchpad\Jobs\RunLaunch;
use Modules\Launchpad\Models\Launch;

class LaunchpadController
{
    /** The launch wizard, with everything a user can pick and their past launches. */
    public function index(Request $request): Response
    {
        return Inertia::render('Launchpad::Index', [
            'catalog' => [
                'modules' => config('launchpad.optional_modules'),
                'themes' => $this->themes(),
                'services' => collect(config('launchpad.services'))
                    ->map(fn (array $service, string $key) => [
                        'slug' => $key,
                        'label' => $service['label'],
                        'catalog' => implode(' + ', $service['slugs']),
                    ])
                    ->values(),
            ],
            'launches' => Launch::query()->whereBelongsTo($request->user())->latest()
                ->get(['id', 'name', 'status', 'url', 'created_at']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'idea' => ['nullable', 'string', 'max:500'],
            'name' => ['required', 'string', 'max:40', 'regex:/[a-z]/i'],
            'tagline' => ['nullable', 'string', 'max:120'],
            'modules' => ['array'],
            'modules.*' => [Rule::in(config('launchpad.optional_modules'))],
            'theme' => ['required', Rule::in(array_column($this->themes(), 'name'))],
            'services' => ['array'],
            'services.*' => [Rule::in(array_keys(config('launchpad.services')))],
        ]);

        // ponytail: slug comes straight from the name, so two launches named the same collide
        // in the workspace and on Laravel Cloud; add a suffix once that happens.
        $launch = Launch::create([
            ...$validated,
            'modules' => $validated['modules'] ?? [],
            'services' => $validated['services'] ?? [],
            'slug' => Str::slug($validated['name']),
            'user_id' => $request->user()->id,
        ]);

        RunLaunch::dispatch($launch);

        return redirect()->route('launchpad.show', $launch);
    }

    public function show(Request $request, Launch $launch): Response
    {
        abort_unless($launch->user_id === $request->user()->id, 403);

        return Inertia::render('Launchpad::Show', [
            'launch' => $launch->only(['id', 'name', 'slug', 'tagline', 'theme', 'modules', 'services', 'status', 'step', 'log', 'url']),
            'adminEmail' => $request->user()->email,
        ]);
    }

    /**
     * The themes module's presets, with a few colours to preview each.
     *
     * @return list<array{name: string, title: string, description: string, swatches: list<string>}>
     */
    private function themes(): array
    {
        return collect(glob(config('launchpad.themes_path').'/*.json'))
            ->map(function (string $file) {
                $theme = json_decode(file_get_contents($file), true);
                $light = $theme['cssVars']['light'] ?? [];

                return [
                    'name' => $theme['name'],
                    'title' => $theme['title'] ?? Str::headline($theme['name']),
                    'description' => $theme['description'] ?? '',
                    'swatches' => array_values(array_filter([
                        $light['primary'] ?? null,
                        $light['secondary'] ?? null,
                        $light['accent'] ?? null,
                        $light['background'] ?? null,
                    ])),
                ];
            })
            ->sortBy(fn (array $theme) => $theme['name'] === 'default' ? '' : $theme['name'])
            ->values()
            ->all();
    }
}
