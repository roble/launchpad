<?php

use Illuminate\Support\Facades\Auth;
use Saucebase\Core\Facades\Navigation;
use Saucebase\Core\Navigation\Section;

/*
|--------------------------------------------------------------------------
| Core Navigation
|--------------------------------------------------------------------------
|
| Define core application navigation items here.
| These items will be loaded automatically by the Navigation service.
|
*/

Navigation::add('Welcome', fn () => route('dashboard'), function (Section $section) {
    $section->attributes([
        'group' => 'main',
        'slug' => 'dashboard',
        'icon' => 'dashboard',
        'order' => 0,
    ]);
});

Navigation::addWhen(
    fn () => Auth::user()?->can('access admin panel') ?? false,
    'Admin',
    fn () => route('filament.admin.pages.dashboard'),
    function (Section $section) {
        $section->attributes([
            'group' => 'secondary',
            'slug' => 'admin',
            'icon' => 'admin',
            'order' => 10,
            'external' => true,
            'newPage' => true,
            'class' => 'bg-yellow-500/10 text-yellow-600 hover:bg-yellow-500/20 hover:text-yellow-700 dark:hover:text-yellow-400',
        ]);
    }
);
