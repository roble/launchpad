<p align="center"><img src="public/images/logo-on-light.svg" width="320" alt="Launchpad"></p>

<p align="center"><strong>Your SaaS idea, live before lunch.</strong></p>

<p align="center"><a href="https://launchpad-stripe.laravel.cloud">Live demo</a></p>

---

Launchpad turns a one-line idea into a deployed SaaS. Pick your modules and a theme, click **Launch**, and it scaffolds a [Saucebase](https://github.com/saucebase-dev/saucebase) app, provisions payments, a database, and email through **Stripe Projects**, deploys to **Laravel Cloud**, and makes you the admin.

Built for the Stripe Projects hackathon.

## How it works

1. **Idea:** name, tagline, and what you're building.
2. **Modules:** auth, billing, and themes are always included. Add a blog, roadmap, announcements, or a coming-soon page.
3. **Theme:** pick one of 14 presets (Kiwi, Blueberry, Coffee, …).
4. **Launch:** choose the services to provision, then go.

A queued job then runs three CLIs in order and streams each step to a live progress page:

| Step | Command |
|---|---|
| Create the app | `saucebase new <slug> --modules=…` |
| Apply the theme | `php artisan saucebase:theme:apply <theme>` |
| Create the Stripe project | `stripe projects init` |
| Provision services | `stripe projects add stripe/sandbox`, `Neon/free` + `neon/postgres`, `resend/email` |
| Create the admin | `php artisan auth:make-admin <your email>` |
| Deploy | `cloud ship` |

When it finishes you get the live URL, the login and admin links, and how to sign in.

## Demo mode

Launches are **simulated by default**: each step pauses and reports success, and nothing real is created. The live demo runs this way.

To run real launches, set `LAUNCHPAD_FAKE=false` on a machine where the `saucebase`, `stripe`, `gh`, and `cloud` CLIs are installed and signed in. Each launch then creates real resources on those accounts.

## Run it locally

Requires PHP 8.4, Composer, and Node.

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run build
php artisan queue:work   # processes launches
```

The code lives in `modules/launchpad`:

- `src/Jobs/RunLaunch.php`: the launch steps
- `src/Http/Controllers/LaunchpadController.php`: wizard, launch, and progress
- `resources/js/pages/`: the wizard (`Index.vue`) and progress page (`Show.vue`)
- `config/config.php`: offered modules, services, and demo-mode settings

```bash
php artisan test --compact modules/launchpad/tests
```

## Built with

[Saucebase](https://github.com/saucebase-dev/saucebase) · Laravel 13 · Inertia + Vue · [Stripe Projects](https://projects.dev) · Laravel Cloud · Neon · Resend
