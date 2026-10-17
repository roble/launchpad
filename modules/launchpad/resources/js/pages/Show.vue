<script setup lang="ts">
import { Button } from "@/components/ui/button";
import AppLayout from "@/layouts/AppLayout.vue";
import { Link, usePoll } from "@inertiajs/vue3";
import { computed, nextTick, ref, watch } from "vue";
import IconCheck from "~icons/heroicons/check-circle";
import IconX from "~icons/heroicons/x-circle";
import IconRocket from "~icons/heroicons/rocket-launch";

const props = defineProps<{
    launch: {
        id: number;
        name: string;
        slug: string;
        tagline: string | null;
        theme: string;
        modules: string[];
        services: string[];
        status: "queued" | "running" | "live" | "failed";
        step: string | null;
        log: string | null;
        url: string | null;
    };
    adminEmail: string;
}>();

const inFlight = computed(() =>
    ["queued", "running"].includes(props.launch.status),
);

// Reload just the launch every two seconds until it lands or fails.
const { stop } = usePoll(2000, { only: ["launch"] });
watch(inFlight, (flying) => flying || stop(), { immediate: true });

/** The step labels the job has started, read from the "▶ label" lines in the log. */
const startedSteps = computed(() =>
    (props.launch.log ?? "")
        .split("\n")
        .filter((line) => line.startsWith("▶ "))
        .map((line) => line.slice(2)),
);

const logBox = ref<HTMLElement>();
watch(
    () => props.launch.log,
    async () => {
        await nextTick();
        logBox.value?.scrollTo({ top: logBox.value.scrollHeight });
    },
);

const siteUrl = computed(
    () => props.launch.url ?? `https://${props.launch.slug}.laravel.cloud`,
);
</script>

<template>
    <AppLayout
        :title="launch.name"
        :breadcrumbs="[
            { title: $t('Launchpad'), url: route('launchpad.index') },
            { title: launch.name },
        ]"
    >
        <div
            class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-6 p-6 pt-2"
        >
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        {{ launch.name }}
                    </h1>
                    <p v-if="launch.tagline" class="text-muted-foreground mt-1">
                        {{ launch.tagline }}
                    </p>
                </div>
                <span
                    class="rounded-full px-3 py-1 text-sm font-medium capitalize"
                    :class="{
                        'bg-green-500/15 text-green-700 dark:text-green-400':
                            launch.status === 'live',
                        'bg-red-500/15 text-red-700 dark:text-red-400':
                            launch.status === 'failed',
                        'bg-primary/15 text-primary animate-pulse': inFlight,
                    }"
                    data-testid="launch-status"
                >
                    {{ launch.status }}
                </span>
            </div>

            <!-- Handover -->
            <section
                v-if="launch.status === 'live'"
                class="bg-primary text-primary-foreground relative overflow-hidden rounded-2xl p-6"
                data-testid="handover"
            >
                <IconRocket
                    class="pointer-events-none absolute -right-6 -bottom-8 size-48 opacity-10"
                />
                <h2 class="text-2xl font-bold">
                    🎉 {{ $t(":name is live", { name: launch.name }) }}
                </h2>
                <dl
                    class="mt-4 grid gap-2 font-mono text-sm sm:grid-cols-[8rem_1fr]"
                >
                    <dt class="opacity-70">{{ $t("Site") }}</dt>
                    <dd>
                        <a :href="siteUrl" target="_blank" class="underline">{{
                            siteUrl
                        }}</a>
                    </dd>
                    <dt class="opacity-70">{{ $t("Log in") }}</dt>
                    <dd>
                        <a
                            :href="`${siteUrl}/auth/login`"
                            target="_blank"
                            class="underline"
                            >{{ siteUrl }}/auth/login</a
                        >
                    </dd>
                    <dt class="opacity-70">{{ $t("Admin panel") }}</dt>
                    <dd>
                        <a
                            :href="`${siteUrl}/admin`"
                            target="_blank"
                            class="underline"
                            >{{ siteUrl }}/admin</a
                        >
                    </dd>
                    <dt class="opacity-70">{{ $t("Admin account") }}</dt>
                    <dd>{{ adminEmail }}</dd>
                </dl>
                <ol
                    class="mt-5 list-inside list-decimal space-y-1 text-sm opacity-90"
                >
                    <li>
                        {{
                            $t(
                                'Open the login page and choose "Email me a magic link".',
                            )
                        }}
                    </li>
                    <li>
                        {{
                            $t(
                                "Click the link in your inbox. You are signed in as admin.",
                            )
                        }}
                    </li>
                    <li>
                        {{
                            $t(
                                "Open /admin to manage plans, content, and settings.",
                            )
                        }}
                    </li>
                </ol>
            </section>

            <!-- Failure -->
            <section
                v-if="launch.status === 'failed'"
                class="rounded-2xl border border-red-500/40 bg-red-500/5 p-5"
            >
                <h2 class="font-semibold text-red-700 dark:text-red-400">
                    {{
                        $t('The launch stopped at ":step".', {
                            step: launch.step ?? "",
                        })
                    }}
                </h2>
                <p class="text-muted-foreground mt-1 text-sm">
                    {{ $t("The log below shows what went wrong.") }}
                </p>
            </section>

            <!-- Steps -->
            <ol
                class="bg-card grid gap-2 rounded-2xl border p-5"
                data-testid="launch-steps"
            >
                <li
                    v-if="!startedSteps.length"
                    class="text-muted-foreground text-sm"
                >
                    {{ $t("Waiting for the launch to start…") }}
                </li>
                <li
                    v-for="label in startedSteps"
                    :key="label"
                    class="flex items-center gap-3 text-sm"
                >
                    <IconX
                        v-if="
                            launch.status === 'failed' && label === launch.step
                        "
                        class="size-5 text-red-500"
                    />
                    <span
                        v-else-if="inFlight && label === launch.step"
                        class="border-primary size-5 animate-spin rounded-full border-2 border-t-transparent"
                    />
                    <IconCheck v-else class="text-primary size-5" />
                    <span :class="{ 'font-medium': label === launch.step }">{{
                        label
                    }}</span>
                </li>
            </ol>

            <!-- Raw log -->
            <details
                :open="launch.status === 'failed'"
                class="rounded-2xl border"
            >
                <summary class="cursor-pointer px-5 py-3 text-sm font-medium">
                    {{ $t("Launch log") }}
                </summary>
                <pre
                    ref="logBox"
                    class="bg-muted/40 max-h-96 overflow-auto px-5 py-4 font-mono text-xs whitespace-pre-wrap"
                    data-testid="launch-log"
                    >{{ launch.log || "…" }}</pre>
            </details>

            <div>
                <Button variant="outline" as-child>
                    <Link :href="route('launchpad.index')">{{
                        $t("Start another launch")
                    }}</Link>
                </Button>
            </div>
        </div>
    </AppLayout>
</template>
