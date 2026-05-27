<template>
    <AppLayout :title="`Modifier : ${trip.name}`" :back="`/trips/${trip.id}`">
        <div class="page-body">
            <form @submit.prevent="submit" class="space-y-5">
                <div class="card p-5 flex flex-col items-center gap-4">
                    <div class="text-6xl">{{ form.cover_emoji }}</div>
                    <div>
                        <label class="label text-center block mb-2">Emoji</label>
                        <div class="grid grid-cols-7 gap-2">
                            <button v-for="e in tripEmojis" :key="e" type="button" @click="form.cover_emoji = e"
                                    class="h-10 rounded-xl text-xl flex items-center justify-center transition-all"
                                    :class="form.cover_emoji === e ? 'bg-orange-100 ring-2 ring-orange-400 scale-110' : 'bg-stone-100'">
                                {{ e }}
                            </button>
                        </div>
                    </div>
                </div>

                <div class="card-section space-y-4">
                    <div>
                        <label class="label">Nom *</label>
                        <input v-model="form.name" class="input" required />
                    </div>
                    <div>
                        <label class="label">Destination</label>
                        <input v-model="form.destination" class="input" />
                    </div>
                </div>

                <div class="card-section space-y-4">
                    <p class="section-title">📅 Dates</p>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Départ</label>
                            <input v-model="form.start_date" type="date" class="input" />
                        </div>
                        <div>
                            <label class="label">Retour</label>
                            <input v-model="form.end_date" type="date" class="input" />
                        </div>
                    </div>
                </div>

                <div class="card-section space-y-3">
                    <p class="section-title">⚙️ Statut</p>
                    <div class="grid grid-cols-2 gap-2">
                        <button v-for="s in statuses" :key="s.value" type="button" @click="form.status = s.value"
                                class="py-3 rounded-xl border text-sm font-medium transition-all flex items-center justify-center gap-2"
                                :class="form.status === s.value ? 'border-orange-400 bg-orange-50 text-orange-700' : 'border-stone-200 bg-white text-stone-600'">
                            {{ s.icon }} {{ s.label }}
                        </button>
                    </div>
                </div>

                <div class="card-section">
                    <label class="label">Notes</label>
                    <textarea v-model="form.notes" class="input" rows="3" />
                </div>

                <div class="flex gap-3 pb-4">
                    <Link :href="`/trips/${trip.id}`" class="btn-secondary flex-1 text-center">Annuler</Link>
                    <button type="submit" class="btn-primary flex-1" :disabled="form.processing">Enregistrer</button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { Link, useForm } from '@inertiajs/vue3';

const props = defineProps({ trip: Object });

const form = useForm({
    name:         props.trip.name,
    destination:  props.trip.destination ?? '',
    start_date:   props.trip.start_date?.substring(0, 10) ?? '',
    end_date:     props.trip.end_date?.substring(0, 10) ?? '',
    cover_emoji:  props.trip.cover_emoji,
    notes:        props.trip.notes ?? '',
    status:       props.trip.status,
});

function submit() { form.patch(`/trips/${props.trip.id}`); }

const tripEmojis = ['✈️','🏖️','🏔️','🌍','🏝️','🗺️','🚂','🛳️','🚗','🏕️','🎡','🌅','🗼','🏯','🌋','🦁','🐬','🎿','🌺','🏄'];
const statuses = [
    { value: 'planning',  icon: '💭', label: 'Planification' },
    { value: 'confirmed', icon: '✅', label: 'Confirmé'      },
    { value: 'ongoing',   icon: '🔥', label: 'En cours'      },
    { value: 'completed', icon: '🏁', label: 'Terminé'       },
];
</script>
