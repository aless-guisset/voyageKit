<template>
    <AppLayout :title="trip.name" subtitle="Groupes & voyageurs" :back="`/trips/${trip.id}`">
        <template #header-actions>
            <button @click="showAddGroup = true" class="btn-primary btn-sm">+ Groupe</button>
        </template>

        <div class="page-body">
            <!-- Empty -->
            <div v-if="!trip.groups?.length" class="card p-8 text-center">
                <div class="text-4xl mb-3">👨‍👩‍👧‍👦</div>
                <p class="font-display font-700 mb-1">Aucun groupe</p>
                <p class="text-sm text-stone-400 mb-4">Créez un groupe pour organiser les voyageurs</p>
                <button @click="showAddGroup = true" class="btn-primary btn-sm">Créer un groupe</button>
            </div>

            <!-- Groups -->
            <div v-for="group in trip.groups" :key="group.id" class="space-y-3">
                <div class="flex items-center gap-3">
                    <span class="text-2xl">{{ group.icon }}</span>
                    <div class="flex-1">
                        <h2 class="font-display font-700 text-base">{{ group.name }}</h2>
                        <p class="text-xs text-stone-400">{{ group.members?.length ?? 0 }} membre(s)</p>
                    </div>
                    <button @click="editGroup(group)" class="btn-ghost btn-sm p-2">✏️</button>
                    <button @click="deleteGroup(group)" class="btn-ghost btn-sm p-2 text-red-400">🗑️</button>
                </div>

                <div class="space-y-2">
                    <div v-for="member in group.members" :key="member.id" class="card overflow-hidden">
                        <!-- Member header -->
                        <div class="flex items-center gap-3 p-4 border-b border-stone-50">
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-2xl shrink-0"
                                 :style="{ background: member.color + '22', border: `2px solid ${member.color}44` }">
                                {{ member.avatar_emoji }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="font-display font-700 text-sm">{{ member.name }}</p>
                                <p class="text-xs text-stone-400">
                                    {{ roleLabel(member.role) }}<span v-if="member.age"> · {{ member.age }} ans</span>
                                </p>
                            </div>
                            <button @click="openEditMember(group, member)" class="btn-ghost btn-sm p-2">✏️</button>
                            <button @click="deleteMember(group, member)" class="btn-ghost btn-sm p-2 text-red-400">🗑️</button>
                        </div>

                        <!-- Stats row -->
                        <div class="grid grid-cols-3 divide-x divide-stone-100 bg-stone-50/50">
                            <button @click="openAddListForMember(group, member)"
                                    class="flex flex-col items-center py-3 gap-1 hover:bg-stone-100 transition-colors active:scale-95">
                                <span class="text-base">🧳</span>
                                <span class="text-xs text-stone-500 font-medium">
                                    {{ member.packing_lists?.length ?? 0 }} liste(s)
                                </span>
                            </button>
                            <div class="flex flex-col items-center py-3 gap-1">
                                <span class="text-base">🎯</span>
                                <span class="text-xs text-stone-500 font-medium">
                                    {{ member.activities?.length ?? 0 }} activité(s)
                                </span>
                            </div>
                            <button @click="openMemberBudget(group, member)"
                                    class="flex flex-col items-center py-3 gap-1 hover:bg-stone-100 transition-colors active:scale-95">
                                <span class="text-base">💳</span>
                                <span class="text-xs font-medium"
                                      :class="member.member_budget ? 'text-green-600' : 'text-stone-400'">
                                    {{ member.member_budget
                                        ? formatCurrency(member.member_budget.allocated_amount)
                                        : '—' }}
                                </span>
                            </button>
                        </div>

                        <!-- Packing lists -->
                        <div v-if="member.packing_lists?.length" class="px-4 pb-3 pt-3 space-y-2">
                            <Link v-for="list in (member.packing_lists || []).filter(l => l.trip_group_id === group.id)" :key="list.id"
                                  :href="`/lists/${list.id}`"
                                  class="flex items-center gap-3 bg-stone-50 rounded-xl p-3 active:scale-98 transition-transform">
                                <span class="text-base shrink-0">{{ list.icon }}</span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium truncate">{{ list.name }}</p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <ProgressBar :percent="list.progress?.percent ?? 0" :show-label="false" class="flex-1" />
                                        <span class="text-xs text-stone-400 shrink-0">
                                            {{ list.progress?.checked }}/{{ list.progress?.total }}
                                        </span>
                                    </div>
                                </div>
                                <svg class="w-3.5 h-3.5 text-stone-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </Link>
                            <button @click="openAddListForMember(group, member)"
                                    class="w-full text-xs text-stone-400 hover:text-orange-600 py-2 transition-colors text-center">
                                + Ajouter une liste
                            </button>
                        </div>
                        <div v-else class="px-4 pb-4 pt-3">
                            <button @click="openAddListForMember(group, member)"
                                    class="w-full border border-dashed border-stone-200 rounded-xl py-3 text-xs text-stone-400 hover:border-orange-300 hover:text-orange-500 transition-all flex items-center justify-center gap-2">
                                <span>🧳</span> Créer une liste pour {{ member.name }}
                            </button>
                        </div>
                    </div>

                    <!-- Add member -->
                    <button @click="openAddMember(group)"
                            class="w-full border border-dashed border-stone-200 rounded-2xl py-3.5 text-sm text-stone-400 hover:border-orange-300 hover:text-orange-500 transition-all flex items-center justify-center gap-2">
                        <span>+</span> Ajouter un membre à {{ group.name }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Sheet: Add/Edit group -->
        <BottomSheet :show="showAddGroup || !!editingGroup"
                     :title="editingGroup ? 'Modifier le groupe' : 'Nouveau groupe'"
                     @close="closeGroupSheet">
            <form @submit.prevent="submitGroup" class="space-y-4">
                <div>
                    <label class="label">Nom du groupe</label>
                    <input v-model="groupForm.name" class="input" placeholder="ex: Famille Dupont, Les copains…" required />
                    <p v-if="groupForm.errors.name" class="text-xs text-red-500 mt-1">{{ groupForm.errors.name }}</p>
                </div>
                <div>
                    <label class="label">Icône</label>
                    <div class="grid grid-cols-7 gap-2">
                        <button v-for="e in groupEmojis" :key="e" type="button" @click="groupForm.icon = e"
                                class="h-10 rounded-xl text-xl flex items-center justify-center transition-all"
                                :class="groupForm.icon === e ? 'bg-orange-100 ring-2 ring-orange-400 scale-110' : 'bg-stone-100 hover:bg-stone-200'">
                            {{ e }}
                        </button>
                    </div>
                </div>
                <div>
                    <label class="label">Notes (optionnel)</label>
                    <textarea v-model="groupForm.notes" class="input" rows="2" placeholder="Infos sur le groupe…" />
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="closeGroupSheet" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1" :disabled="groupForm.processing">
                        {{ editingGroup ? 'Modifier' : 'Créer' }}
                    </button>
                </div>
            </form>
        </BottomSheet>

        <!-- Sheet: Add/Edit member -->
        <BottomSheet :show="showAddMember || !!editingMember"
                     :title="editingMember ? 'Modifier le membre' : 'Nouveau membre'"
                     @close="closeMemberSheet">
            <form @submit.prevent="submitMember" class="space-y-4">
                <div>
                    <label class="label">Prénom / Surnom</label>
                    <input v-model="memberForm.name" class="input" placeholder="ex: Papa, Maman, Léo, Emma…" required />
                </div>
                <div>
                    <label class="label">Avatar</label>
                    <div class="grid grid-cols-7 gap-2">
                        <button v-for="e in memberEmojis" :key="e" type="button" @click="memberForm.avatar_emoji = e"
                                class="h-10 rounded-xl text-xl flex items-center justify-center transition-all"
                                :class="memberForm.avatar_emoji === e ? 'bg-orange-100 ring-2 ring-orange-400 scale-110' : 'bg-stone-100'">
                            {{ e }}
                        </button>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Rôle</label>
                        <select v-model="memberForm.role" class="input">
                            <option value="adult">Adulte</option>
                            <option value="teen">Ado</option>
                            <option value="child">Enfant</option>
                            <option value="baby">Bébé</option>
                        </select>
                    </div>
                    <div>
                        <label class="label">Âge</label>
                        <input v-model="memberForm.age" type="number" class="input" placeholder="optionnel" min="0" max="120" />
                    </div>
                </div>
                <div>
                    <label class="label">Couleur personnelle</label>
                    <div class="flex gap-2 flex-wrap">
                        <button v-for="c in memberColors" :key="c" type="button" @click="memberForm.color = c"
                                class="w-9 h-9 rounded-full transition-transform hover:scale-110"
                                :style="{ background: c }"
                                :class="memberForm.color === c ? 'ring-2 ring-offset-2 ring-stone-400 scale-110' : ''" />
                    </div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="closeMemberSheet" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1" :disabled="memberForm.processing">
                        {{ editingMember ? 'Modifier' : 'Ajouter' }}
                    </button>
                </div>
            </form>
        </BottomSheet>

        <!-- Sheet: Add list to member -->
        <BottomSheet :show="showAddList"
                     :title="`Liste pour ${activeMember?.name ?? '…'}`"
                     @close="showAddList = false">
            <form @submit.prevent="submitMemberList" class="space-y-4">
                <div>
                    <label class="label">Nom de la liste</label>
                    <input v-model="listForm.name" class="input" placeholder="ex: Valise été, Nourriture, Médicaments…" required />
                </div>
                <div>
                    <label class="label">Type</label>
                    <div class="grid grid-cols-2 gap-2">
                        <button v-for="t in listTypes" :key="t.value" type="button"
                                @click="listForm.type = t.value; listForm.icon = t.icon"
                                class="py-3 rounded-xl border text-sm font-medium transition-all flex items-center justify-center gap-2"
                                :class="listForm.type === t.value
                                    ? 'border-orange-400 bg-orange-50 text-orange-700'
                                    : 'border-stone-200 bg-white text-stone-600 hover:bg-stone-50'">
                            {{ t.icon }} {{ t.label }}
                        </button>
                    </div>
                </div>
                <div v-if="templates.length">
                    <label class="label">Template (optionnel)</label>
                    <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        <button type="button" @click="listForm.list_template_id = null"
                                class="w-full text-left px-4 py-3 rounded-xl border text-sm transition-all flex items-center gap-3"
                                :class="!listForm.list_template_id ? 'border-orange-400 bg-orange-50' : 'border-stone-200 bg-white'">
                            <span>✨</span><span>Liste vide</span>
                        </button>
                        <button v-for="t in templates" :key="t.id" type="button"
                                @click="listForm.list_template_id = t.id; if (!listForm.name) listForm.name = t.name"
                                class="w-full text-left px-4 py-3 rounded-xl border text-sm transition-all flex items-center gap-3"
                                :class="listForm.list_template_id === t.id ? 'border-orange-400 bg-orange-50' : 'border-stone-200 bg-white'">
                            <span>{{ t.icon }}</span>
                            <span class="flex-1">{{ t.name }}</span>
                            <span class="badge bg-stone-100 text-stone-500">{{ t.items_count }}</span>
                        </button>
                    </div>
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="showAddList = false" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1" :disabled="listForm.processing">Créer</button>
                </div>
            </form>
        </BottomSheet>

        <!-- Sheet: Member budget -->
        <BottomSheet :show="showMemberBudget"
                     :title="`Budget de ${activeMember?.name ?? '…'}`"
                     @close="showMemberBudget = false">
            <form @submit.prevent="submitMemberBudget" class="space-y-4">
                <div>
                    <label class="label">Argent alloué ({{ trip.budget?.currency ?? 'EUR' }})</label>
                    <input v-model="budgetForm.allocated_amount" type="number" step="0.01" min="0" class="input text-lg font-display" placeholder="0.00" />
                </div>
                <div>
                    <label class="label">Dépenses personnelles sur place</label>
                    <input v-model="budgetForm.personal_spending" type="number" step="0.01" min="0" class="input" placeholder="0.00" />
                </div>
                <div v-if="budgetForm.allocated_amount" class="bg-stone-50 rounded-2xl p-4 space-y-2.5">
                    <div class="flex justify-between text-sm">
                        <span class="text-stone-500">Alloué</span>
                        <span class="font-medium">{{ formatCurrency(budgetForm.allocated_amount) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-stone-500">Dépensé</span>
                        <span class="font-medium text-red-500">-{{ formatCurrency(budgetForm.personal_spending || 0) }}</span>
                    </div>
                    <div class="border-t border-stone-200 pt-2.5 flex justify-between">
                        <span class="font-medium text-sm">Restant</span>
                        <span class="font-display font-700 text-base"
                              :class="memberRemaining >= 0 ? 'text-green-600' : 'text-red-600'">
                            {{ formatCurrency(memberRemaining) }}
                        </span>
                    </div>
                    <ProgressBar
                        :percent="budgetForm.allocated_amount > 0 ? Math.min(100, Math.round(((budgetForm.personal_spending || 0) / budgetForm.allocated_amount) * 100)) : 0"
                        :color="memberRemaining >= 0 ? '#10B981' : '#EF4444'"
                        label="Consommé" />
                </div>
                <div class="flex gap-3 pt-1">
                    <button type="button" @click="showMemberBudget = false" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1" :disabled="budgetForm.processing">Enregistrer</button>
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
    trip:      Object,
    templates: { type: Array, default: () => [] },
});

const currency = computed(() => props.trip.budget?.currency ?? 'EUR');
function formatCurrency(n) {
    return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: currency.value }).format(n ?? 0);
}
const ROLES = { adult: 'Adulte', teen: 'Ado', child: 'Enfant', baby: 'Bébé' };
function roleLabel(r) { return ROLES[r] ?? r; }

// Group
const showAddGroup = ref(false);
const editingGroup = ref(null);
const groupForm = useForm({ name: '', icon: '👨‍👩‍👧‍👦', notes: '' });

function editGroup(g) { editingGroup.value = g; groupForm.name = g.name; groupForm.icon = g.icon; groupForm.notes = g.notes ?? ''; }
function closeGroupSheet() { showAddGroup.value = false; editingGroup.value = null; groupForm.reset(); }
function submitGroup() {
    const url = editingGroup.value ? `/trips/${props.trip.id}/groups/${editingGroup.value.id}` : `/trips/${props.trip.id}/groups`;
    groupForm[editingGroup.value ? 'patch' : 'post'](url, { onSuccess: closeGroupSheet });
}
function deleteGroup(g) {
    if (confirm(`Supprimer "${g.name}" et tous ses membres ?`)) router.delete(`/trips/${props.trip.id}/groups/${g.id}`);
}

// Member
const showAddMember = ref(false);
const editingMember = ref(null);
const activeGroup   = ref(null);
const activeMember  = ref(null);
const memberForm = useForm({ name: '', avatar_emoji: '🧑', color: '#F97316', role: 'adult', age: '' });

function openAddMember(group) {
    activeGroup.value = group; editingMember.value = null;
    memberForm.reset(); memberForm.avatar_emoji = '🧑'; memberForm.color = '#F97316'; memberForm.role = 'adult';
    showAddMember.value = true;
}
function openEditMember(group, member) {
    activeGroup.value = group; editingMember.value = member;
    memberForm.name = member.name; memberForm.avatar_emoji = member.avatar_emoji;
    memberForm.color = member.color; memberForm.role = member.role; memberForm.age = member.age ?? '';
    showAddMember.value = true;
}
function closeMemberSheet() { showAddMember.value = false; editingMember.value = null; }
function submitMember() {
    const base = `/trips/${props.trip.id}/groups/${activeGroup.value.id}/members`;
    if (editingMember.value) {
        memberForm.patch(`${base}/${editingMember.value.id}`, { onSuccess: closeMemberSheet });
    } else {
        memberForm.post(base, { onSuccess: closeMemberSheet });
    }
}
function deleteMember(group, member) {
    if (confirm(`Supprimer "${member.name}" ?`))
        router.delete(`/trips/${props.trip.id}/groups/${group.id}/members/${member.id}`);
}

// Member list
const showAddList = ref(false);
const listForm = useForm({ name: '', type: 'packing', icon: '🧳', list_template_id: null });

function openAddListForMember(group, member) {
    activeGroup.value = group; activeMember.value = member;
    listForm.reset(); listForm.type = 'packing'; listForm.icon = '🧳';
    showAddList.value = true;
}
function submitMemberList() {
    listForm.post(
        `/trips/${props.trip.id}/groups/${activeGroup.value.id}/members/${activeMember.value.id}/lists`,
        { onSuccess: () => { showAddList.value = false; listForm.reset(); } }
    );
}

// Member budget
const showMemberBudget = ref(false);
const budgetForm = useForm({ allocated_amount: '', personal_spending: '' });

function openMemberBudget(group, member) {
    activeGroup.value = group; activeMember.value = member;
    const mb = member.member_budget;
    budgetForm.allocated_amount = mb?.allocated_amount ?? '';
    budgetForm.personal_spending = mb?.personal_spending ?? '';
    showMemberBudget.value = true;
}
function submitMemberBudget() {
    budgetForm.patch(`/trips/${props.trip.id}/members/${activeMember.value.id}/budget`, {
        onSuccess: () => { showMemberBudget.value = false; }
    });
}
const memberRemaining = computed(() =>
    (Number(budgetForm.allocated_amount) || 0) - (Number(budgetForm.personal_spending) || 0)
);

const groupEmojis  = ['👨‍👩‍👧‍👦','👫','👬','👭','🧑‍🤝‍🧑','👨‍👦','👩‍👧','🏠','🏕️','🎒','✈️','🌍','🏖️','🏔️'];
const memberEmojis = ['🧑','👨','👩','👦','👧','🧒','👴','👵','🧔','👱','😎','🤓','🧑‍🎨','🧑‍💼'];
const memberColors = ['#F97316','#EF4444','#8B5CF6','#3B82F6','#10B981','#F59E0B','#EC4899','#14B8A6','#6366F1','#84CC16'];
const listTypes = [
    { value: 'packing',  icon: '🧳', label: 'Valise'   },
    { value: 'grocery',  icon: '🛒', label: 'Courses'  },
    { value: 'shopping', icon: '🛍️', label: 'Shopping' },
    { value: 'todo',     icon: '✅', label: 'To-do'    },
];
</script>
