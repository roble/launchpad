<?php

use Saucebase\Core\Facades\Navigation;
use Saucebase\Core\Navigation\Section;

/*
|--------------------------------------------------------------------------
| Launchpad Module Navigation
|--------------------------------------------------------------------------
|
| Define Launchpad module navigation items here.
| These items will be loaded automatically when the module is enabled.
|
*/

Navigation::add('Launchpad', fn () => route('launchpad.index'), function (Section $section) {
    $section->attributes([
        'group' => 'main',
        'slug' => 'launchpad',
        'icon' => 'launchpad',
        'order' => 1,
    ]);
});
