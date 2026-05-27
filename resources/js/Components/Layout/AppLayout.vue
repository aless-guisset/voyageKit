<template>
    <div class="min-h-screen">
        <!-- ── Mobile header ─────────────────────────── -->
        <header class="page-header border-b border-stone-200/60 lg:hidden">
            <div class="flex items-center gap-3 min-w-0">
                <button v-if="back" @click="router.visit(back)" class="w-9 h-9 flex items-center justify-center rounded-xl bg-white border border-stone-200 shrink-0">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>
                <div class="min-w-0">
                    <h1 class="font-display font-700 text-lg leading-tight truncate">{{ title }}</h1>
                    <p v-if="subtitle" class="text-xs text-stone-400 truncate">{{ subtitle }}</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <slot name="header-actions" />
            </div>
        </header>

        <!-- ── Desktop sidebar ───────────────────────── -->
        <aside class="hidden lg:flex fixed inset-y-0 left-0 w-64 bg-[#1A1714] text-white flex-col z-30">
            <div class="px-6 py-6 border-b border-white/10">
                <Link href="/trips" class="flex items-center gap-3">
                    <span class="text-2xl">✈️</span>
                    <span class="font-display font-800 text-xl">VoyageKit</span>
                </Link>
            </div>
            <nav class="flex-1 px-3 py-4 space-y-1">
                <SideNavLink href="/trips" :active="isTripsActive">
                    <span class="text-base">🗺️</span> Voyages
                </SideNavLink>
                <SideNavLink href="/lists" :active="$page.url.startsWith('/lists')">
                    <span class="text-base">🧳</span> Mes listes
                </SideNavLink>
            </nav>
            <div class="px-4 py-4 border-t border-white/10">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-orange-500 flex items-center justify-center text-sm font-bold shrink-0">
                        {{ $page.props.auth?.user?.name?.charAt(0)?.toUpperCase() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium truncate">{{ $page.props.auth?.user?.name }}</p>
                    </div>
                    <Link href="/logout" method="post" as="button" class="text-white/40 hover:text-white transition-colors p-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- ── Desktop top bar ───────────────────────── -->
        <div class="hidden lg:flex fixed top-0 left-64 right-0 z-20 bg-[var(--sand)]/95 backdrop-blur border-b border-stone-200 px-8 py-4 items-center justify-between">
            <div>
                <h1 class="font-display font-700 text-xl">{{ title }}</h1>
                <p v-if="subtitle" class="text-sm text-stone-500 mt-0.5">{{ subtitle }}</p>
            </div>
            <slot name="header-actions" />
        </div>

        <!-- ── Content ────────────────────────────────── -->
        <div class="lg:ml-64 lg:pt-[73px]">
            <slot />
        </div>

        <!-- ── Mobile tab bar ─────────────────────────── -->
        <nav class="tab-bar lg:hidden">
            <Link href="/trips" class="tab-item" :class="{ active: isTripsActive }">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-[10px] font-medium">Voyages</span>
            </Link>
            <Link href="/lists" class="tab-item" :class="{ active: $page.url.startsWith('/lists') }">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="text-[10px] font-medium">Listes</span>
            </Link>
        </nav>

        <!-- Flash toast -->
        <Transition name="pop">
            <div v-if="flash" class="fixed bottom-24 left-1/2 -translate-x-1/2 z-50 bg-[#1A1714] text-white px-5 py-3 rounded-2xl shadow-2xl text-sm font-medium flex items-center gap-2 whitespace-nowrap lg:bottom-6 lg:left-auto lg:translate-x-0 lg:right-6">
                <span class="text-green-400">✓</span> {{ flash }}
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import SideNavLink from './NavLink.vue';

defineProps({ title: String, subtitle: String, back: String });

const page = usePage();
const flash = ref(null);
const isTripsActive = computed(() =>
    page.url.startsWith('/trips') && !page.url.includes('/lists')
);

watch(() => page.props.flash?.success, (val) => {
    if (val) {
        flash.value = val;
        setTimeout(() => { flash.value = null; }, 2500);
    }
}, { immediate: true });
</script>
