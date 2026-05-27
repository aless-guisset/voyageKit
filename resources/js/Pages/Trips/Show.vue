<template>
    <AppLayout :title="trip.name" :subtitle="trip.destination" back="/trips">
        <template #header-actions>
            <Link :href="`/trips/${trip.id}/edit`" class="btn-ghost btn-sm">✏️</Link>
        </template>

        <div class="page-body">
            <!-- Hero card -->
            <div class="card overflow-hidden">
                <div class="h-32 flex items-center justify-center text-5xl relative" :style="heroBg">
                    <span class="drop-shadow-lg">{{ trip.cover_emoji }}</span>
                    <span class="absolute top-3 right-3 badge" :class="statusClass(trip.status)">{{ statusLabel(trip.status) }}</span>
                </div>
                <div class="p-4 grid grid-cols-3 gap-4">
                    <div class="text-center" v-if="trip.duration_days">
                        <p class="font-display font-700 text-xl">{{ trip.duration_days }}</p>
                        <p class="text-xs text-stone-400">jours</p>
                    </div>
                    <div class="text-center" v-if="totalMembers">
                        <p class="font-display font-700 text-xl">{{ totalMembers }}</p>
                        <p class="text-xs text-stone-400">voyageurs</p>
                    </div>
                    <div class="text-center" v-if="trip.budget">
                        <p class="font-display font-700 text-xl">{{ formatCurrency(trip.budget.total_income) }}</p>
                        <p class="text-xs text-stone-400">budget</p>
                    </div>
                </div>
                <div v-if="trip.start_date" class="px-4 pb-4">
                    <p class="text-sm text-stone-500">
                        📅 {{ formatDate(trip.start_date) }}
                        <span v-if="trip.end_date"> → {{ formatDate(trip.end_date) }}</span>
                    </p>
                    <p v-if="trip.days_until > 0" class="text-sm text-orange-600 font-medium mt-1">
                        🚀 Départ dans {{ trip.days_until }} jour{{ trip.days_until > 1 ? 's' : '' }}
                    </p>
                    <p v-else-if="trip.days_until === 0" class="text-sm text-green-600 font-medium mt-1">🎉 C'est aujourd'hui !</p>
                </div>
            </div>

            <!-- Quick nav -->
            <div class="grid grid-cols-2 gap-3">
                <Link :href="`/trips/${trip.id}/groups`" class="card p-4 flex items-center gap-3 active:scale-95 transition-transform">
                    <span class="text-2xl">👨‍👩‍👧‍👦</span>
                    <div>
                        <p class="font-display font-700 text-sm">Groupes</p>
                        <p class="text-xs text-stone-400">{{ trip.groups?.length ?? 0 }} groupe(s)</p>
                    </div>
                </Link>
                <Link :href="`/trips/${trip.id}/itinerary`" class="card p-4 flex items-center gap-3 active:scale-95 transition-transform">
                    <span class="text-2xl">🗓️</span>
                    <div>
                        <p class="font-display font-700 text-sm">Itinéraire</p>
                        <p class="text-xs text-stone-400">{{ trip.itinerary_days?.length ?? 0 }} jour(s)</p>
                    </div>
                </Link>
                <Link :href="`/trips/${trip.id}/budget`" class="card p-4 flex items-center gap-3 active:scale-95 transition-transform">
                    <span class="text-2xl">💰</span>
                    <div>
                        <p class="font-display font-700 text-sm">Budget</p>
                        <p v-if="trip.budget" class="text-xs text-stone-400">{{ formatCurrency(trip.budget.remaining) }} restant</p>
                    </div>
                </Link>
                <Link href="/lists" class="card p-4 flex items-center gap-3 active:scale-95 transition-transform">
                    <span class="text-2xl">🧳</span>
                    <div>
                        <p class="font-display font-700 text-sm">Listes</p>
                        <p class="text-xs text-stone-400">{{ lists.length }} liste(s)</p>
                    </div>
                </Link>
            </div>

            <!-- Groups preview -->
            <div v-if="trip.groups?.length">
                <div class="flex items-center justify-between mb-3">
                    <p class="section-title mb-0">Voyageurs</p>
                    <Link :href="`/trips/${trip.id}/groups`" class="text-xs text-orange-600 font-medium">Gérer →</Link>
                </div>
                <div class="space-y-2">
                    <div v-for="group in trip.groups" :key="group.id" class="card p-4">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="text-xl">{{ group.icon }}</span>
                            <span class="font-display font-700 text-sm">{{ group.name }}</span>
                            <span class="badge bg-stone-100 text-stone-500 ml-auto">{{ group.members?.length }} pers.</span>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <div v-for="member in group.members" :key="member.id"
                                 class="flex items-center gap-1.5 bg-stone-50 rounded-xl px-3 py-1.5">
                                <span class="text-base">{{ member.avatar_emoji }}</span>
                                <span class="text-xs font-medium">{{ member.name }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div v-if="trip.notes" class="card p-4">
                <p class="label">Notes</p>
                <p class="text-sm text-stone-600 whitespace-pre-wrap">{{ trip.notes }}</p>
            </div>

            <!-- Danger zone -->
            <div class="pt-4">
                <Link :href="`/trips/${trip.id}`" method="delete" as="button"
                      class="text-xs text-red-400 hover:text-red-600 transition-colors"
                      @click.prevent="confirmDelete">
                    Supprimer ce voyage
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    lists: { type: Array, default: () => [] }, trip: Object });

const heroBg = computed(() => {
    const gs = ['linear-gradient(135deg,#FFF3E6,#FDE68A)', 'linear-gradient(135deg,#E6FFF8,#A7F3D0)',
                 'linear-gradient(135deg,#E6F0FF,#C7D2FE)', 'linear-gradient(135deg,#FFF0F6,#FDE68A)'];
    return { background: gs[props.trip.id % gs.length] };
});

const totalMembers = computed(() =>
    props.trip.groups?.reduce((sum, g) => sum + (g.members?.length ?? 0), 0) ?? 0
);

const STATUS = {
    planning:  { label: 'Planification', class: 'bg-yellow-100 text-yellow-700' },
    confirmed: { label: 'Confirmé',       class: 'bg-blue-100 text-blue-700' },
    ongoing:   { label: 'En cours',       class: 'bg-green-100 text-green-700' },
    completed: { label: 'Terminé',        class: 'bg-stone-100 text-stone-500' },
};
function statusLabel(s) { return STATUS[s]?.label ?? s; }
function statusClass(s) { return STATUS[s]?.class ?? ''; }
function formatDate(d) { return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' }); }
function formatCurrency(n) { return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: props.trip.budget?.currency ?? 'EUR' }).format(n ?? 0); }

function confirmDelete() {
    if (confirm('Supprimer ce voyage ? Cette action est irréversible.')) {
        router.delete(`/trips/${props.trip.id}`);
    }
}
</script>
