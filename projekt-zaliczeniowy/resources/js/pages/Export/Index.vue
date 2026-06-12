<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { Download, FileJson, FileCode } from '@lucide/vue';
import { ref } from 'vue';
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
import exportRoutes from '@/routes/export';

const yearFrom = ref('2010');
const yearTo = ref(String(new Date().getFullYear()));
const category = ref('all');

function downloadUrl(format: 'json' | 'xml') {
    const params = new URLSearchParams({
        format,
        year_from: yearFrom.value,
        year_to: yearTo.value,
    });

    if (category.value !== 'all') {
        params.set('category', category.value);
    }

    return `/eksport/pobierz?${params.toString()}`;
}

defineOptions({
    layout: {
        breadcrumbs: [
            { title: 'Panel', href: dashboard() },
            { title: 'Eksport', href: exportRoutes.index() },
        ],
    },
});
</script>

<template>
    <Head title="Eksport" />

    <div class="flex flex-1 flex-col gap-6 p-4">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight">
                Eksport danych
            </h1>
            <p class="mt-1 text-sm text-muted-foreground">
                Pobierz zestawienie wskaźników w formacie JSON lub XML. Ten sam
                zasób jest dostępny przez REST API pod adresem
                <code class="text-xs">/api/indicators/export</code> z tokenem
                JWT.
            </p>
        </div>

        <Card class="max-w-lg">
            <CardHeader>
                <CardTitle class="text-base">Parametry eksportu</CardTitle>
                <CardDescription>
                    Zakres lat i typ świadczenia / wskaźnika.
                </CardDescription>
            </CardHeader>
            <CardContent class="grid gap-4">
                <div class="grid gap-2">
                    <Label for="export_year_from">Rok od</Label>
                    <Input
                        id="export_year_from"
                        v-model="yearFrom"
                        type="number"
                    />
                </div>
                <div class="grid gap-2">
                    <Label for="export_year_to">Rok do</Label>
                    <Input id="export_year_to" v-model="yearTo" type="number" />
                </div>
                <div class="grid gap-2">
                    <Label>Typ</Label>
                    <Select v-model="category">
                        <SelectTrigger>
                            <SelectValue placeholder="Wszystkie" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="all">Wszystkie</SelectItem>
                            <SelectItem value="wage">Wynagrodzenia</SelectItem>
                            <SelectItem value="benefit">Świadczenia</SelectItem>
                            <SelectItem value="macro">Makroekonomia</SelectItem>
                        </SelectContent>
                    </Select>
                </div>
                <div class="flex flex-wrap gap-2 pt-2">
                    <Button as-child variant="default">
                        <a :href="downloadUrl('json')">
                            <FileJson class="size-4" />
                            Pobierz JSON
                        </a>
                    </Button>
                    <Button as-child variant="outline">
                        <a :href="downloadUrl('xml')">
                            <FileCode class="size-4" />
                            Pobierz XML
                        </a>
                    </Button>
                </div>
            </CardContent>
        </Card>

        <Card class="max-w-lg">
            <CardHeader>
                <CardTitle class="flex items-center gap-2 text-base">
                    <Download class="size-4" />
                    REST API
                </CardTitle>
            </CardHeader>
            <CardContent class="space-y-2 text-sm text-muted-foreground">
                <p>
                    <code>POST /api/auth/login</code> — uzyskanie tokenu JWT
                </p>
                <p>
                    <code>GET /api/indicators</code> — lista wskaźników z
                    wartościami
                </p>
                <p>
                    <code>GET /api/indicators/export?format=xml</code> —
                    eksport
                </p>
                <p><code>GET /api/news</code> — artykuły prasowe</p>
            </CardContent>
        </Card>
    </div>
</template>
