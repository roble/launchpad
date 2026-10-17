<?php

namespace Modules\Launchpad\Filament;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Saucebase\Core\Filament\ModulePlugin;

class LaunchpadPlugin implements Plugin
{
    use ModulePlugin;

    public function getModuleName(): string
    {
        return 'Launchpad';
    }

    public function getId(): string
    {
        return 'launchpad';
    }

    public function boot(Panel $panel): void
    {
        //
    }
}
