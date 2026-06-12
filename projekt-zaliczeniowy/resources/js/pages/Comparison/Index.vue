<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ExternalLink } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { cn } from '@/lib/utils';
import { dashboard } from '@/routes';
import comparisonRoute from '@/routes/comparison';

type SeriesPoint = { year: number; value: string };
type NewsItem = {
    id: number;
    headline: string;
    snippet: string | null;
    section: string | null;
    published_at: string;
    url: string | null;
};
type IndicatorOption = {
    slug: string;
    name: string;
    unit: string | null;
    category: string;
};

const props = defineProps<{
    indicatorOptions: IndicatorOption[];
    selected: { slug: string; name: string; unit: string | null; category: string };
    series: SeriesPoint[];
    newsByYear: Record<string, NewsItem[]>;
    filters: { year_from: number; year_to: number };
}>();

const selectedYear = ref<number | null>(null);

const chartWidth = 720;
const chartHeight = 260;
const padding = { top: 20, right: 20, bottom: 36, left: 56 };

const points = computed(() => {
    if (props.series.length === 0) {
        return [];
    }

    const values = props.series.map((p) => Number(p.value));
    const min = Math.min(...values);
    const max = Math.max(...values);
    const range = max - min || 1;
    const innerW = chartWidth - padding.left - padding.right;
    const innerH = chartHeight - padding.top - padding.bottom;

    return props.series.map((point, index) => {
        const x =
            padding.left +
            (index / Math.max(props.series.length - 1, 1)) * innerW;
        const y =
            padding.top +
            innerH -
            ((Number(point.value) - min) / range) * innerH;

        return { ...point, x, y, valueNum: Number(point.value) };
    });
});

const polylinePoints = computed(() =>
    points.value.map((p) => `${p.x},${p.y}`).join(' '),
);

const chartYears = computed(() => points.value.map((p) => p.year));

watch(
    () => props.series,
    () => {
        const years = props.series.map((p) => p.year);
        if (years.length === 0) {
            selectedYear.value = null;
            return;
        }
        if (
            selectedYear.value === null ||
            !years.includes(selectedYear.value)
        ) {
            selectedYear.value = years[years.length - 1] ?? null;
        }
    },
    { immediate: true },
);

const activeNews = computed(() => {
    if (selectedYear.value === null) {
        return [];
    }

    return props.newsByYear[String(selectedYear.value)] ?? [];
});

const yAxisLabels = computed(() => {
    if (props.series.length === 0) {
        return [];
    }

    const values = props.series.map((p) => Number(p.value));
    const min = Math.min(...values);
    const max = Math.max(...values);
    const innerH = chartHeight - padding.top - padding.bottom;

    return [0, 0.5, 1].map((ratio) => {
        const value = min + (max - min) * (1 - ratio);
        const y = padding.top + innerH * ratio;

        return { value, y };
    });
});

function selectYear(year: number) {
    selectedYear.value = year;
}

function onIndicatorChange(slug: string) {
    router.get(
        comparisonRoute.index(),
        { indicator: slug },
        { preserveState: true, replace: true },
    );
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Panel', href: dashboard() },
            { title: 'Porównanie', href: comparisonRoute.index() },
        ],
    },
});
</script>

<template>
    <Head title="Porównanie" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Porównanie</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Wykres wskaźnika w czasie z kontekstem prasowym dla wybranego
                roku. Kliknij punkt na wykresie lub rok pod osią.
            </p>
        </div>

        <Card>
            <CardHeader class="pb-4">
                <CardTitle class="text-base">{{ selected.name }}</CardTitle>
                <CardDescription v-if="selected.unit">
                    Jednostka: {{ selected.unit }}
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <div class="grid max-w-md gap-2">
                    <Label>Wskaźnik</Label>
                    <Select
                        :model-value="selected.slug"
                        @update:model-value="onIndicatorChange"
                    >
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="opt in indicatorOptions"
                                :key="opt.slug"
                                :value="opt.slug"
                            >
                                {{ opt.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <div
                    v-if="series.length === 0"
                    class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground"
                >
                    Brak danych dla tego wskaźnika w wybranym zakresie lat.
                </div>

                <div v-else class="space-y-3 overflow-x-auto">
                    <svg
                        :viewBox="`0 0 ${chartWidth} ${chartHeight}`"
                        class="w-full min-w-[480px] text-foreground"
                        role="img"
                        :aria-label="`Wykres: ${selected.name}`"
                    >
                        <line
                            :x1="padding.left"
                            :y1="chartHeight - padding.bottom"
                            :x2="chartWidth - padding.right"
                            :y2="chartHeight - padding.bottom"
                            class="stroke-border"
                            stroke-width="1"
                        />
                        <line
                            :x1="padding.left"
                            :y1="padding.top"
                            :x2="padding.left"
                            :y2="chartHeight - padding.bottom"
                            class="stroke-border"
                            stroke-width="1"
                        />

                        <text
                            v-for="label in yAxisLabels"
                            :key="label.y"
                            :x="padding.left - 8"
                            :y="label.y + 4"
                            text-anchor="end"
                            class="fill-muted-foreground text-[10px]"
                        >
                            {{
                                label.value.toLocaleString('pl-PL', {
                                    maximumFractionDigits: 1,
                                })
                            }}
                        </text>

                        <polyline
                            :points="polylinePoints"
                            fill="none"
                            class="stroke-primary"
                            stroke-width="2"
                            stroke-linejoin="round"
                            stroke-linecap="round"
                        />

                        <g v-for="point in points" :key="point.year">
                            <circle
                                :cx="point.x"
                                :cy="point.y"
                                r="14"
                                fill="transparent"
                                class="cursor-pointer"
                                role="button"
                                :aria-label="`Wybierz rok ${point.year}`"
                                @click="selectYear(point.year)"
                            />
                            <circle
                                :cx="point.x"
                                :cy="point.y"
                                :r="selectedYear === point.year ? 5.5 : 3.5"
                                class="fill-primary stroke-background transition-all"
                                :class="{
                                    'stroke-2': selectedYear === point.year,
                                    'opacity-60': selectedYear !== point.year,
                                }"
                                stroke-width="2"
                                pointer-events="none"
                            />
                            <text
                                :x="point.x"
                                :y="chartHeight - 12"
                                text-anchor="middle"
                                class="cursor-pointer select-none text-[10px] transition-colors"
                                :class="
                                    selectedYear === point.year
                                        ? 'fill-primary font-semibold'
                                        : 'fill-muted-foreground hover:fill-foreground'
                                "
                                role="button"
                                @click="selectYear(point.year)"
                            >
                                {{ point.year }}
                            </text>
                        </g>
                    </svg>

                    <div
                        class="flex flex-wrap gap-1.5"
                        role="group"
                        aria-label="Wybór roku"
                    >
                        <button
                            v-for="year in chartYears"
                            :key="`btn-${year}`"
                            type="button"
                            :class="
                                cn(
                                    'rounded-md border px-2 py-1 text-xs tabular-nums transition-colors',
                                    selectedYear === year
                                        ? 'border-primary bg-primary text-primary-foreground'
                                        : 'border-border text-muted-foreground hover:bg-muted hover:text-foreground',
                                )
                            "
                            @click="selectYear(year)"
                        >
                            {{ year }}
                        </button>
                    </div>
                </div>
            </CardContent>
        </Card>

        <Card>
            <CardHeader>
                <CardTitle class="text-base">
                    Aktualności
                    <span v-if="selectedYear !== null">
                        — {{ selectedYear }}
                    </span>
                </CardTitle>
                <CardDescription>
                    Artykuły z NY Times opublikowane w wybranym roku.
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
                <p
                    v-if="selectedYear === null"
                    class="text-sm text-muted-foreground"
                >
                    Wybierz rok na wykresie.
                </p>
                <p
                    v-else-if="activeNews.length === 0"
                    class="text-sm text-muted-foreground"
                >
                    Brak artykułów dla roku {{ selectedYear }} w bazie.
                </p>
                <article
                    v-for="article in activeNews"
                    :key="article.id"
                    class="border-b border-border pb-3 last:border-0 last:pb-0"
                >
                    <a
                        v-if="article.url"
                        :href="article.url"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-start gap-1 text-sm font-medium hover:underline"
                    >
                        {{ article.headline }}
                        <ExternalLink class="mt-0.5 size-3.5 shrink-0" />
                    </a>
                    <p v-else class="text-sm font-medium">
                        {{ article.headline }}
                    </p>
                    <p class="mt-1 text-xs text-muted-foreground">
                        {{ article.section }}
                        ·
                        {{
                            new Date(article.published_at).toLocaleDateString(
                                'pl-PL',
                            )
                        }}
                    </p>
                    <p
                        v-if="article.snippet"
                        class="mt-1 text-sm text-muted-foreground line-clamp-2"
                    >
                        {{ article.snippet }}
                    </p>
                </article>
            </CardContent>
        </Card>
    </div>
</template>
