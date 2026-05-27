<template>
    <AppLayout title="Mes listes">
        <template #header-actions>
            <button @click="openCreateSheet" class="btn-primary btn-sm">+ Liste</button>
        </template>

        <div class="page-body">
            <!-- Tabs -->
            <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-none">
                <button @click="activeTab = 'by-trip'"
                        class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all"
                        :class="activeTab === 'by-trip' ? 'bg-[#1A1714] text-white' : 'bg-white text-stone-500 border border-stone-200'">
                    ✈️ Par voyage ({{ tripListCount }})
                </button>
                <button @click="activeTab = 'all'"
                        class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all"
                        :class="activeTab === 'all' ? 'bg-[#1A1714] text-white' : 'bg-white text-stone-500 border border-stone-200'">
                    📋 Toutes ({{ lists.length }})
                </button>
                <button @click="activeTab = 'templates'"
                        class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all"
                        :class="activeTab === 'templates' ? 'bg-[#1A1714] text-white' : 'bg-white text-stone-500 border border-stone-200'">
                    🗂️ Templates ({{ templates.length }})
                </button>
            </div>

            <!-- ── Par voyage (défaut) ─────────────────── -->
            <template v-if="activeTab === 'by-trip'">
                <div v-if="!tripsWithAnyList.length" class="card p-8 text-center">
                    <div class="text-4xl mb-3">✈️</div>
                    <p class="font-display font-700 mb-1">Aucune liste liée à un voyage</p>
                    <p class="text-sm text-stone-400 mb-4">Créez une liste et choisissez un voyage</p>
                    <button @click="openCreateSheet" class="btn-primary btn-sm">+ Créer une liste</button>
                </div>

                <div v-for="trip in tripsWithAnyList" :key="trip.id" class="space-y-2">
                    <!-- Trip header -->
                    <Link :href="'/trips/' + trip.id"
                          class="flex items-center gap-3 bg-white rounded-2xl px-4 py-3 border border-stone-100 shadow-sm active:scale-98 transition-transform">
                        <span class="text-xl">{{ trip.cover_emoji }}</span>
                        <div class="flex-1 min-w-0">
                            <p class="font-display font-700 text-sm truncate">{{ trip.name }}</p>
                            <p v-if="trip.destination" class="text-xs text-stone-400 truncate">📍 {{ trip.destination }}</p>
                        </div>
                        <span class="badge bg-stone-100 text-stone-500 shrink-0">{{ countTripLists(trip) }} liste(s)</span>
                    </Link>

                    <!-- Listes directes (sans membre) -->
                    <div v-if="trip.direct_lists?.length" class="card divide-y divide-stone-50">
                        <div v-for="list in trip.direct_lists" :key="list.id"
                             class="flex items-center gap-3 px-4 py-3">
                            <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg shrink-0"
                                 :style="{ background: TYPE_BG[list.type] ?? '#F5F5F4' }">{{ list.icon }}</div>
                            <a :href="'/lists/' + list.id" class="flex-1 min-w-0">
                                <p class="text-sm font-medium truncate">{{ list.name }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <div class="flex-1 h-1.5 bg-stone-100 rounded-full overflow-hidden">
                                        <div class="h-full bg-orange-400 rounded-full" :style="{ width: (list.progress?.percent ?? 0) + '%' }"/>
                                    </div>
                                    <span class="text-xs text-stone-400">{{ list.progress?.checked ?? 0 }}/{{ list.progress?.total ?? 0 }}</span>
                                </div>
                            </a>
                            <button @click="deleteList(list)"
                                    class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-red-50 text-stone-300 hover:text-red-400 transition-colors shrink-0">🗑️</button>
                        </div>
                    </div>

                    <!-- Listes par groupe > membre -->
                    <div v-for="group in trip.groups" :key="group.id">
                        <template v-for="member in group.members" :key="member.id">
                            <div v-if="member.packing_lists?.length" class="space-y-1">
                                <div class="flex items-center gap-2 px-2 pt-1">
                                    <span class="text-sm">{{ group.icon }}</span>
                                    <div class="w-5 h-5 rounded-lg flex items-center justify-center text-xs"
                                         :style="{ background: member.color + '33' }">{{ member.avatar_emoji }}</div>
                                    <p class="text-xs font-semibold text-stone-400">{{ member.name }}</p>
                                    <div class="flex-1 h-px bg-stone-200"/>
                                </div>
                                <div class="card divide-y divide-stone-50">
                                    <div v-for="list in member.packing_lists" :key="list.id"
                                         class="flex items-center gap-3 px-4 py-3">
                                        <div class="w-9 h-9 rounded-xl flex items-center justify-center text-lg shrink-0"
                                             :style="{ background: TYPE_BG[list.type] ?? '#F5F5F4' }">{{ list.icon }}</div>
                                        <a :href="'/lists/' + list.id" class="flex-1 min-w-0">
                                            <p class="text-sm font-medium truncate">{{ list.name }}</p>
                                            <div class="flex items-center gap-2 mt-0.5">
                                                <div class="flex-1 h-1.5 bg-stone-100 rounded-full overflow-hidden">
                                                    <div class="h-full bg-orange-400 rounded-full" :style="{ width: (list.progress?.percent ?? 0) + '%' }"/>
                                                </div>
                                                <span class="text-xs text-stone-400">{{ list.progress?.checked ?? 0 }}/{{ list.progress?.total ?? 0 }}</span>
                                            </div>
                                        </a>
                                        <button @click="deleteList(list)"
                                                class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-red-50 text-stone-300 hover:text-red-400 transition-colors shrink-0">🗑️</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </template>

            <!-- ── Toutes ─────────────────────────────── -->
            <template v-if="activeTab === 'all'">
                <div v-if="!lists.length" class="card p-8 text-center">
                    <div class="text-4xl mb-3">📋</div>
                    <p class="font-display font-700 mb-1">Aucune liste</p>
                    <button @click="openCreateSheet" class="btn-primary btn-sm mt-3">+ Créer une liste</button>
                </div>
                <div v-else class="space-y-2">
                    <div v-for="list in lists" :key="list.id"
                         class="card flex items-center gap-3 p-4">
                        <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-xl shrink-0"
                             :style="{ background: TYPE_BG[list.type] ?? '#F5F5F4' }">{{ list.icon }}</div>
                        <a :href="'/lists/' + list.id" class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <p class="font-display font-700 text-sm truncate">{{ list.name }}</p>
                                <span class="badge text-[10px] shrink-0" :class="TYPE_CLASSES[list.type]">{{ TYPE_LABELS[list.type] }}</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-stone-100 rounded-full overflow-hidden">
                                    <div class="h-full bg-orange-400 rounded-full" :style="{ width: (list.progress?.percent ?? 0) + '%' }"/>
                                </div>
                                <span class="text-xs text-stone-400">{{ list.progress?.checked ?? 0 }}/{{ list.progress?.total ?? 0 }}</span>
                            </div>
                            <p v-if="list.trip" class="text-xs text-stone-400 mt-0.5">✈️ {{ list.trip.name }}</p>
                        </a>
                        <button @click="deleteList(list)"
                                class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-red-50 text-stone-300 hover:text-red-400 transition-colors shrink-0">🗑️</button>
                    </div>
                </div>
            </template>

            <!-- ── Templates ─────────────────────────── -->
            <template v-if="activeTab === 'templates'">
                <div v-if="!templates.length" class="card p-8 text-center">
                    <div class="text-4xl mb-3">🗂️</div>
                    <p class="font-display font-700 mb-1">Aucun template</p>
                    <p class="text-sm text-stone-400">Depuis une liste, appuyez sur 💾 pour la sauvegarder</p>
                </div>
                <div v-else class="space-y-2">
                    <div v-for="tmpl in templates" :key="tmpl.id" class="card p-4 flex items-center gap-4">
                        <div class="w-12 h-12 rounded-2xl bg-stone-100 flex items-center justify-center text-2xl shrink-0">{{ tmpl.icon }}</div>
                        <div class="flex-1 min-w-0">
                            <p class="font-display font-700 text-sm truncate">{{ tmpl.name }}</p>
                            <p class="text-xs text-stone-400">{{ tmpl.items_count }} items · {{ TYPE_LABELS[tmpl.type] }}</p>
                        </div>
                        <button @click="useTemplate(tmpl)" class="btn-primary btn-sm shrink-0">Utiliser</button>
                    </div>
                </div>
            </template>
        </div>

        <!-- Sheet: Créer liste -->
        <BottomSheet :show="showCreateList" title="Nouvelle liste" @close="closeSheet">
            <form @submit.prevent="submitList" class="space-y-4">
                <div>
                    <label class="label">Nom</label>
                    <input v-model="listForm.name" class="input" placeholder="ex: Valise été, Courses plage…" required />
                </div>
                <div>
                    <label class="label">Type</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button v-for="t in listTypes" :key="t.value" type="button"
                                @click="listForm.type = t.value; listForm.icon = t.icon"
                                class="py-3 rounded-xl border text-sm font-medium transition-all flex items-center justify-center gap-2"
                                :class="listForm.type === t.value ? 'border-orange-400 bg-orange-50 text-orange-700' : 'border-stone-200 bg-white text-stone-600'">
                            {{ t.icon }} {{ t.label }}
                        </button>
                    </div>
                </div>

                <!-- Voyage -->
                <div v-if="trips.length">
                    <label class="label">Voyage (optionnel)</label>
                    <div class="space-y-2 max-h-36 overflow-y-auto pr-1">
                        <button type="button" @click="listForm.trip_id = null"
                                class="w-full text-left px-4 py-3 rounded-xl border text-sm transition-all flex items-center gap-3"
                                :class="!listForm.trip_id ? 'border-orange-400 bg-orange-50' : 'border-stone-200 bg-white'">
                            <span>📋</span><span>Sans voyage</span>
                        </button>
                        <button v-for="trip in trips" :key="trip.id" type="button"
                                @click="listForm.trip_id = trip.id"
                                class="w-full text-left px-4 py-3 rounded-xl border text-sm transition-all flex items-center gap-3"
                                :class="listForm.trip_id === trip.id ? 'border-orange-400 bg-orange-50' : 'border-stone-200 bg-white'">
                            <span>{{ trip.cover_emoji }}</span>
                            <span class="flex-1 font-medium">{{ trip.name }}</span>
                            <span v-if="trip.destination" class="text-xs text-stone-400">{{ trip.destination }}</span>
                        </button>
                    </div>
                </div>

                <!-- Templates -->
                <div v-if="templates.length">
                    <label class="label">Partir d'un template</label>
                    <div class="space-y-2 max-h-40 overflow-y-auto pr-1">
                        <button type="button" @click="listForm.list_template_id = null"
                                class="w-full text-left px-4 py-3 rounded-xl border text-sm transition-all flex items-center gap-3"
                                :class="!listForm.list_template_id ? 'border-orange-400 bg-orange-50' : 'border-stone-200 bg-white'">
                            <span>✨</span><span class="font-medium">Liste vide</span>
                        </button>
                        <button v-for="t in templates" :key="t.id" type="button"
                                @click="listForm.list_template_id = t.id; listForm.name = listForm.name || t.name; listForm.type = t.type; listForm.icon = t.icon"
                                class="w-full text-left px-4 py-3 rounded-xl border text-sm transition-all flex items-center gap-3"
                                :class="listForm.list_template_id === t.id ? 'border-orange-400 bg-orange-50' : 'border-stone-200 bg-white'">
                            <span>{{ t.icon }}</span>
                            <span class="flex-1 font-medium">{{ t.name }}</span>
                            <span class="badge bg-stone-100 text-stone-500">{{ t.items_count }}</span>
                        </button>
                    </div>
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="button" @click="closeSheet" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1" :disabled="listForm.processing">Créer</button>
                </div>
            </form>
        </BottomSheet>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Components/Layout/AppLayout.vue';
import BottomSheet from '@/Components/UI/BottomSheet.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    lists:     { type: Array, default: () => [] },
    trips:     { type: Array, default: () => [] },
    templates: { type: Array, default: () => [] },
});

const TYPE_BG     = { packing: '#FFF3E6', grocery: '#E6FFF8', shopping: '#FFF0F6', todo: '#E6F0FF' };
const TYPE_LABELS = { packing: '🧳 Valise', grocery: '🛒 Courses', shopping: '🛍️ Shopping', todo: '✅ To-do' };
const TYPE_CLASSES= { packing: 'bg-orange-100 text-orange-700', grocery: 'bg-teal-100 text-teal-700', shopping: 'bg-pink-100 text-pink-700', todo: 'bg-blue-100 text-blue-700' };

// Tab par défaut = par-voyage
const activeTab      = ref('by-trip');
const showCreateList = ref(false);

// Pré-sélectionner le premier voyage si on est sur "par-voyage"
const listForm = useForm({
    name: '', type: 'packing', icon: '🧳',
    trip_id: null,
    list_template_id: null,
});

function openCreateSheet() {
    // Pré-sélectionner le voyage si on est dans l'onglet par-voyage et qu'il n'y en a qu'un
    if (activeTab.value === 'by-trip' && props.trips.length === 1) {
        listForm.trip_id = props.trips[0].id;
    }
    showCreateList.value = true;
}

const tripsWithAnyList = computed(() =>
    props.trips.filter(trip =>
        (trip.direct_lists?.length > 0) ||
        trip.groups?.some(g => g.members?.some(m => m.packing_lists?.length > 0))
    )
);

const tripListCount = computed(() => props.lists.filter(l => l.trip_id).length);

function countTripLists(trip) {
    const direct = trip.direct_lists?.length ?? 0;
    const member = trip.groups?.reduce((s, g) =>
        s + (g.members?.reduce((ss, m) => ss + (m.packing_lists?.length ?? 0), 0) ?? 0), 0) ?? 0;
    return direct + member;
}

function deleteList(list) {
    if (confirm('Supprimer "' + list.name + '" ?')) {
        router.delete('/lists/' + list.id, { preserveScroll: true });
    }
}

function closeSheet() {
    showCreateList.value = false;
    listForm.reset();
    listForm.type = 'packing';
    listForm.icon = '🧳';
}

function submitList() {
    listForm.post('/lists', { onSuccess: closeSheet });
}

function useTemplate(tmpl) {
    listForm.name = tmpl.name; listForm.type = tmpl.type;
    listForm.icon = tmpl.icon; listForm.list_template_id = tmpl.id;
    showCreateList.value = true;
}

const listTypes = [
    { value: 'packing',  icon: '🧳', label: 'Valise'   },
    { value: 'grocery',  icon: '🛒', label: 'Courses'  },
    { value: 'shopping', icon: '🛍️', label: 'Shopping' },
    { value: 'todo',     icon: '✅', label: 'To-do'    },
];
</script>

<style scoped>
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>
