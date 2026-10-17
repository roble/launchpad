<script setup lang="ts">
import { Button } from "@/components/ui/button";
import { Input } from "@/components/ui/input";
import { Textarea } from "@/components/ui/textarea";
import AppLayout from "@/layouts/AppLayout.vue";
import { Link, useForm } from "@inertiajs/vue3";
import { computed, ref } from "vue";
import IconCheck from "~icons/heroicons/check";
import IconLock from "~icons/heroicons/lock-closed";
import IconRocket from "~icons/heroicons/rocket-launch";

interface Theme {
    name: string;
    title: string;
    description: string;
    swatches: string[];
}

const props = defineProps<{
    catalog: {
        modules: string[];
        themes: Theme[];
        services: { slug: string; label: string; catalog: string }[];
    };
    launches: {
        id: number;
        name: string;
        status: string;
        url: string | null;
    }[];
}>();

const title = "Launchpad";
const stepTitles = ["Idea", "Modules", "Theme", "Launch"];
const step = ref(0);

const form = useForm({
    idea: "",
    name: "",
    tagline: "",
    modules: ["coming-soon"] as string[],
    theme: "default",
    services: props.catalog.services.map((service) => service.slug),
});

const canContinue = computed(() => step.value !== 0 || form.name.trim() !== "");
const chosenTheme = computed(() =>
    props.catalog.themes.find((theme) => theme.name === form.theme),
);

function toggle(list: string[], value: string) {
    const index = list.indexOf(value);
    if (index === -1) list.push(value);
    else list.splice(index, 1);
}

function launch() {
    form.post(route("launchpad.store"), {
        // Send the user back to whichever step holds the first error.
        onError: (errors) => {
            const keys = Object.keys(errors);
            if (keys.some((key) => ["name", "idea", "tagline"].includes(key)))
                step.value = 0;
            else if (keys.some((key) => key.startsWith("modules")))
                step.value = 1;
            else if (keys.includes("theme")) step.value = 2;
        },
    });
}

const statusClass: Record<string, string> = {
    live: "bg-green-500/15 text-green-700 dark:text-green-400",
    failed: "bg-red-500/15 text-red-700 dark:text-red-400",
};
</script>

<template>
    <AppLayout :title="$t(title)" :breadcrumbs="[{ title: $t(title) }]">
        <div
            class="mx-auto flex w-full max-w-4xl flex-1 flex-col gap-8 p-6 pt-2"
        >
            <div>
                <h1 class="text-3xl font-bold tracking-tight">
                    {{ $t("New launch") }}
                </h1>
                <p class="text-muted-foreground mt-1">
                    {{ $t("Four choices, one click, a live SaaS.") }}
                </p>
            </div>

            <!-- Step indicator -->
            <ol class="grid grid-cols-4 gap-2" data-testid="wizard-steps">
                <li v-for="(label, index) in stepTitles" :key="label">
                    <button
                        type="button"
                        class="w-full border-t-4 pt-2 text-left text-sm font-medium transition"
                        :class="
                            index <= step
                                ? 'border-primary text-foreground'
                                : 'text-muted-foreground border-muted'
                        "
                        :disabled="index > step"
                        @click="step = index"
                    >
                        <span class="font-mono text-xs">0{{ index + 1 }}</span>
                        {{ $t(label) }}
                    </button>
                </li>
            </ol>

            <form
                class="bg-card rounded-2xl border p-6 shadow-sm"
                @submit.prevent="step < 3 ? step++ : launch()"
            >
                <!-- 1. Idea -->
                <div v-if="step === 0" class="grid gap-5">
                    <label class="grid gap-2">
                        <span class="text-sm font-medium">{{
                            $t("What are you building?")
                        }}</span>
                        <Textarea
                            v-model="form.idea"
                            rows="3"
                            :placeholder="$t('A booking app for dog groomers')"
                            data-testid="idea"
                        />
                    </label>
                    <div class="grid gap-5 sm:grid-cols-2">
                        <label class="grid gap-2">
                            <span class="text-sm font-medium">{{
                                $t("App name")
                            }}</span>
                            <Input
                                v-model="form.name"
                                placeholder="Groomly"
                                required
                                data-testid="name"
                            />
                            <span
                                v-if="form.errors.name"
                                class="text-destructive text-sm"
                                >{{ form.errors.name }}</span
                            >
                        </label>
                        <label class="grid gap-2">
                            <span class="text-sm font-medium">{{
                                $t("Tagline")
                            }}</span>
                            <Input
                                v-model="form.tagline"
                                :placeholder="$t('Bookings that wag')"
                                data-testid="tagline"
                            />
                        </label>
                    </div>
                </div>

                <!-- 2. Modules -->
                <div v-else-if="step === 1" class="grid gap-3 sm:grid-cols-2">
                    <div
                        v-for="module in ['auth', 'billing', 'themes']"
                        :key="module"
                        class="bg-muted/50 flex items-center justify-between rounded-xl border px-4 py-3"
                    >
                        <span class="font-medium capitalize">{{ module }}</span>
                        <span
                            class="text-muted-foreground flex items-center gap-1 text-xs"
                        >
                            <IconLock class="size-3.5" /> {{ $t("Included") }}
                        </span>
                    </div>
                    <button
                        v-for="module in catalog.modules"
                        :key="module"
                        type="button"
                        class="flex items-center justify-between rounded-xl border px-4 py-3 text-left transition"
                        :class="
                            form.modules.includes(module)
                                ? 'border-primary bg-primary/5 ring-primary ring-1'
                                : 'hover:bg-muted/50'
                        "
                        :data-testid="`module-${module}`"
                        @click="toggle(form.modules, module)"
                    >
                        <span class="font-medium capitalize">{{
                            module.replace("-", " ")
                        }}</span>
                        <IconCheck
                            v-if="form.modules.includes(module)"
                            class="text-primary size-5"
                        />
                    </button>
                </div>

                <!-- 3. Theme -->
                <div
                    v-else-if="step === 2"
                    class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4"
                >
                    <button
                        v-for="theme in catalog.themes"
                        :key="theme.name"
                        type="button"
                        class="rounded-xl border p-3 text-left transition"
                        :class="
                            form.theme === theme.name
                                ? 'border-primary ring-primary ring-2'
                                : 'hover:bg-muted/50'
                        "
                        :title="theme.description"
                        :data-testid="`theme-${theme.name}`"
                        @click="form.theme = theme.name"
                    >
                        <div
                            class="flex h-10 overflow-hidden rounded-lg border"
                        >
                            <span
                                v-for="color in theme.swatches"
                                :key="color"
                                class="flex-1"
                                :style="{ background: color }"
                            />
                        </div>
                        <span class="mt-2 block text-sm font-medium">{{
                            theme.title
                        }}</span>
                    </button>
                </div>

                <!-- 4. Services + review -->
                <div v-else class="grid gap-6">
                    <div class="grid gap-3">
                        <h2 class="font-semibold">
                            {{ $t("Provision with Stripe Projects") }}
                        </h2>
                        <button
                            v-for="service in catalog.services"
                            :key="service.slug"
                            type="button"
                            class="flex items-center justify-between rounded-xl border px-4 py-3 text-left transition"
                            :class="
                                form.services.includes(service.slug)
                                    ? 'border-primary bg-primary/5 ring-primary ring-1'
                                    : 'hover:bg-muted/50'
                            "
                            :data-testid="`service-${service.slug}`"
                            @click="toggle(form.services, service.slug)"
                        >
                            <span>
                                <span class="block font-medium">{{
                                    service.label
                                }}</span>
                                <span
                                    class="text-muted-foreground font-mono text-xs"
                                    >{{ service.catalog }}</span
                                >
                            </span>
                            <IconCheck
                                v-if="form.services.includes(service.slug)"
                                class="text-primary size-5"
                            />
                        </button>
                        <p class="text-muted-foreground text-sm">
                            {{
                                $t(
                                    "Hosting on Laravel Cloud is always included.",
                                )
                            }}
                        </p>
                    </div>

                    <dl
                        class="bg-muted/40 grid gap-2 rounded-xl p-4 text-sm sm:grid-cols-2"
                    >
                        <div>
                            <dt class="text-muted-foreground">
                                {{ $t("App") }}
                            </dt>
                            <dd class="font-medium">{{ form.name }}</dd>
                        </div>
                        <div>
                            <dt class="text-muted-foreground">
                                {{ $t("Theme") }}
                            </dt>
                            <dd class="font-medium">
                                {{ chosenTheme?.title }}
                            </dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-muted-foreground">
                                {{ $t("Modules") }}
                            </dt>
                            <dd class="font-medium capitalize">
                                {{
                                    [
                                        "auth",
                                        "billing",
                                        "themes",
                                        ...form.modules,
                                    ].join(", ")
                                }}
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="mt-8 flex justify-between">
                    <Button
                        type="button"
                        variant="ghost"
                        :disabled="step === 0"
                        @click="step--"
                        >{{ $t("Back") }}</Button
                    >
                    <Button
                        v-if="step < 3"
                        type="submit"
                        :disabled="!canContinue"
                        data-testid="next"
                        >{{ $t("Continue") }}</Button
                    >
                    <Button
                        v-else
                        type="submit"
                        :disabled="form.processing"
                        data-testid="launch"
                    >
                        <IconRocket class="size-4" /> {{ $t("Launch") }}
                    </Button>
                </div>
            </form>

            <!-- Past launches -->
            <section v-if="launches.length" class="grid gap-3">
                <h2 class="font-semibold">{{ $t("Your launches") }}</h2>
                <Link
                    v-for="item in launches"
                    :key="item.id"
                    :href="route('launchpad.show', item.id)"
                    class="hover:bg-muted/50 flex items-center justify-between rounded-xl border px-4 py-3"
                >
                    <span class="font-medium">{{ item.name }}</span>
                    <span
                        class="rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                        :class="statusClass[item.status] ?? 'bg-muted'"
                    >
                        {{ item.status }}
                    </span>
                </Link>
            </section>
        </div>
    </AppLayout>
</template>
