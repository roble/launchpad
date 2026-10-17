<?php

return [
    /** Where launched apps are created on this machine. */
    'workspace' => env('LAUNCHPAD_WORKSPACE', storage_path('app/launches')),

    /**
     * Demo mode: replay a launch without running any CLI. On by default so nothing real
     * is created until LAUNCHPAD_FAKE=false is set deliberately.
     */
    'fake' => (bool) env('LAUNCHPAD_FAKE', true),

    'fake_step_seconds' => 2,

    /** Theme presets offered in the wizard, read from the themes module. */
    'themes_path' => base_path('modules/themes/resources/themes'),

    /** Modules every launched app gets, whatever the user picks. */
    'required_modules' => ['auth', 'billing', 'themes'],

    /** Modules a user may add. */
    'optional_modules' => ['announcements', 'blog', 'coming-soon', 'roadmap'],

    /**
     * Stripe Projects services a user may pick, each with the exact catalog slugs it adds,
     * in order. A plan comes before the resource that needs it (Neon).
     *
     * ponytail: Resend allows one free plan per account, and this account already has it,
     * so only the email resource is added; add Resend/free first for a fresh account.
     */
    'services' => [
        'payments' => ['label' => 'Stripe payments (sandbox)', 'slugs' => ['stripe/sandbox']],
        'postgres' => ['label' => 'Postgres database (Neon)', 'slugs' => ['Neon/free', 'neon/postgres']],
        'email' => ['label' => 'Transactional email (Resend)', 'slugs' => ['resend/email']],
    ],
];
