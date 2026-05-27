<template>
    <AppLayout :title="trip.name" subtitle="Budget" :back="`/trips/${trip.id}`">
        <div class="page-body">

            <!-- ── Global summary ─────────────────────── -->
            <div class="card overflow-hidden">
                <div class="bg-gradient-to-br from-[#1A1714] to-stone-700 text-white p-5">
                    <p class="text-xs text-white/60 uppercase tracking-wide mb-1">Budget total</p>
                    <p class="font-display font-800 text-3xl mb-3">{{ formatCurrency(budget.total_income) }}</p>
                    <div class="grid grid-cols-3 gap-3 text-center">
                        <div>
                            <p class="font-display font-700 text-lg text-green-400">{{ formatCurrency(budget.remaining) }}</p>
                            <p class="text-xs text-white/50">Restant</p>
                        </div>
                        <div>
                            <p class="font-display font-700 text-lg text-red-400">{{ formatCurrency(budget.total_spent) }}</p>
                            <p class="text-xs text-white/50">Dépensé</p>
                        </div>
                        <div>
                            <p class="font-display font-700 text-lg text-yellow-400">{{ formatCurrency(budget.total_planned) }}</p>
                            <p class="text-xs text-white/50">Prévu</p>
                        </div>
                    </div>
                </div>
                <!-- Spending bar -->
                <div class="px-5 py-3 space-y-1">
                    <div class="flex justify-between text-xs text-stone-400">
                        <span>Consommé</span>
                        <span>{{ spentPercent }}%</span>
                    </div>
                    <div class="h-2.5 bg-stone-100 rounded-full overflow-hidden flex">
                        <div class="h-full bg-red-400 transition-all duration-700" :style="{ width: spentPercent + '%' }" />
                        <div class="h-full bg-yellow-300 transition-all duration-700" :style="{ width: plannedPercent + '%' }" />
                    </div>
                    <div class="flex gap-4 text-xs text-stone-400 pt-0.5">
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-400 inline-block" />Réel</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-yellow-300 inline-block" />Prévu</span>
                        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-stone-200 inline-block" />Libre</span>
                    </div>
                </div>
                <!-- Budget target -->
                <div v-if="budget.total_target" class="px-5 pb-4 flex items-center justify-between text-sm border-t border-stone-100 pt-3">
                    <span class="text-stone-500">Objectif</span>
                    <span class="font-medium">{{ formatCurrency(budget.total_target) }}</span>
                </div>
            </div>

            <!-- ── Section tabs ────────────────────────── -->
            <div class="flex gap-2 overflow-x-auto pb-1 -mx-4 px-4 scrollbar-none">
                <button v-for="tab in tabs" :key="tab.id" @click="activeTab = tab.id"
                        class="shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-all"
                        :class="activeTab === tab.id ? 'bg-[#1A1714] text-white' : 'bg-white text-stone-500 border border-stone-200'">
                    {{ tab.icon }} {{ tab.label }}
                </button>
            </div>

            <!-- ── Incomes tab ─────────────────────────── -->
            <template v-if="activeTab === 'incomes'">
                <div class="flex items-center justify-between">
                    <p class="section-title mb-0">Entrées d'argent</p>
                    <button @click="showAddIncome = true" class="btn-primary btn-sm">+ Entrée</button>
                </div>

                <div v-if="!budget.incomes?.length" class="card p-6 text-center">
                    <p class="text-2xl mb-2">💰</p>
                    <p class="text-sm text-stone-400 mb-3">Ajoutez vos entrées d'argent semaine par semaine</p>
                    <button @click="showAddIncome = true" class="btn-primary btn-sm">Ajouter une entrée</button>
                </div>

                <div v-else class="card divide-y divide-stone-50">
                    <div v-for="income in budget.incomes" :key="income.id" class="flex items-center gap-3 p-4">
                        <div class="w-10 h-10 rounded-xl bg-green-100 flex items-center justify-center text-lg shrink-0">💵</div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ income.label }}</p>
                            <p class="text-xs text-stone-400">{{ income.date ? formatDate(income.date) : 'Sans date' }}</p>
                        </div>
                        <p class="font-display font-700 text-green-600 shrink-0">+{{ formatCurrency(income.amount) }}</p>
                        <button @click="openEditIncome(income)" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-stone-100 text-stone-400 shrink-0">✏️</button>
                        <button @click="deleteIncome(income)" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-red-50 text-stone-300 hover:text-red-400 shrink-0">🗑️</button>
                    </div>
                </div>

                <!-- Total row -->
                <div v-if="budget.incomes?.length" class="flex justify-between items-center px-1">
                    <span class="text-sm text-stone-500">Total entré</span>
                    <span class="font-display font-700 text-green-600">{{ formatCurrency(budget.total_income) }}</span>
                </div>
            </template>

            <!-- ── Expenses tab ────────────────────────── -->
            <template v-if="activeTab === 'expenses'">
                <div class="flex items-center justify-between">
                    <p class="section-title mb-0">Dépenses</p>
                    <button @click="showAddExpense = true" class="btn-primary btn-sm">+ Dépense</button>
                </div>

                <!-- By category summary -->
                <div v-if="Object.keys(expensesByCategory).length" class="card p-4">
                    <p class="label mb-3">Par catégorie</p>
                    <div class="space-y-2.5">
                        <div v-for="(amount, cat) in expensesByCategory" :key="cat">
                            <div class="flex justify-between text-xs text-stone-600 mb-1">
                                <span class="flex items-center gap-1.5">{{ categoryIcon(cat) }} {{ cat }}</span>
                                <span class="font-medium">{{ formatCurrency(amount) }}</span>
                            </div>
                            <div class="h-1.5 bg-stone-100 rounded-full overflow-hidden">
                                <div class="h-full bg-red-400 rounded-full" :style="{ width: categoryPercent(amount) + '%' }" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter: actual/planned -->
                <div class="flex gap-2">
                    <button @click="expenseFilter = null"
                            class="flex-1 py-2 rounded-xl text-sm font-medium transition-all border"
                            :class="!expenseFilter ? 'bg-[#1A1714] text-white border-transparent' : 'bg-white text-stone-500 border-stone-200'">
                        Tout
                    </button>
                    <button @click="expenseFilter = 'actual'"
                            class="flex-1 py-2 rounded-xl text-sm font-medium transition-all border"
                            :class="expenseFilter === 'actual' ? 'bg-red-500 text-white border-transparent' : 'bg-white text-stone-500 border-stone-200'">
                        💸 Réelles
                    </button>
                    <button @click="expenseFilter = 'planned'"
                            class="flex-1 py-2 rounded-xl text-sm font-medium transition-all border"
                            :class="expenseFilter === 'planned' ? 'bg-yellow-400 text-[#1A1714] border-transparent' : 'bg-white text-stone-500 border-stone-200'">
                        📋 Prévues
                    </button>
                </div>

                <div v-if="!filteredExpenses.length" class="card p-6 text-center">
                    <p class="text-sm text-stone-400">Aucune dépense enregistrée</p>
                    <button @click="showAddExpense = true" class="btn-primary btn-sm mt-3">+ Ajouter</button>
                </div>

                <div v-else class="card divide-y divide-stone-50">
                    <div v-for="expense in filteredExpenses" :key="expense.id" class="flex items-center gap-3 p-4">
                        <div class="w-10 h-10 rounded-xl shrink-0 flex items-center justify-center text-lg"
                             :class="expense.type === 'actual' ? 'bg-red-100' : 'bg-yellow-100'">
                            {{ categoryIcon(expense.category) }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium truncate">{{ expense.label }}</p>
                            <div class="flex items-center gap-2">
                                <p class="text-xs text-stone-400">{{ expense.category }}</p>
                                <span class="badge text-[10px]" :class="expense.type === 'actual' ? 'bg-red-100 text-red-600' : 'bg-yellow-100 text-yellow-700'">
                                    {{ expense.type === 'actual' ? 'Réelle' : 'Prévue' }}
                                </span>
                            </div>
                        </div>
                        <p class="font-display font-700 shrink-0" :class="expense.type === 'actual' ? 'text-red-600' : 'text-yellow-600'">
                            -{{ formatCurrency(expense.amount) }}
                        </p>
                        <button @click="openEditExpense(expense)" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-stone-100 text-stone-400 shrink-0">✏️</button>
                        <button @click="deleteExpense(expense)" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-red-50 text-stone-300 hover:text-red-400 shrink-0">🗑️</button>
                    </div>
                </div>
            </template>

            <!-- ── Activities tab ─────────────────────── -->
            <template v-if="activeTab === 'activities'">
                <div class="flex items-center justify-between">
                    <p class="section-title mb-0">Tableau des activités</p>
                    <button @click="showAddActivity = true" class="btn-primary btn-sm">+ Activité</button>
                </div>

                <!-- Activities total -->
                <div v-if="budget.activities?.length" class="card p-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-blue-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-stone-500 mb-0.5">Total activités</p>
                            <p class="font-display font-700 text-xl">{{ formatCurrency(budget.activities_total) }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-xs text-stone-500 mb-0.5">Payé</p>
                            <p class="font-display font-700 text-green-600">{{ formatCurrency(paidTotal) }}</p>
                        </div>
                    </div>
                </div>

                <div v-if="!budget.activities?.length" class="card p-6 text-center">
                    <p class="text-2xl mb-2">🎯</p>
                    <p class="text-sm text-stone-400 mb-3">Listez vos activités avec leur coût pour estimer le budget</p>
                    <button @click="showAddActivity = true" class="btn-primary btn-sm">Ajouter une activité</button>
                </div>

                <div v-else class="space-y-2">
                    <div v-for="activity in budget.activities" :key="activity.id" class="card p-4">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg shrink-0"
                                 :class="activity.is_paid ? 'bg-green-100' : 'bg-blue-100'">
                                🎯
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <p class="font-medium text-sm">{{ activity.name }}</p>
                                    <span v-if="activity.is_paid" class="badge bg-green-100 text-green-700 text-[10px]">✓ Payé</span>
                                    <span v-else-if="activity.is_planned" class="badge bg-blue-100 text-blue-700 text-[10px]">Planifié</span>
                                </div>
                                <p class="text-xs text-stone-400 mt-0.5">
                                    {{ formatCurrency(activity.price_per_person) }}/pers. × {{ activity.persons }} pers.
                                    <span v-if="activity.date"> · {{ formatDate(activity.date) }}</span>
                                </p>
                            </div>
                            <div class="text-right shrink-0">
                                <p class="font-display font-700 text-sm">{{ formatCurrency(activity.total) }}</p>
                            </div>
                        </div>
                        <div class="flex gap-2 mt-3 pt-3 border-t border-stone-50">
                            <button @click="toggleActivityPaid(activity)"
                                    class="flex-1 py-2 rounded-xl text-xs font-medium transition-all"
                                    :class="activity.is_paid ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-500 hover:bg-green-50 hover:text-green-600'">
                                {{ activity.is_paid ? '✓ Payé' : 'Marquer payé' }}
                            </button>
                            <button @click="openEditActivity(activity)" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-500 text-xs hover:bg-stone-200 transition-colors">✏️</button>
                            <button @click="deleteActivity(activity)" class="px-4 py-2 rounded-xl bg-stone-100 text-stone-300 text-xs hover:bg-red-50 hover:text-red-400 transition-colors">🗑️</button>
                        </div>
                    </div>
                </div>
            </template>

            <!-- ── Members tab ────────────────────────── -->
            <template v-if="activeTab === 'members'">
                <p class="section-title">Budget par voyageur</p>
                <div v-if="!allMembers.length" class="card p-6 text-center">
                    <p class="text-sm text-stone-400">Aucun voyageur défini. Créez des groupes dans l'onglet Voyageurs.</p>
                    <Link :href="`/trips/${trip.id}/groups`" class="btn-primary btn-sm mt-3 inline-block">Gérer les groupes →</Link>
                </div>
                <div v-else class="space-y-3">
                    <div v-for="member in allMembers" :key="member.id" class="card p-4">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 rounded-2xl flex items-center justify-center text-xl shrink-0"
                                 :style="{ background: member.color + '22', border: `2px solid ${member.color}44` }">
                                {{ member.avatar_emoji }}
                            </div>
                            <div class="flex-1">
                                <p class="font-display font-700 text-sm">{{ member.name }}</p>
                                <p class="text-xs text-stone-400">{{ member.group_name }}</p>
                            </div>
                        </div>
                        <div v-if="member.member_budget" class="space-y-2">
                            <div class="flex justify-between text-sm">
                                <span class="text-stone-500">Alloué</span>
                                <span class="font-medium">{{ formatCurrency(member.member_budget.allocated_amount) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-stone-500">Dépensé</span>
                                <span class="text-red-500 font-medium">-{{ formatCurrency(member.member_budget.personal_spending) }}</span>
                            </div>
                            <ProgressBar
                                :percent="member.member_budget.allocated_amount > 0
                                    ? Math.min(100, Math.round((member.member_budget.personal_spending / member.member_budget.allocated_amount) * 100))
                                    : 0"
                                :color="(member.member_budget.allocated_amount - member.member_budget.personal_spending) >= 0 ? '#10B981' : '#EF4444'"
                                :label="'Restant : ' + formatCurrency(member.member_budget.allocated_amount - member.member_budget.personal_spending)" />
                        </div>
                        <p v-else class="text-xs text-stone-400 text-center py-2">Pas de budget défini</p>
                    </div>
                </div>
            </template>
        </div>

        <!-- Sheet: Add/Edit income -->
        <BottomSheet :show="showAddIncome || !!editingIncome" :title="editingIncome ? 'Modifier l\'entrée' : 'Nouvelle entrée'" @close="closeIncomeSheet">
            <form @submit.prevent="submitIncome" class="space-y-4">
                <div>
                    <label class="label">Description</label>
                    <input v-model="incomeForm.label" class="input" placeholder="ex: Semaine 1, Économies vacances…" required />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Montant ({{ budget.currency }})</label>
                        <input v-model="incomeForm.amount" type="number" step="0.01" min="0" class="input" placeholder="0.00" required />
                    </div>
                    <div>
                        <label class="label">Date</label>
                        <input v-model="incomeForm.date" type="date" class="input" />
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="closeIncomeSheet" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1" :disabled="incomeForm.processing">
                        {{ editingIncome ? 'Modifier' : 'Ajouter' }}
                    </button>
                </div>
            </form>
        </BottomSheet>

        <!-- Sheet: Add/Edit expense -->
        <BottomSheet :show="showAddExpense || !!editingExpense" :title="editingExpense ? 'Modifier la dépense' : 'Nouvelle dépense'" @close="closeExpenseSheet">
            <form @submit.prevent="submitExpense" class="space-y-4">
                <div>
                    <label class="label">Description</label>
                    <input v-model="expenseForm.label" class="input" placeholder="ex: Restaurant, Musée du Louvre…" required />
                </div>
                <div>
                    <label class="label">Catégorie</label>
                    <div class="grid grid-cols-3 gap-2 mb-2">
                        <button v-for="cat in expenseCategories" :key="cat.value" type="button"
                                @click="expenseForm.category = cat.value"
                                class="py-2.5 rounded-xl border text-xs font-medium transition-all flex flex-col items-center gap-1"
                                :class="expenseForm.category === cat.value ? 'border-orange-400 bg-orange-50 text-orange-700' : 'border-stone-200 bg-white text-stone-600'">
                            <span class="text-base">{{ cat.icon }}</span>
                            {{ cat.label }}
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Montant</label>
                        <input v-model="expenseForm.amount" type="number" step="0.01" min="0" class="input" placeholder="0.00" required />
                    </div>
                    <div>
                        <label class="label">Date</label>
                        <input v-model="expenseForm.date" type="date" class="input" />
                    </div>
                </div>
                <div>
                    <label class="label">Type</label>
                    <div class="flex gap-2">
                        <button type="button" @click="expenseForm.type = 'actual'"
                                class="flex-1 py-2.5 rounded-xl border text-sm font-medium transition-all"
                                :class="expenseForm.type === 'actual' ? 'border-red-400 bg-red-50 text-red-700' : 'border-stone-200 bg-white text-stone-500'">
                            💸 Réelle
                        </button>
                        <button type="button" @click="expenseForm.type = 'planned'"
                                class="flex-1 py-2.5 rounded-xl border text-sm font-medium transition-all"
                                :class="expenseForm.type === 'planned' ? 'border-yellow-400 bg-yellow-50 text-yellow-700' : 'border-stone-200 bg-white text-stone-500'">
                            📋 Prévue
                        </button>
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="closeExpenseSheet" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1" :disabled="expenseForm.processing">
                        {{ editingExpense ? 'Modifier' : 'Ajouter' }}
                    </button>
                </div>
            </form>
        </BottomSheet>

        <!-- Sheet: Add/Edit activity -->
        <BottomSheet :show="showAddActivity || !!editingActivity" :title="editingActivity ? 'Modifier l\'activité' : 'Nouvelle activité'" @close="closeActivitySheet">
            <form @submit.prevent="submitActivity" class="space-y-4">
                <div>
                    <label class="label">Nom de l'activité</label>
                    <input v-model="activityForm.name" class="input" placeholder="ex: Musée du Louvre, Safari…" required />
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Prix / personne</label>
                        <div class="relative">
                            <input v-model="activityForm.price_per_person" type="number" step="0.01" min="0" class="input pr-10" placeholder="0.00" required />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-stone-400">€</span>
                        </div>
                    </div>
                    <div>
                        <label class="label">Nb personnes</label>
                        <input v-model="activityForm.persons" type="number" min="1" class="input" placeholder="1" />
                    </div>
                </div>
                <!-- Subtotal preview -->
                <div v-if="activityForm.price_per_person && activityForm.persons"
                     class="bg-stone-50 rounded-xl px-4 py-3 flex justify-between text-sm">
                    <span class="text-stone-500">Total activité</span>
                    <span class="font-display font-700">{{ formatCurrency(activityForm.price_per_person * activityForm.persons) }}</span>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Date</label>
                        <input v-model="activityForm.date" type="date" class="input" />
                    </div>
                    <div>
                        <label class="label">Catégorie</label>
                        <input v-model="activityForm.category" class="input" placeholder="ex: Musée, Sport…" />
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="activityForm.is_planned = !activityForm.is_planned"
                            class="flex-1 py-2.5 rounded-xl border text-sm font-medium transition-all"
                            :class="activityForm.is_planned ? 'border-blue-400 bg-blue-50 text-blue-700' : 'border-stone-200 bg-white text-stone-500'">
                        {{ activityForm.is_planned ? '📋 Planifiée' : '📋 Planifier' }}
                    </button>
                    <button type="button" @click="activityForm.is_paid = !activityForm.is_paid"
                            class="flex-1 py-2.5 rounded-xl border text-sm font-medium transition-all"
                            :class="activityForm.is_paid ? 'border-green-400 bg-green-50 text-green-700' : 'border-stone-200 bg-white text-stone-500'">
                        {{ activityForm.is_paid ? '✓ Payée' : 'Marquer payée' }}
                    </button>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="closeActivitySheet" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1" :disabled="activityForm.processing">
                        {{ editingActivity ? 'Modifier' : 'Ajouter' }}
                    </button>
                </div>
            </form>
        </BottomSheet>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Components/Layout/AppLayout.vue';
import BottomSheet from '@/Components/UI/BottomSheet.vue';
import ProgressBar from '@/Components/UI/ProgressBar.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    trip:                Object,
    budget:              Object,
    expensesByCategory:  Object,
});

const currency = computed(() => props.budget?.currency ?? 'EUR');
function formatCurrency(n) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: currency.value }).format(n ?? 0);
}
function formatDate(d) { return new Date(d).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' }); }

// Tabs
const activeTab = ref('incomes');
const tabs = [
    { id: 'incomes',    icon: '💰', label: 'Entrées'   },
    { id: 'expenses',   icon: '💸', label: 'Dépenses'  },
    { id: 'activities', icon: '🎯', label: 'Activités' },
    { id: 'members',    icon: '👤', label: 'Voyageurs' },
];

// Computed stats
const spentPercent = computed(() => {
    if (!props.budget.total_income) return 0;
    return Math.min(100, Math.round((props.budget.total_spent / props.budget.total_income) * 100));
});
const plannedPercent = computed(() => {
    if (!props.budget.total_income) return 0;
    return Math.min(100 - spentPercent.value, Math.round((props.budget.total_planned / props.budget.total_income) * 100));
});

const paidTotal = computed(() =>
    props.budget.activities?.filter(a => a.is_paid).reduce((s, a) => s + a.total, 0) ?? 0
);

const allMembers = computed(() => {
    const members = [];
    for (const group of props.trip.groups ?? []) {
        for (const member of group.members ?? []) {
            members.push({ ...member, group_name: group.name });
        }
    }
    return members;
});

// Expense filter
const expenseFilter = ref(null);
const filteredExpenses = computed(() => {
    if (!expenseFilter.value) return props.budget.expenses ?? [];
    return (props.budget.expenses ?? []).filter(e => e.type === expenseFilter.value);
});

function categoryPercent(amount) {
    const max = Math.max(...Object.values(props.expensesByCategory ?? {}));
    return max > 0 ? Math.round((amount / max) * 100) : 0;
}

const CAT_ICONS = { logement: '🏨', transport: '🚗', nourriture: '🍽️', activités: '🎯', shopping: '🛍️', autre: '📌' };
function categoryIcon(cat) { return CAT_ICONS[cat?.toLowerCase()] ?? '📌'; }

// ── Income CRUD ───────────────────────────────────────
const showAddIncome = ref(false);
const editingIncome = ref(null);
const incomeForm = useForm({ label: '', amount: '', date: '' });

function openEditIncome(income) { editingIncome.value = income; incomeForm.label = income.label; incomeForm.amount = income.amount; incomeForm.date = income.date?.substring(0, 10) ?? ''; showAddIncome.value = true; }
function closeIncomeSheet() { showAddIncome.value = false; editingIncome.value = null; incomeForm.reset(); }
function submitIncome() {
    const base = `/trips/${props.trip.id}/budget/incomes`;
    if (editingIncome.value) incomeForm.patch(`${base}/${editingIncome.value.id}`, { onSuccess: closeIncomeSheet });
    else incomeForm.post(base, { onSuccess: closeIncomeSheet });
}
function deleteIncome(i) { if (confirm('Supprimer cette entrée ?')) router.delete(`/trips/${props.trip.id}/budget/incomes/${i.id}`); }

// ── Expense CRUD ──────────────────────────────────────
const showAddExpense = ref(false);
const editingExpense = ref(null);
const expenseForm = useForm({ label: '', category: 'autre', amount: '', date: '', type: 'actual' });

function openEditExpense(e) { editingExpense.value = e; expenseForm.label = e.label; expenseForm.category = e.category; expenseForm.amount = e.amount; expenseForm.date = e.date?.substring(0, 10) ?? ''; expenseForm.type = e.type; showAddExpense.value = true; }
function closeExpenseSheet() { showAddExpense.value = false; editingExpense.value = null; expenseForm.reset(); expenseForm.category = 'autre'; expenseForm.type = 'actual'; }
function submitExpense() {
    const base = `/trips/${props.trip.id}/budget/expenses`;
    if (editingExpense.value) expenseForm.patch(`${base}/${editingExpense.value.id}`, { onSuccess: closeExpenseSheet });
    else expenseForm.post(base, { onSuccess: closeExpenseSheet });
}
function deleteExpense(e) { if (confirm('Supprimer cette dépense ?')) router.delete(`/trips/${props.trip.id}/budget/expenses/${e.id}`); }

// ── Activity CRUD ─────────────────────────────────────
const showAddActivity = ref(false);
const editingActivity = ref(null);
const activityForm = useForm({ name: '', category: '', price_per_person: '', persons: 1, is_planned: true, is_paid: false, date: '' });

function openEditActivity(a) {
    editingActivity.value = a;
    activityForm.name = a.name; activityForm.category = a.category ?? ''; activityForm.price_per_person = a.price_per_person;
    activityForm.persons = a.persons; activityForm.is_planned = a.is_planned; activityForm.is_paid = a.is_paid;
    activityForm.date = a.date?.substring(0, 10) ?? '';
    showAddActivity.value = true;
}
function closeActivitySheet() { showAddActivity.value = false; editingActivity.value = null; activityForm.reset(); activityForm.persons = 1; activityForm.is_planned = true; }
function submitActivity() {
    const base = `/trips/${props.trip.id}/budget/activities`;
    if (editingActivity.value) activityForm.patch(`${base}/${editingActivity.value.id}`, { onSuccess: closeActivitySheet });
    else activityForm.post(base, { onSuccess: closeActivitySheet });
}
function deleteActivity(a) { if (confirm('Supprimer cette activité ?')) router.delete(`/trips/${props.trip.id}/budget/activities/${a.id}`); }
function toggleActivityPaid(a) { router.patch(`/trips/${props.trip.id}/budget/activities/${a.id}`, { is_paid: !a.is_paid }, { preserveScroll: true }); }

const expenseCategories = [
    { value: 'logement',  icon: '🏨', label: 'Logement'  },
    { value: 'transport', icon: '🚗', label: 'Transport'  },
    { value: 'nourriture',icon: '🍽️', label: 'Nourriture' },
    { value: 'activités', icon: '🎯', label: 'Activités'  },
    { value: 'shopping',  icon: '🛍️', label: 'Shopping'   },
    { value: 'autre',     icon: '📌', label: 'Autre'       },
];
</script>

<style scoped>
.scrollbar-none::-webkit-scrollbar { display: none; }
.scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
</style>
