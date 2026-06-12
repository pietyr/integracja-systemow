<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { Filter } from '@lucide/vue';
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
import indicatorsRoute from '@/routes/indicators';

type IndicatorValue = { year: number; value: string };
type Indicator = {
    id: number;
    slug: string;
    name: string;
    category: string;
    unit: string | null;
    values: IndicatorValue[];
};

type Filters = {
    year_from: number;
    year_to: number;
    category: string | null;
};

const props = defineProps<{
    indicators: Indicator[];
    filters: Filters;
    categories: { value: string; label: string }[];
}>();

const yearFrom = ref(String(props.filters.year_from));
const yearTo = ref(String(props.filters.year_to));
const category = ref(props.filters.category ?? 'all');

const categoryLabel: Record<string, string> = {
    wage: 'Wynagrodzenia',
    benefit: 'Świadczenia',
    macro: 'Makroekonomia',
};

function applyFilters() {
    router.get(
        indicatorsRoute.index(),
        {
            year_from: yearFrom.value,
            year_to: yearTo.value,
            ...(category.value !== 'all' ? { category: category.value } : {}),
        },
        { preserveState: true, replace: true },
    );
}

watch(category, applyFilters);

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Panel', href: dashboard() },
            { title: 'Wskaźniki', href: indicatorsRoute.index() },
        ],
    },
});
</script>

<template>
    <Head title="Wskaźniki" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">Wskaźniki</h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Dane z GUS BDL — wynagrodzenia, świadczenia i wskaźniki
                makroekonomiczne w ujęciu rocznym.
            </p>
        </div>

        <Card>
            <CardHeader class="pb-4">
                <CardTitle class="flex items-center gap-2 text-base">
                    <Filter class="size-4" />
                    Filtry
                </CardTitle>
            </CardHeader>
            <CardContent>
                <form
                    class="grid gap-4 sm:grid-cols-4"
                    @submit.prevent="applyFilters"
                >
                    <div class="grid gap-2">
                        <Label for="year_from">Rok od</Label>
                        <Input
                            id="year_from"
                            v-model="yearFrom"
                            type="number"
                            min="1990"
                            :max="filters.year_to"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label for="year_to">Rok do</Label>
                        <Input
                            id="year_to"
                            v-model="yearTo"
                            type="number"
                            :min="filters.year_from"
                            max="2030"
                        />
                    </div>
                    <div class="grid gap-2">
                        <Label>Typ</Label>
                        <Select v-model="category">
                            <SelectTrigger>
                                <SelectValue placeholder="Wszystkie" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Wszystkie</SelectItem>
                                <SelectItem
                                    v-for="cat in categories"
                                    :key="cat.value"
                                    :value="cat.value"
                                >
                                    {{ cat.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>
                    <div class="flex items-end">
                        <Button type="submit" class="w-full sm:w-auto">
                            Zastosuj
                        </Button>
                    </div>
                </form>
            </CardContent>
        </Card>

        <div
            v-if="indicators.length === 0"
            class="rounded-lg border border-dashed p-8 text-center text-sm text-muted-foreground"
        >
            Brak danych. Uruchom
            <code class="text-xs">php artisan integrations:sync --source=gus</code>.
        </div>

        <Card v-for="indicator in indicators" :key="indicator.id">
            <CardHeader>
                <CardTitle class="text-base">{{ indicator.name }}</CardTitle>
                <CardDescription>
                    {{ categoryLabel[indicator.category] ?? indicator.category }}
                    <span v-if="indicator.unit"> · {{ indicator.unit }}</span>
                </CardDescription>
            </CardHeader>
            <CardContent class="overflow-x-auto">
                <table class="w-full min-w-[320px] text-sm">
                    <thead>
                        <tr class="border-b text-left text-muted-foreground">
                            <th class="pb-2 pr-4 font-medium">Rok</th>
                            <th class="pb-2 font-medium">Wartość</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr
                            v-for="row in indicator.values"
                            :key="row.year"
                            class="border-b border-border/50 last:border-0"
                        >
                            <td class="py-2 pr-4 tabular-nums">{{ row.year }}</td>
                            <td class="py-2 tabular-nums">
                                {{
                                    Number(row.value).toLocaleString('pl-PL', {
                                        maximumFractionDigits: 2,
                                    })
                                }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </CardContent>
        </Card>
    </div>
</template>
