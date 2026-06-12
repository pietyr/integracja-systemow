<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import {
    Download,
    GitCompare,
    LayoutGrid,
    LineChart,
    Newspaper,
} from '@lucide/vue';
import AppLogo from '@/components/AppLogo.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { dashboard } from '@/routes';
import comparisonRoute from '@/routes/comparison';
import exportRoutes from '@/routes/export';
import indicatorsRoute from '@/routes/indicators';
import newsRoute from '@/routes/news';
import type { NavItem } from '@/types';

const mainNavItems: NavItem[] = [
    {
        title: 'Panel',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Porównanie',
        href: comparisonRoute.index(),
        icon: GitCompare,
    },
    {
        title: 'Wskaźniki',
        href: indicatorsRoute.index(),
        icon: LineChart,
    },
    {
        title: 'Aktualności',
        href: newsRoute.index(),
        icon: Newspaper,
    },
    {
        title: 'Eksport',
        href: exportRoutes.index(),
        icon: Download,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
