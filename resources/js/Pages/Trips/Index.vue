<template>
    <AppLayout title="Mes voyages">
        <template #header-actions>
            <Link href="/trips/create" class="btn-primary btn-sm">+ Voyage</Link>
        </template>

        <div class="page-body">
            <!-- Empty state -->
            <div v-if="trips.length === 0" class="flex flex-col items-center justify-center py-20 text-center">
                <div class="text-6xl mb-4">🌍</div>
                <h2 class="font-display text-xl font-700 mb-2">Aucun voyage</h2>
                <p class="text-stone-400 text-sm mb-6 max-w-xs">Créez votre premier voyage pour commencer à planifier.</p>
                <Link href="/trips/create" class="btn-primary">✈️ Créer un voyage</Link>
            </div>

            <!-- Trips list -->
            <div v-else class="space-y-3">
                <!-- Upcoming / ongoing -->
                <template v-if="upcoming.length">
                    <p class="label">À venir</p>
                    <Link v-for="trip in upcoming" :key="trip.id" :href="`/trips/${trip.id}`" class="card flex items-center gap-4 p-4 active:scale-98 transition-transform">
                        <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl shrink-0" :style="cardBg(trip)">
                            {{ trip.cover_emoji }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-0.5">
                                <span class="font-display font-700 text-base truncate">{{ trip.name }}</span>
                                <span class="badge shrink-0" :class="statusClass(trip.status)">{{ statusLabel(trip.status) }}</span>
                            </div>
                            <p v-if="trip.destination" class="text-xs text-stone-400 truncate mb-1">📍 {{ trip.destination }}</p>
                            <div class="flex items-center gap-3 text-xs text-stone-400">
                                <span v-if="trip.start_date">{{ formatDate(trip.start_date) }}</span>
                                <span v-if="trip.duration_days">· {{ trip.duration_days }}j</span>
                                <span v-if="trip.days_until > 0" class="text-orange-500 font-medium">· dans {{ trip.days_until }}j</span>
                                <span v-else-if="trip.days_until === 0" class="text-green-500 font-medium">· Aujourd'hui !</span>
                            </div>
                        </div>
                        <svg class="w-4 h-4 text-stone-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </template>

                <!-- Past -->
                <template v-if="past.length">
                    <p class="label mt-4">Passés</p>
                    <Link v-for="trip in past" :key="trip.id" :href="`/trips/${trip.id}`" class="card flex items-center gap-4 p-4 opacity-70">
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl shrink-0 bg-stone-100">
                            {{ trip.cover_emoji }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-display font-600 text-sm truncate">{{ trip.name }}</p>
                            <p class="text-xs text-stone-400">{{ trip.destination }} · {{ trip.duration_days }}j</p>
                        </div>
                        <svg class="w-4 h-4 text-stone-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                        </svg>
                    </Link>
                </template>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({ trips: Array });

const upcoming = computed(() => props.trips.filter(t => t.status !== 'completed'));
const past = computed(() => props.trips.filter(t => t.status === 'completed'));

const GRADIENTS = [
    { bg: '#FFF3E6', emoji: '' }, { bg: '#E6FFF8', emoji: '' },
    { bg: '#E6F0FF', emoji: '' }, { bg: '#FFF0F6', emoji: '' },
    { bg: '#FFFFF0', emoji: '' },
];

function cardBg(trip) {
    return { background: GRADIENTS[trip.id % GRADIENTS.length].bg };
}

function formatDate(d) {
    return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' });
}

const STATUS = {
    planning:  { label: 'Planif.',  class: 'bg-yellow-100 text-yellow-700' },
    confirmed: { label: 'Confirmé', class: 'bg-blue-100 text-blue-700' },
    ongoing:   { label: 'En cours', class: 'bg-green-100 text-green-700' },
    completed: { label: 'Terminé',  class: 'bg-stone-100 text-stone-500' },
};
function statusLabel(s) { return STATUS[s]?.label ?? s; }
function statusClass(s) { return STATUS[s]?.class ?? ''; }
</script>
