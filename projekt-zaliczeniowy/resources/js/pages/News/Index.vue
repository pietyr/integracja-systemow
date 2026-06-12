<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { ExternalLink, Search } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { dashboard } from '@/routes';
import news from '@/routes/news';

type Article = {
    id: number;
    headline: string;
    snippet: string | null;
    section: string | null;
    published_at: string | null;
    url: string | null;
};

type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
};

const props = defineProps<{
    articles: Paginated<Article>;
    sections: string[];
    years: number[];
    filters: { section?: string; year?: string; search?: string };
}>();

const search = ref(props.filters.search ?? '');
const section = ref(props.filters.section ?? 'all');
const year = ref(props.filters.year ?? 'all');

function applyFilters() {
    router.get(
        news.index(),
        {
            ...(search.value ? { search: search.value } : {}),
            ...(section.value !== 'all' ? { section: section.value } : {}),
            ...(year.value !== 'all' ? { year: year.value } : {}),
        },
        { preserveState: true, replace: true },
    );
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Panel', href: dashboard() },
            { title: 'Aktualności', href: news.index() },
        ],
    },
});
</script>

<template>
    <Head title="Aktualności" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Aktualności</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Artykuły z NY Times Archive API — wydarzenia gospodarcze i
                społeczne w kontekście analizowanych wskaźników.
            </p>
        </div>

        <Card>
            <CardContent class="pt-6">
                <form
                    class="grid gap-4 md:grid-cols-4"
                    @submit.prevent="applyFilters"
                >
                    <div class="grid gap-2 md:col-span-2">
                        <Label for="search">Szukaj</Label>
                        <div class="relative">
                            <Search
                                class="absolute top-2.5 left-2.5 size-4 text-muted-foreground"
                            />
                            <Input
                                id="search"
                                v-model="search"
                                class="pl-8"
                                placeholder="Tytuł lub fragment..."
                            />
                        </div>
                    </div>
                    <div class="grid gap-2">
                        <Label>Sekcja</Label>
                        <Select v-model="section">
                            <SelectTrigger>
                                <SelectValue placeholder="Wszystkie" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Wszystkie</SelectItem>
                                <SelectItem
                                    v-for="s in sections"
                                    :key="s"
                                    :value="s"
                                >
                                    {{ s }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="grid gap-2">
                        <Label>Rok</Label>
                        <Select v-model="year">
                            <SelectTrigger>
                                <SelectValue placeholder="Wszystkie" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Wszystkie</SelectItem>
                                <SelectItem
                                    v-for="y in years"
                                    :key="y"
                                    :value="String(y)"
                                >
                                    {{ y }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex items-end md:col-span-4">
                        <Button type="submit">Filtruj</Button>
                    </div>
                </form>
            </CardContent>
        </Card>

        <div
            v-if="articles.data.length === 0"
            class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground"
        >
            Brak artykułów. Uruchom
            <code class="text-xs"
                >php artisan integrations:sync --source=nytimes</code
            >.
        </div>

        <div class="space-y-3">
            <Card v-for="article in articles.data" :key="article.id">
                <CardHeader class="pb-2">
                    <CardTitle class="text-base leading-snug">
                        <a
                            v-if="article.url"
                            :href="article.url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-start gap-1 hover:underline"
                        >
                            {{ article.headline }}
                            <ExternalLink class="mt-0.5 size-3.5 shrink-0" />
                        </a>
                        <span v-else>{{ article.headline }}</span>
                    </CardTitle>
                    <CardDescription>
                        {{ article.section }}
                        <span v-if="article.published_at">
                            ·
                            {{
                                new Date(article.published_at).toLocaleDateString(
                                    'pl-PL',
                                )
                            }}
                        </span>
                    </CardDescription>
                </CardHeader>
                <CardContent v-if="article.snippet">
                    <p class="text-sm text-muted-foreground line-clamp-2">
                        {{ article.snippet }}
                    </p>
                </CardContent>
            </Card>
        </div>

        <nav
            v-if="articles.last_page > 1"
            class="flex flex-wrap gap-1"
            aria-label="Paginacja"
        >
            <Link
                v-for="link in articles.links"
                :key="link.label"
                :href="link.url ?? '#'"
                class="rounded-md border px-3 py-1 text-sm"
                :class="
                    link.active
                        ? 'border-primary bg-primary text-primary-foreground'
                        : 'border-border text-muted-foreground hover:bg-muted'
                "
                v-html="link.label"
            />
        </nav>
    </div>
</template>
