<script setup lang="ts">
import SiteLayout from "@/layouts/SiteLayout.vue";
import { useSettings } from "@saucebase/vue/composables/useSettings";
import { Link } from "@inertiajs/vue3";
import { Check, Rocket } from "@lucide/vue";

const settings = useSettings();

/** The launch log the hero replays: what a real launch runs, in order. */
const sequence = [
    {
        label: "Scaffold Saucebase app",
        detail: "auth · billing · blog · coming-soon",
    },
    { label: "Apply theme", detail: "kiwi" },
    { label: "Create Stripe project", detail: "stripe projects init groomly" },
    { label: "Provision Postgres", detail: "Neon" },
    { label: "Provision email", detail: "Resend" },
    { label: "Make you admin", detail: "auth:make-admin" },
    { label: "Deploy", detail: "Laravel Cloud" },
];

const steps = [
    {
        n: "01",
        title: "Pitch your idea",
        body: "One sentence. We turn it into a name, a tagline, and a landing page.",
    },
    {
        n: "02",
        title: "Pick modules",
        body: "Auth and billing come standard. Add a blog, roadmap, announcements, and more.",
    },
    {
        n: "03",
        title: "Choose a look",
        body: "Fourteen hand-tuned themes, from Blueberry to Hotdog. Switch any time.",
    },
    {
        n: "04",
        title: "Launch",
        body: "Stripe Projects provisions the infrastructure. You get a live URL and admin keys.",
    },
];

const stack = [
    "Saucebase",
    "Stripe Projects",
    "Laravel Cloud",
    "Neon",
    "Resend",
];
</script>

<template>
    <SiteLayout
        :title="
            settings.general.site_tagline ??
            $t('From idea to live SaaS in one click')
        "
    >
        <main class="mx-auto w-full max-w-6xl px-6 lg:px-8">
            <!-- Hero -->
            <section
                class="grid items-center gap-12 pt-20 pb-16 lg:grid-cols-2 lg:pt-28"
            >
                <div>
                    <p
                        class="text-primary mb-4 inline-flex items-center gap-2 rounded-full border px-3 py-1 font-mono text-xs tracking-widest uppercase"
                    >
                        <span
                            class="bg-primary size-1.5 animate-pulse rounded-full"
                        />
                        {{ $t("T-minus one click") }}
                    </p>
                    <h1
                        class="text-foreground text-5xl font-extrabold tracking-tight md:text-6xl"
                    >
                        {{ $t("Your SaaS idea,") }}
                        <span class="text-primary block">{{
                            $t("live before lunch.")
                        }}</span>
                    </h1>
                    <p class="text-muted-foreground mt-6 max-w-lg text-lg">
                        {{
                            $t(
                                "Describe it, pick your modules and a theme. Launchpad builds the app, provisions the database, email and payments through Stripe Projects, and deploys it, with you as admin.",
                            )
                        }}
                    </p>
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        <Link
                            :href="route('launchpad.index')"
                            class="bg-primary text-primary-foreground inline-flex items-center gap-2 rounded-full px-7 py-3.5 font-semibold shadow-lg transition hover:-translate-y-0.5 hover:shadow-xl"
                            data-testid="launch-cta"
                        >
                            <Rocket class="size-5" aria-hidden="true" />
                            {{ $t("Start a launch") }}
                        </Link>
                        <span class="text-muted-foreground text-sm">{{
                            $t("First launch is free.")
                        }}</span>
                    </div>
                </div>

                <!-- Mission control: the launch log replaying -->
                <div
                    class="bg-card relative overflow-hidden rounded-2xl border shadow-2xl"
                    aria-hidden="true"
                >
                    <div class="flex items-center gap-2 border-b px-4 py-3">
                        <span class="size-3 rounded-full bg-red-400" />
                        <span class="size-3 rounded-full bg-amber-400" />
                        <span class="size-3 rounded-full bg-green-400" />
                        <span
                            class="text-muted-foreground ml-2 font-mono text-xs"
                            >launch · groomly</span
                        >
                    </div>
                    <ol class="space-y-3 p-5 font-mono text-sm">
                        <li
                            v-for="(item, index) in sequence"
                            :key="item.label"
                            class="launch-step flex items-start gap-3"
                            :style="{ '--delay': `${index * 0.7}s` }"
                        >
                            <span
                                class="bg-primary/15 text-primary mt-0.5 grid size-5 shrink-0 place-items-center rounded-full"
                            >
                                <Check class="size-3.5" />
                            </span>
                            <span>
                                <span class="text-foreground">{{
                                    item.label
                                }}</span>
                                <span
                                    class="text-muted-foreground block text-xs"
                                    >{{ item.detail }}</span
                                >
                            </span>
                        </li>
                    </ol>
                    <div
                        class="launch-step bg-primary text-primary-foreground mx-5 mb-5 rounded-xl px-4 py-3 font-mono text-sm"
                        :style="{ '--delay': `${sequence.length * 0.7}s` }"
                    >
                        🚀 groomly is live → groomly.laravel.cloud
                    </div>
                </div>
            </section>

            <!-- Built on -->
            <section
                class="text-muted-foreground flex flex-wrap items-center justify-center gap-x-8 gap-y-2 border-y py-6 font-mono text-xs tracking-widest uppercase"
            >
                <span>{{ $t("Built on") }}</span>
                <span
                    v-for="name in stack"
                    :key="name"
                    class="text-foreground/70"
                    >{{ name }}</span
                >
            </section>

            <!-- How it works -->
            <section class="py-20">
                <h2
                    class="text-center text-3xl font-bold tracking-tight md:text-4xl"
                >
                    {{ $t("Four steps to orbit") }}
                </h2>
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    <div
                        v-for="step in steps"
                        :key="step.n"
                        class="bg-card rounded-2xl border p-6 transition hover:-translate-y-1 hover:shadow-lg"
                    >
                        <span
                            class="text-primary font-mono text-sm font-bold"
                            >{{ step.n }}</span
                        >
                        <h3 class="mt-3 text-lg font-semibold">
                            {{ $t(step.title) }}
                        </h3>
                        <p class="text-muted-foreground mt-2 text-sm">
                            {{ $t(step.body) }}
                        </p>
                    </div>
                </div>
            </section>

            <!-- Closing CTA -->
            <section
                class="bg-primary text-primary-foreground relative mb-24 overflow-hidden rounded-3xl px-8 py-14 text-center"
            >
                <Rocket
                    class="pointer-events-none absolute -right-6 -bottom-10 size-64 opacity-10"
                    aria-hidden="true"
                />
                <h2 class="text-3xl font-bold md:text-4xl">
                    {{ $t("Stop configuring. Start launching.") }}
                </h2>
                <p class="text-primary-foreground/80 mx-auto mt-3 max-w-xl">
                    {{
                        $t(
                            "Every launch ships with sign-up, billing, and an admin panel already wired.",
                        )
                    }}
                </p>
                <Link
                    :href="route('launchpad.index')"
                    class="bg-background text-foreground mt-8 inline-flex items-center gap-2 rounded-full px-7 py-3.5 font-semibold transition hover:-translate-y-0.5"
                >
                    <Rocket class="size-5" aria-hidden="true" />
                    {{ $t("Launch your SaaS") }}
                </Link>
            </section>
        </main>
    </SiteLayout>
</template>

<style scoped>
/* Each step lands in turn, then the whole log replays. */
.launch-step {
    opacity: 0;
    animation: land 7s var(--delay) infinite;
}

@keyframes land {
    0%,
    100% {
        opacity: 0;
        transform: translateY(6px);
    }
    6%,
    85% {
        opacity: 1;
        transform: none;
    }
}

@media (prefers-reduced-motion: reduce) {
    .launch-step {
        animation: none;
        opacity: 1;
    }
}
</style>
