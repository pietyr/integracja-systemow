<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { LineChart } from '@lucide/vue';
import { Button } from '@/components/ui/button';
import { dashboard, login, register } from '@/routes';
</script>

<template>
    <Head title="Strona główna" />

    <div
        class="flex min-h-screen flex-col items-center justify-center bg-background p-6"
    >
        <header class="absolute top-0 right-0 p-6">
            <nav class="flex items-center gap-3">
                <Link v-if="$page.props.auth.user" :href="dashboard()">
                    <Button variant="outline" size="sm">Panel</Button>
                </Link>
                <template v-else>
                    <Link :href="login()">
                        <Button variant="ghost" size="sm">Logowanie</Button>
                    </Link>
                    <Link :href="register()">
                        <Button size="sm">Rejestracja</Button>
                    </Link>
                </template>
            </nav>
        </header>

        <main class="mx-auto max-w-2xl text-center">
            <div
                class="mx-auto mb-6 flex size-14 items-center justify-center rounded-xl bg-primary text-primary-foreground"
            >
                <LineChart class="size-7" />
            </div>
            <h1 class="text-3xl font-semibold tracking-tight">
                Zestawienie ekonomiczne
            </h1>
            <p class="mt-4 text-muted-foreground">
                Aplikacja integruje dane z GUS BDL (wynagrodzenia, świadczenia,
                inflacja, PKB) z artykułami prasowymi NY Times. Dane są
                pobierane do lokalnej bazy i prezentowane z podziałem na okresy
                oraz typy wskaźników.
            </p>
            <div class="mt-8 flex justify-center gap-3">
                <Link v-if="$page.props.auth.user" :href="dashboard()">
                    <Button>Przejdź do panelu</Button>
                </Link>
                <template v-else>
                    <Link :href="login()">
                        <Button>Zaloguj się</Button>
                    </Link>
                    <Link :href="register()">
                        <Button variant="outline">Utwórz konto</Button>
                    </Link>
                </template>
            </div>
        </main>
    </div>
</template>
