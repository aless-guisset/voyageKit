<template>
    <AppLayout :title="list.name" :subtitle="listTypeLabel" :back="backUrl">
        <template #header-actions>
            <button @click="showSaveTemplate = true" class="btn-ghost btn-sm">💾</button>
            <button @click="showAddItem = true" class="btn-primary btn-sm">+ Item</button>
        </template>

        <div class="page-body">
            <!-- Progress & totals -->
            <div class="card p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-display font-700 text-2xl">{{ list.progress?.percent ?? 0 }}%</p>
                        <p class="text-xs text-stone-400">{{ list.progress?.checked }}/{{ list.progress?.total }} items cochés</p>
                    </div>
                    <div class="text-right" v-if="list.estimated_total > 0">
                        <p class="font-display font-700 text-lg">{{ formatCurrency(list.estimated_total) }}</p>
                        <p class="text-xs text-stone-400">total estimé</p>
                    </div>
                </div>
                <ProgressBar :percent="list.progress?.percent ?? 0" />
                <div v-if="list.to_buy_total > 0" class="flex items-center justify-between text-sm pt-1 border-t border-stone-100">
                    <span class="text-stone-500 flex items-center gap-1">🛒 À acheter</span>
                    <span class="font-medium text-orange-600">{{ formatCurrency(list.to_buy_total) }}</span>
                </div>
            </div>

            <!-- Filter tabs -->
            <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-none">
                <button @click="activeCategory = null"
                        class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all"
                        :class="activeCategory === null ? 'bg-[#1A1714] text-white' : 'bg-white text-stone-500 border border-stone-200'">
                    Tout ({{ list.items?.length ?? 0 }})
                </button>
                <button @click="activeCategory = null; showToBuyOnly = !showToBuyOnly"
                        class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all flex items-center gap-1.5"
                        :class="showToBuyOnly ? 'bg-orange-500 text-white' : 'bg-white text-stone-500 border border-stone-200'">
                    🛒 À acheter
                </button>
                <button v-for="cat in categories" :key="cat"
                        @click="activeCategory = activeCategory === cat ? null : cat; showToBuyOnly = false"
                        class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all"
                        :class="activeCategory === cat ? 'bg-[#1A1714] text-white' : 'bg-white text-stone-500 border border-stone-200'">
                    {{ cat }}
                </button>
            </div>

            <!-- Items by category -->
            <div v-if="filteredItems.length === 0" class="card p-8 text-center">
                <p class="text-3xl mb-2">📋</p>
                <p class="text-stone-400 text-sm">Aucun item pour l'instant</p>
                <button @click="showAddItem = true" class="btn-primary btn-sm mt-4">+ Ajouter un item</button>
            </div>

            <div v-for="(items, category) in groupedItems" :key="category" class="space-y-1">
                <!-- Category header -->
                <div class="flex items-center gap-2 px-1 pt-2">
                    <p class="text-xs font-semibold text-stone-400 uppercase tracking-wide">{{ category || 'Autres' }}</p>
                    <div class="flex-1 h-px bg-stone-200" />
                    <p class="text-xs text-stone-400">{{ items.filter(i => i.is_checked).length }}/{{ items.length }}</p>
                </div>

                <!-- Items -->
                <div class="card divide-y divide-stone-50">
                    <div v-for="item in items" :key="item.id"
                         class="flex items-center gap-3 px-4 py-3.5 transition-colors"
                         :class="item.is_checked ? 'bg-stone-50/50' : ''">
                        <!-- Checkbox -->
                        <button @click="toggleItem(item)"
                                class="w-6 h-6 rounded-lg border-2 shrink-0 flex items-center justify-center transition-all"
                                :class="item.is_checked
                                    ? 'bg-green-500 border-green-500 text-white'
                                    : 'border-stone-300 hover:border-stone-400'">
                            <svg v-if="item.is_checked" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                            </svg>
                        </button>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium leading-tight" :class="item.is_checked ? 'line-through text-stone-400' : ''">
                                {{ item.name }}
                            </p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span v-if="item.quantity !== 1 || item.unit" class="text-xs text-stone-400">
                                    {{ item.quantity }}{{ item.unit ? ' ' + item.unit : '' }}
                                </span>
                                <span v-if="item.need_to_buy" class="badge bg-orange-100 text-orange-600 text-[10px]">🛒 À acheter</span>
                                <span v-if="item.subtotal" class="text-xs text-stone-400 font-medium">{{ formatCurrency(item.subtotal) }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-1 shrink-0">
                            <button @click="openEditItem(item)" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-stone-100 transition-colors text-stone-400">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button @click="deleteItem(item)" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-red-50 transition-colors text-stone-300 hover:text-red-400">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cost summary if prices entered -->
            <div v-if="list.estimated_total > 0" class="card p-4 space-y-3">
                <p class="section-title">💰 Résumé des coûts</p>
                <div class="space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-stone-500">Total estimé</span>
                        <span class="font-medium">{{ formatCurrency(list.estimated_total) }}</span>
                    </div>
                    <div v-if="list.to_buy_total > 0" class="flex justify-between text-sm">
                        <span class="text-stone-500">Dont à acheter</span>
                        <span class="font-medium text-orange-600">{{ formatCurrency(list.to_buy_total) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-stone-500">Déjà en stock</span>
                        <span class="font-medium text-green-600">{{ formatCurrency(list.estimated_total - list.to_buy_total) }}</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sheet: Add/Edit item -->
        <BottomSheet :show="showAddItem || !!editingItem"
                     :title="editingItem ? 'Modifier l\'item' : 'Nouvel item'"
                     @close="closeItemSheet">
            <form @submit.prevent="submitItem" class="space-y-4">
                <div>
                    <label class="label">Nom</label>
                    <input v-model="itemForm.name" class="input" placeholder="ex: T-shirt, Crème solaire…" required autofocus />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Quantité</label>
                        <input v-model="itemForm.quantity" type="number" step="0.5" min="0.5" class="input" placeholder="1" />
                    </div>
                    <div>
                        <label class="label">Unité</label>
                        <input v-model="itemForm.unit" class="input" placeholder="ex: pcs, kg, L…" />
                    </div>
                </div>
                <div>
                    <label class="label">Catégorie</label>
                    <div class="flex flex-wrap gap-2 mb-2">
                        <button v-for="cat in suggestedCategories" :key="cat" type="button"
                                @click="itemForm.category = cat"
                                class="px-3 py-1.5 rounded-xl text-xs font-medium transition-all"
                                :class="itemForm.category === cat ? 'bg-orange-100 text-orange-700 ring-1 ring-orange-300' : 'bg-stone-100 text-stone-600'">
                            {{ cat }}
                        </button>
                    </div>
                    <input v-model="itemForm.category" class="input" placeholder="ou saisir une catégorie…" />
                </div>
                <div>
                    <label class="label">Prix unitaire (optionnel)</label>
                    <div class="relative">
                        <input v-model="itemForm.unit_price" type="number" step="0.01" min="0" class="input pr-12" placeholder="0.00" />
                        <span class="absolute right-4 top-1/2 -translate-y-1/2 text-sm text-stone-400">€</span>
                    </div>
                    <p v-if="itemForm.unit_price && itemForm.quantity" class="text-xs text-stone-400 mt-1">
                        Sous-total : {{ formatCurrency(itemForm.unit_price * itemForm.quantity) }}
                    </p>
                </div>
                <div class="flex items-center justify-between bg-stone-50 rounded-2xl px-4 py-3">
                    <div>
                        <p class="text-sm font-medium">À acheter</p>
                        <p class="text-xs text-stone-400">Pas encore en stock</p>
                    </div>
                    <button type="button" @click="itemForm.need_to_buy = !itemForm.need_to_buy"
                            class="w-12 h-6 rounded-full transition-all relative"
                            :class="itemForm.need_to_buy ? 'bg-orange-500' : 'bg-stone-300'">
                        <span class="absolute top-0.5 w-5 h-5 bg-white rounded-full shadow transition-all"
                              :class="itemForm.need_to_buy ? 'left-6' : 'left-0.5'" />
                    </button>
                </div>
                <div>
                    <label class="label">Notes</label>
                    <input v-model="itemForm.notes" class="input" placeholder="optionnel…" />
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="closeItemSheet" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1" :disabled="itemForm.processing">
                        {{ editingItem ? 'Modifier' : 'Ajouter' }}
                    </button>
                </div>
            </form>
        </BottomSheet>

        <!-- Sheet: Save as template -->
        <BottomSheet :show="showSaveTemplate" title="Sauvegarder comme template" @close="showSaveTemplate = false">
            <form @submit.prevent="submitSaveTemplate" class="space-y-4">
                <p class="text-sm text-stone-500">Cette liste ({{ list.items?.length }} items) sera sauvegardée comme template réutilisable.</p>
                <div>
                    <label class="label">Nom du template</label>
                    <input v-model="templateForm.name" class="input" :placeholder="list.name" required />
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="showSaveTemplate = false" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1" :disabled="templateForm.processing">Sauvegarder 💾</button>
                </div>
            </form>
        </BottomSheet>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Components/Layout/AppLayout.vue';
import BottomSheet from '@/Components/UI/BottomSheet.vue';
import ProgressBar from '@/Components/UI/ProgressBar.vue';
import { useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({ list: Object });

const backUrl = computed(() => props.list.trip_id ? `/trips/${props.list.trip_id}/groups` : '/lists');

const LIST_TYPES = { packing: '🧳 Valise', grocery: '🛒 Courses', shopping: '🛍️ Shopping', todo: '✅ To-do' };
const listTypeLabel = computed(() => LIST_TYPES[props.list.type] ?? props.list.type);

function formatCurrency(n) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(n ?? 0);
}

// Filters
const activeCategory = ref(null);
const showToBuyOnly  = ref(false);

const categories = computed(() => {
    const cats = [...new Set(props.list.items?.map(i => i.category).filter(Boolean))];
    return cats.sort();
});

const filteredItems = computed(() => {
    let items = props.list.items ?? [];
    if (showToBuyOnly.value) items = items.filter(i => i.need_to_buy);
    if (activeCategory.value) items = items.filter(i => i.category === activeCategory.value);
    return items;
});

const groupedItems = computed(() => {
    const groups = {};
    for (const item of filteredItems.value) {
        const key = item.category || '';
        if (!groups[key]) groups[key] = [];
        groups[key].push(item);
    }
    return groups;
});

// Toggle item
function toggleItem(item) {
    router.patch(`/lists/${props.list.id}/items/${item.id}`, { is_checked: !item.is_checked }, { preserveScroll: true });
}

function deleteItem(item) {
    router.delete(`/lists/${props.list.id}/items/${item.id}`, { preserveScroll: true });
}

// Add/edit item
const showAddItem = ref(false);
const editingItem = ref(null);
const itemForm = useForm({ name: '', category: '', quantity: 1, unit: '', unit_price: '', need_to_buy: false, notes: '' });

function openEditItem(item) {
    editingItem.value = item;
    itemForm.name       = item.name;
    itemForm.category   = item.category ?? '';
    itemForm.quantity   = item.quantity;
    itemForm.unit       = item.unit ?? '';
    itemForm.unit_price = item.unit_price ?? '';
    itemForm.need_to_buy = item.need_to_buy;
    itemForm.notes      = item.notes ?? '';
    showAddItem.value   = true;
}
function closeItemSheet() { showAddItem.value = false; editingItem.value = null; itemForm.reset(); itemForm.quantity = 1; }
function submitItem() {
    if (editingItem.value) {
        itemForm.patch(`/lists/${props.list.id}/items/${editingItem.value.id}`, { onSuccess: closeItemSheet, preserveScroll: true });
    } else {
        itemForm.post(`/lists/${props.list.id}/items`, { onSuccess: () => { itemForm.reset(); itemForm.quantity = 1; showAddItem.value = false; }, preserveScroll: true });
    }
}

// Save template
const showSaveTemplate = ref(false);
const templateForm = useForm({ name: '' });
function submitSaveTemplate() {
    templateForm.post(`/lists/${props.list.id}/save-template`, { onSuccess: () => { showSaveTemplate.value = false; templateForm.reset(); } });
}

const suggestedCategories = [
    'Vêtements', 'Chaussures', 'Hygiène', 'Médicaments',
    'Tech', 'Documents', 'Nourriture', 'Boissons',
    'Enfants', 'Sport', 'Loisirs', 'Divers',
];
</script>

<style scoped>
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>
