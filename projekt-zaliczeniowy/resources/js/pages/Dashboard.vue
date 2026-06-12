<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { ArrowRight, Newspaper, TrendingUp } from '@lucide/vue';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { dashboard } from '@/routes';
import comparisonRoute from '@/routes/comparison';
import indicatorsRoute from '@/routes/indicators';
import newsRoute from '@/routes/news';

type IndicatorSummary = {
    slug: string;
    name: string;
    category: string;
    unit: string | null;
    latest: { year: number; value: string } | null;
};

type NewsItem = {
    id: number;
    headline: string;
    section: string | null;
    published_at: string | null;
    url: string | null;
};

type SyncRun = {
    id: number;
    source: string;
    status: string;
    records_synced: number;
    started_at: string;
};

const { indicators, recentNews, lastSync } = defineProps<{
    indicators: IndicatorSummary[];
    recentNews: NewsItem[];
    lastSync: SyncRun[];
}>();

const categoryLabel: Record<string, string> = {
    wage: 'Wynagrodzenia',
    benefit: 'Świadczenia',
    macro: 'Makroekonomia',
};

defineOptions({
    layout: {
        breadcrumbs: [{ title: 'Panel', href: dashboard() }],
    },
});
</script>

<template>
    <Head title="Panel" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Zestawienie ekonomiczne
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Dane makroekonomiczne z GUS BDL oraz kontekst prasowy z NY
                Times.
            </p>
        </div>

        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            <Card v-for="item in indicators" :key="item.slug">
                <CardHeader class="pb-2">
                    <div class="flex items-start justify-between gap-2">
                        <CardTitle class="text-sm font-medium leading-snug">
                            {{ item.name }}
                        </CardTitle>
                        <TrendingUp
                            class="size-4 shrink-0 text-muted-foreground"
                        />
                    </div>
                    <CardDescription>
                        <Badge variant="outline" class="text-xs">
                            {{ categoryLabel[item.category] ?? item.category }}
                        </Badge>
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <p
                        v-if="item.latest"
                        class="text-2xl font-semibold tabular-nums"
                    >
                        {{ Number(item.latest.value).toLocaleString('pl-PL') }}
                        <span class="text-sm font-normal text-muted-foreground">
                            {{ item.unit }} ({{ item.latest.year }})
                        </span>
                    </p>
                    <p v-else class="text-sm text-muted-foreground">
                        Brak danych — uruchom synchronizację.
                    </p>
                </CardContent>
            </Card>
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <CardTitle class="text-base">Aktualności</CardTitle>
                        <Link
                            :href="newsRoute.index()"
                            class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                        >
                            Zobacz wszystkie
                            <ArrowRight class="size-4" />
                        </Link>
                    </div>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div
                        v-if="recentNews.length === 0"
                        class="text-sm text-muted-foreground"
                    >
                        Brak artykułów w bazie.
                    </div>
                    <article
                        v-for="article in recentNews"
                        :key="article.id"
                        class="border-b border-border pb-3 last:border-0 last:pb-0"
                    >
                        <div class="flex items-start gap-2">
                            <Newspaper
                                class="mt-0.5 size-4 shrink-0 text-muted-foreground"
                            />
                            <div>
                                <a
                                    v-if="article.url"
                                    :href="article.url"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-sm font-medium hover:underline"
                                >
                                    {{ article.headline }}
                                </a>
                                <p v-else class="text-sm font-medium">
                                    {{ article.headline }}
                                </p>
                                <p class="mt-0.5 text-xs text-muted-foreground">
                                    {{ article.section }}
                                    <span v-if="article.published_at">
                                        ·
                                        {{
                                            new Date(
                                                article.published_at,
                                            ).toLocaleDateString('pl-PL')
                                        }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </article>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle class="text-base"
                        >Ostatnia synchronizacja</CardTitle
                    >
                    <CardDescription>
                        Dane pobierane poleceniem
                        <code class="text-xs">php artisan integrations:sync</code>
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-3">
                    <div
                        v-if="lastSync.length === 0"
                        class="text-sm text-muted-foreground"
                    >
                        Nie wykonano jeszcze synchronizacji.
                    </div>
                    <div
                        v-for="run in lastSync"
                        :key="run.id"
                        class="flex items-center justify-between text-sm"
                    >
                        <span class="uppercase text-muted-foreground">{{
                            run.source
                        }}</span>
                        <span>
                            {{ run.status }} · {{ run.records_synced }} rek.
                        </span>
                        <span class="text-xs text-muted-foreground">
                            {{
                                new Date(run.started_at).toLocaleString('pl-PL')
                            }}
                        </span>
                    </div>
                    <Link
                        :href="comparisonRoute.index()"
                        class="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        Porównaj wskaźniki z aktualnościami
                        <ArrowRight class="size-4" />
                    </Link>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
