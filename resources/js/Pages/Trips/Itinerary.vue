<template>
    <AppLayout :title="trip.name" subtitle="Itinéraire" :back="`/trips/${trip.id}`">
        <template #header-actions>
            <button v-if="trip.start_date && !trip.itinerary_days?.length"
                    @click="generateDays" class="btn-secondary btn-sm">🗓️ Générer</button>
            <button @click="showAddDay = true" class="btn-primary btn-sm">+ Jour</button>
        </template>

        <div class="page-body">
            <!-- Carte Leaflet -->
            <div v-if="eventsWithCoords.length" class="card overflow-hidden">
                <div id="map" class="w-full h-64 z-0" />
                <div class="px-4 py-2 flex items-center gap-4 text-xs text-stone-500 border-t border-stone-100">
                    <span>{{ eventsWithCoords.length }} lieu(x) sur la carte</span>
                    <a v-if="googleMapsRouteUrl" :href="googleMapsRouteUrl" target="_blank"
                       class="flex items-center gap-1 text-blue-600 font-medium">
                        🗺️ Google Maps
                    </a>
                    <a v-if="wazeRouteUrl" :href="wazeRouteUrl" target="_blank"
                       class="flex items-center gap-1 text-sky-600 font-medium">
                        🔵 Waze
                    </a>
                </div>
            </div>

            <!-- Stats résumé -->
            <div v-if="totalTravelMin || totalTollCost" class="grid grid-cols-3 gap-3">
                <div v-if="totalTravelMin" class="card-section text-center">
                    <p class="font-display font-700 text-lg">{{ formatDuration(totalTravelMin) }}</p>
                    <p class="text-xs text-stone-400">Trajet total</p>
                </div>
                <div v-if="totalRestMin" class="card-section text-center">
                    <p class="font-display font-700 text-lg">{{ formatDuration(totalRestMin) }}</p>
                    <p class="text-xs text-stone-400">Pauses</p>
                </div>
                <div v-if="totalTollCost" class="card-section text-center">
                    <p class="font-display font-700 text-lg text-orange-600">{{ formatCurrency(totalTollCost) }}</p>
                    <p class="text-xs text-stone-400">Péages</p>
                </div>
            </div>

            <!-- No dates -->
            <div v-if="!trip.start_date" class="card p-5 border-l-4 border-yellow-400 bg-yellow-50">
                <p class="text-sm text-yellow-800 font-medium">⚠️ Définissez des dates pour générer les jours.</p>
                <Link :href="`/trips/${trip.id}/edit`" class="btn-secondary btn-sm mt-3 inline-block">Modifier →</Link>
            </div>

            <!-- Empty -->
            <div v-else-if="!trip.itinerary_days?.length" class="card p-6 text-center">
                <div class="text-4xl mb-3">🗓️</div>
                <p class="font-display font-700 mb-1">Itinéraire vide</p>
                <div class="flex gap-2 justify-center mt-3">
                    <button @click="generateDays" class="btn-secondary btn-sm">🗓️ Générer {{ trip.duration_days }} jours</button>
                    <button @click="showAddDay = true" class="btn-primary btn-sm">+ Manuel</button>
                </div>
            </div>

            <!-- Days -->
            <div v-for="day in trip.itinerary_days" :key="day.id" class="space-y-2">
                <!-- Day header -->
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl bg-[#1A1714] text-white flex flex-col items-center justify-center shrink-0">
                        <span class="text-xs font-bold leading-none">J{{ dayNumber(day) }}</span>
                        <span class="text-[10px] text-white/50 leading-none">{{ dayOfWeek(day.date) }}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="font-display font-700 text-sm">{{ day.title || formatDateLong(day.date) }}</p>
                        <p v-if="day.title" class="text-xs text-stone-400">{{ formatDateShort(day.date) }}</p>
                    </div>
                    <button @click="openEditDay(day)" class="btn-ghost btn-sm p-2 text-stone-400">✏️</button>
                    <button @click="openAddEvent(day)" class="btn-ghost btn-sm p-2 text-orange-500 font-bold">+</button>
                </div>

                <!-- Events -->
                <div v-if="day.events?.length" class="ml-5 border-l-2 border-stone-200 space-y-0 rounded-r-2xl overflow-hidden">
                    <div v-for="(event, idx) in day.events" :key="event.id"
                         class="bg-white border-b border-stone-50 last:border-b-0">
                        <!-- Event main row -->
                        <div class="flex items-start gap-3 px-4 py-3">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center text-base shrink-0 mt-0.5"
                                 :class="eventBg(event.type)">
                                {{ event.icon }}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium leading-tight">{{ event.title }}</p>
                                        <div class="flex flex-wrap items-center gap-x-2 gap-y-0.5 mt-0.5">
                                            <span v-if="event.time_start" class="text-xs text-stone-400">
                                                🕐 {{ event.time_start }}{{ event.time_end ? ' → ' + event.time_end : '' }}
                                            </span>
                                            <span v-if="event.travel_minutes" class="text-xs text-blue-500 font-medium">
                                                🚗 {{ formatDuration(event.travel_minutes) }}
                                            </span>
                                            <span v-if="event.rest_minutes" class="text-xs text-teal-500 font-medium">
                                                ☕ {{ formatDuration(event.rest_minutes) }}
                                            </span>
                                            <span v-if="event.toll_cost" class="text-xs text-orange-500 font-medium">
                                                🛣️ {{ formatCurrency(event.toll_cost) }}
                                            </span>
                                            <span v-if="event.estimated_cost" class="text-xs text-stone-400">
                                                💶 {{ formatCurrency(event.estimated_cost) }}
                                            </span>
                                        </div>
                                        <p v-if="event.location" class="text-xs text-stone-400 mt-0.5 truncate">
                                            📍 {{ event.location }}
                                        </p>
                                    </div>
                                    <div class="flex gap-1 shrink-0">
                                        <button @click="openEditEvent(day, event)"
                                                class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-stone-100 text-stone-400">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                        </button>
                                        <button @click="deleteEvent(day, event)"
                                                class="w-7 h-7 flex items-center justify-center rounded-lg hover:bg-red-50 text-stone-300 hover:text-red-400">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </div>
                                </div>

                                <!-- Notes -->
                                <p v-if="event.notes" class="text-xs text-stone-400 mt-1 leading-snug">{{ event.notes }}</p>

                                <!-- Lieux à visiter -->
                                <div v-if="event.places_to_visit?.length" class="mt-2 space-y-1">
                                    <p class="text-xs font-semibold text-stone-400">📍 Lieux à visiter</p>
                                    <div v-for="(place, pi) in event.places_to_visit" :key="pi"
                                         class="flex items-center gap-2 bg-stone-50 rounded-lg px-2 py-1">
                                        <span class="text-xs flex-1">{{ place }}</span>
                                    </div>
                                </div>

                                <!-- Notes trajet -->
                                <p v-if="event.travel_notes" class="text-xs text-blue-600 mt-1 bg-blue-50 rounded-lg px-2 py-1">
                                    🚗 {{ event.travel_notes }}
                                </p>

                                <!-- Navigation buttons -->
                                <div v-if="event.waze_link || event.google_maps_link" class="flex gap-2 mt-2">
                                    <a v-if="event.google_maps_link" :href="event.google_maps_link" target="_blank"
                                       class="flex-1 flex items-center justify-center gap-1 bg-blue-50 text-blue-700 rounded-xl py-1.5 text-xs font-medium hover:bg-blue-100 transition-colors">
                                        🗺️ Google Maps
                                    </a>
                                    <a v-if="event.waze_link" :href="event.waze_link" target="_blank"
                                       class="flex-1 flex items-center justify-center gap-1 bg-sky-50 text-sky-700 rounded-xl py-1.5 text-xs font-medium hover:bg-sky-100 transition-colors">
                                        🔵 Waze
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- Trajet vers étape suivante -->
                        <div v-if="idx < day.events.length - 1 && event.type === 'transport'"
                             class="flex items-center gap-2 px-4 py-1.5 bg-blue-50/50 border-t border-blue-100/50">
                            <div class="w-px h-4 bg-blue-200 ml-3.5"/>
                        </div>
                    </div>
                </div>

                <!-- Empty day -->
                <div v-else class="ml-5">
                    <button @click="openAddEvent(day)"
                            class="w-full border border-dashed border-stone-200 rounded-xl py-3 text-xs text-stone-400 hover:border-orange-300 hover:text-orange-500 transition-all">
                        + Ajouter une étape
                    </button>
                </div>
            </div>

            <!-- Total coûts -->
            <div v-if="totalCost > 0" class="card p-4 flex items-center justify-between">
                <span class="text-sm text-stone-500">Coût total estimé</span>
                <span class="font-display font-700 text-orange-600">{{ formatCurrency(totalCost) }}</span>
            </div>
        </div>

        <!-- Sheet: Add/Edit day -->
        <BottomSheet :show="showAddDay || !!editingDay" :title="editingDay ? 'Modifier le jour' : 'Ajouter un jour'" @close="closeDaySheet">
            <form @submit.prevent="submitDay" class="space-y-4">
                <div>
                    <label class="label">Date</label>
                    <input v-model="dayForm.date" type="date" class="input" required />
                </div>
                <div>
                    <label class="label">Titre (optionnel)</label>
                    <input v-model="dayForm.title" class="input" placeholder="ex: Arrivée Paris, Journée plage…" />
                </div>
                <div>
                    <label class="label">Notes</label>
                    <textarea v-model="dayForm.notes" class="input" rows="2" />
                </div>
                <div class="flex gap-3">
                    <button type="button" @click="closeDaySheet" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1" :disabled="dayForm.processing">
                        {{ editingDay ? 'Modifier' : 'Ajouter' }}
                    </button>
                </div>
            </form>
        </BottomSheet>

        <!-- Sheet: Add/Edit event -->
        <BottomSheet :show="showAddEvent || !!editingEvent"
                     :title="editingEvent ? 'Modifier l\'étape' : 'Nouvelle étape'"
                     @close="closeEventSheet">
            <form @submit.prevent="submitEvent" class="space-y-4">
                <!-- Type -->
                <div>
                    <label class="label">Type</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button v-for="t in eventTypes" :key="t.value" type="button"
                                @click="eventForm.type = t.value; eventForm.icon = t.icon"
                                class="py-2.5 rounded-xl border text-xs font-medium transition-all flex flex-col items-center gap-1"
                                :class="eventForm.type === t.value ? 'border-orange-400 bg-orange-50 text-orange-700' : 'border-stone-200 bg-white text-stone-600'">
                            <span class="text-base">{{ t.icon }}</span>{{ t.label }}
                        </button>
                    </div>
                </div>

                <!-- Titre -->
                <div>
                    <label class="label">Titre *</label>
                    <input v-model="eventForm.title" class="input" placeholder="ex: Autoroute A6, Musée du Louvre…" required />
                </div>

                <!-- Lieu avec recherche OSM -->
                <div>
                    <label class="label">Lieu / Adresse</label>
                    <div class="relative">
                        <input v-model="locationQuery" @input="searchLocation" @focus="showSuggestions = true"
                               class="input" placeholder="Rechercher un lieu…" autocomplete="off" />
                        <div v-if="showSuggestions && locationSuggestions.length"
                             class="absolute top-full left-0 right-0 z-50 bg-white border border-stone-200 rounded-xl shadow-lg mt-1 overflow-hidden max-h-48 overflow-y-auto">
                            <button v-for="s in locationSuggestions" :key="s.place_id" type="button"
                                    @click="selectLocation(s)"
                                    class="w-full text-left px-4 py-3 text-sm hover:bg-orange-50 transition-colors border-b border-stone-50 last:border-b-0">
                                <p class="font-medium truncate">{{ s.display_name.split(',')[0] }}</p>
                                <p class="text-xs text-stone-400 truncate">{{ s.display_name }}</p>
                            </button>
                        </div>
                    </div>
                    <p v-if="eventForm.lat && eventForm.lng" class="text-xs text-green-600 mt-1">
                        ✓ Coordonnées : {{ Number(eventForm.lat).toFixed(4) }}, {{ Number(eventForm.lng).toFixed(4) }}
                    </p>
                </div>

                <!-- Horaires -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label">Heure début</label>
                        <input v-model="eventForm.time_start" type="time" class="input" />
                    </div>
                    <div>
                        <label class="label">Heure fin</label>
                        <input v-model="eventForm.time_end" type="time" class="input" />
                    </div>
                </div>

                <!-- Transport spécifique -->
                <template v-if="eventForm.type === 'transport' || eventForm.type === 'rest'">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="label">Durée trajet</label>
                            <div class="flex gap-2">
                                <input v-model="travelH" type="number" min="0" max="48" class="input text-center" placeholder="h" />
                                <input v-model="travelM" type="number" min="0" max="59" step="5" class="input text-center" placeholder="min" />
                            </div>
                        </div>
                        <div>
                            <label class="label">Pause</label>
                            <div class="flex gap-2">
                                <input v-model="restH" type="number" min="0" max="24" class="input text-center" placeholder="h" />
                                <input v-model="restM" type="number" min="0" max="59" step="5" class="input text-center" placeholder="min" />
                            </div>
                        </div>
                    </div>
                    <div>
                        <label class="label">Coût péages</label>
                        <div class="relative">
                            <input v-model="eventForm.toll_cost" type="number" step="0.5" min="0" class="input pr-10" placeholder="0.00" />
                            <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-stone-400">€</span>
                        </div>
                    </div>
                    <div>
                        <label class="label">Notes de trajet</label>
                        <textarea v-model="eventForm.travel_notes" class="input" rows="2"
                                  placeholder="ex: Prendre A6 sortie 31, attention travaux…" />
                    </div>
                </template>

                <!-- Lieux à visiter -->
                <div>
                    <label class="label">Lieux à visiter</label>
                    <div class="space-y-2">
                        <div v-for="(place, i) in placesToVisit" :key="i" class="flex gap-2">
                            <input v-model="placesToVisit[i]" class="input flex-1" :placeholder="'Lieu ' + (i+1)" />
                            <button type="button" @click="placesToVisit.splice(i,1)"
                                    class="w-10 h-10 flex items-center justify-center rounded-xl bg-red-50 text-red-400 hover:bg-red-100 shrink-0">✕</button>
                        </div>
                        <button type="button" @click="placesToVisit.push('')"
                                class="w-full border border-dashed border-stone-200 rounded-xl py-2 text-xs text-stone-400 hover:border-orange-300 hover:text-orange-500 transition-all">
                            + Ajouter un lieu
                        </button>
                    </div>
                </div>

                <!-- Coût estimé -->
                <div>
                    <label class="label">Coût estimé (entrée, activité…)</label>
                    <div class="relative">
                        <input v-model="eventForm.estimated_cost" type="number" step="0.01" min="0" class="input pr-10" placeholder="0.00" />
                        <span class="absolute right-3 top-1/2 -translate-y-1/2 text-sm text-stone-400">€</span>
                    </div>
                </div>

                <!-- Notes générales -->
                <div>
                    <label class="label">Notes générales</label>
                    <textarea v-model="eventForm.notes" class="input" rows="2"
                              placeholder="Infos pratiques, réservations, conseils…" />
                </div>

                <div class="flex gap-3 pt-1">
                    <button type="button" @click="closeEventSheet" class="btn-secondary flex-1">Annuler</button>
                    <button type="submit" class="btn-primary flex-1" :disabled="eventForm.processing">
                        {{ editingEvent ? 'Modifier' : 'Ajouter' }}
                    </button>
                </div>
            </form>
        </BottomSheet>
    </AppLayout>
</template>

<script setup>
import AppLayout from '@/Components/Layout/AppLayout.vue';
import BottomSheet from '@/Components/UI/BottomSheet.vue';
import { Link, useForm, router } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, nextTick } from 'vue';

const props = defineProps({ trip: Object });

// ── Carte Leaflet ─────────────────────────────────────────────────────────────
let map = null;
let L   = null;

const eventsWithCoords = computed(() => {
    const events = [];
    for (const day of props.trip.itinerary_days ?? []) {
        for (const event of day.events ?? []) {
            if (event.lat && event.lng) events.push(event);
        }
    }
    return events;
});

const googleMapsRouteUrl = computed(() => {
    if (eventsWithCoords.value.length < 2) return null;
    const waypoints = eventsWithCoords.value.map(e => `${e.lat},${e.lng}`);
    const origin = waypoints[0];
    const dest   = waypoints[waypoints.length - 1];
    const mid    = waypoints.slice(1, -1).join('|');
    return `https://maps.google.com/maps?saddr=${origin}&daddr=${dest}${mid ? '&waypoints=' + mid : ''}`;
});

const wazeRouteUrl = computed(() => {
    if (!eventsWithCoords.value.length) return null;
    const last = eventsWithCoords.value[eventsWithCoords.value.length - 1];
    return `https://waze.com/ul?ll=${last.lat},${last.lng}&navigate=yes`;
});

async function initMap() {
    await nextTick();
    const el = document.getElementById('map');
    if (!el || map) return;

    L = await import('leaflet');
    await import('leaflet/dist/leaflet.css');

    // Fix icônes Leaflet
    delete L.Icon.Default.prototype._getIconUrl;
    L.Icon.Default.mergeOptions({
        iconRetinaUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon-2x.png',
        iconUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-icon.png',
        shadowUrl: 'https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/images/marker-shadow.png',
    });

    const coords = eventsWithCoords.value;
    const center = coords.length
        ? [parseFloat(coords[0].lat), parseFloat(coords[0].lng)]
        : [46.603354, 1.888334]; // France

    map = L.map('map').setView(center, coords.length ? 10 : 5);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const points = [];
    coords.forEach((event, i) => {
        const lat = parseFloat(event.lat);
        const lng = parseFloat(event.lng);
        points.push([lat, lng]);

        const marker = L.marker([lat, lng]).addTo(map);
        marker.bindPopup(`
            <strong>${event.icon} ${event.title}</strong><br>
            <small>${event.location || ''}</small>
            ${event.travel_minutes ? '<br>🚗 ' + formatDuration(event.travel_minutes) : ''}
            ${event.toll_cost ? '<br>🛣️ ' + formatCurrency(event.toll_cost) : ''}
        `);
    });

    if (points.length > 1) {
        // Tracé de la route via OSRM
        try {
            const coords_str = points.map(p => p[1] + ',' + p[0]).join(';');
            const resp = await fetch(`https://router.project-osrm.org/route/v1/driving/${coords_str}?overview=full&geometries=geojson`);
            const data = await resp.json();
            if (data.routes?.[0]) {
                L.geoJSON(data.routes[0].geometry, {
                    style: { color: '#F97316', weight: 4, opacity: 0.8 }
                }).addTo(map);
            }
        } catch (e) {
            // Fallback : ligne droite
            L.polyline(points, { color: '#F97316', weight: 3, dashArray: '6,6' }).addTo(map);
        }
        map.fitBounds(points, { padding: [20, 20] });
    }
}

watch(eventsWithCoords, (val) => { if (val.length) initMap(); }, { immediate: true });

// ── Stats globales ─────────────────────────────────────────────────────────────
const allEvents = computed(() =>
    (props.trip.itinerary_days ?? []).flatMap(d => d.events ?? [])
);
const totalTravelMin = computed(() => allEvents.value.reduce((s, e) => s + (e.travel_minutes ?? 0), 0));
const totalRestMin   = computed(() => allEvents.value.reduce((s, e) => s + (e.rest_minutes ?? 0), 0));
const totalTollCost  = computed(() => allEvents.value.reduce((s, e) => s + parseFloat(e.toll_cost ?? 0), 0));
const totalCost      = computed(() => allEvents.value.reduce((s, e) => s + parseFloat(e.estimated_cost ?? 0) + parseFloat(e.toll_cost ?? 0), 0));

// ── Recherche lieu OSM ────────────────────────────────────────────────────────
const locationQuery       = ref('');
const locationSuggestions = ref([]);
const showSuggestions     = ref(false);
let searchTimeout         = null;

function searchLocation() {
    clearTimeout(searchTimeout);
    if (locationQuery.value.length < 3) { locationSuggestions.value = []; return; }
    searchTimeout = setTimeout(async () => {
        try {
            const q = encodeURIComponent(locationQuery.value);
            const r = await fetch(`https://nominatim.openstreetmap.org/search?q=${q}&format=json&limit=5&accept-language=fr`);
            locationSuggestions.value = await r.json();
        } catch (e) { locationSuggestions.value = []; }
    }, 400);
}

function selectLocation(suggestion) {
    eventForm.location = suggestion.display_name.split(',').slice(0, 2).join(',').trim();
    eventForm.lat      = suggestion.lat;
    eventForm.lng      = suggestion.lon;
    locationQuery.value = eventForm.location;
    showSuggestions.value = false;
    locationSuggestions.value = [];
}

// ── Helpers ───────────────────────────────────────────────────────────────────
function formatCurrency(n) { return new Intl.NumberFormat('fr-FR',{style:'currency',currency:'EUR'}).format(n??0); }
function formatDateLong(d)  { return new Date(d).toLocaleDateString('fr-FR',{weekday:'long',day:'numeric',month:'long'}); }
function formatDateShort(d) { return new Date(d).toLocaleDateString('fr-FR',{day:'numeric',month:'short'}); }
function dayOfWeek(d)       { return new Date(d).toLocaleDateString('fr-FR',{weekday:'short'}); }
function formatDuration(min) {
    if (!min) return '0min';
    const h = Math.floor(min / 60);
    const m = min % 60;
    return h > 0 ? `${h}h${m > 0 ? m + 'min' : ''}` : `${m}min`;
}
function dayNumber(day) {
    if (!props.trip.start_date) return '?';
    return Math.round((new Date(day.date) - new Date(props.trip.start_date)) / 86400000) + 1;
}
const EVENT_BG = { activity:'bg-orange-100', transport:'bg-blue-100', accommodation:'bg-purple-100', food:'bg-green-100', rest:'bg-teal-100', other:'bg-stone-100' };
function eventBg(type) { return EVENT_BG[type] ?? 'bg-stone-100'; }

// ── Day CRUD ──────────────────────────────────────────────────────────────────
const showAddDay  = ref(false);
const editingDay  = ref(null);
const dayForm = useForm({ date: '', title: '', notes: '' });

function openEditDay(day) { editingDay.value = day; dayForm.date = day.date?.substring(0,10)??''; dayForm.title = day.title??''; dayForm.notes = day.notes??''; }
function closeDaySheet() { showAddDay.value = false; editingDay.value = null; dayForm.reset(); }
function submitDay() {
    if (editingDay.value) dayForm.patch(`/trips/${props.trip.id}/itinerary/days/${editingDay.value.id}`, { onSuccess: closeDaySheet });
    else dayForm.post(`/trips/${props.trip.id}/itinerary/days`, { onSuccess: closeDaySheet });
}
function generateDays() { router.post(`/trips/${props.trip.id}/itinerary/generate-days`); }

// ── Event CRUD ────────────────────────────────────────────────────────────────
const showAddEvent  = ref(false);
const editingEvent  = ref(null);
const activeDay     = ref(null);
const placesToVisit = ref([]);
const travelH = ref(0); const travelM = ref(0);
const restH   = ref(0); const restM   = ref(0);

const eventForm = useForm({
    title:'', type:'activity', icon:'🎯', time_start:'', time_end:'',
    location:'', lat:'', lng:'', place_id:'',
    notes:'', estimated_cost:'', toll_cost:'',
    travel_minutes:0, rest_minutes:0,
    places_to_visit:[], travel_notes:'',
});

watch([travelH, travelM], () => { eventForm.travel_minutes = (parseInt(travelH.value)||0)*60 + (parseInt(travelM.value)||0); });
watch([restH, restM],     () => { eventForm.rest_minutes   = (parseInt(restH.value)||0)*60   + (parseInt(restM.value)||0); });

function openAddEvent(day) {
    activeDay.value = day; editingEvent.value = null;
    placesToVisit.value = []; travelH.value = 0; travelM.value = 0; restH.value = 0; restM.value = 0;
    locationQuery.value = ''; eventForm.reset(); eventForm.type = 'activity'; eventForm.icon = '🎯';
    showAddEvent.value = true;
}
function openEditEvent(day, event) {
    activeDay.value = day; editingEvent.value = event;
    eventForm.title = event.title; eventForm.type = event.type; eventForm.icon = event.icon;
    eventForm.time_start = event.time_start??''; eventForm.time_end = event.time_end??'';
    eventForm.location = event.location??''; eventForm.lat = event.lat??''; eventForm.lng = event.lng??'';
    eventForm.notes = event.notes??''; eventForm.estimated_cost = event.estimated_cost??'';
    eventForm.toll_cost = event.toll_cost??''; eventForm.travel_notes = event.travel_notes??'';
    travelH.value = Math.floor((event.travel_minutes??0)/60); travelM.value = (event.travel_minutes??0)%60;
    restH.value   = Math.floor((event.rest_minutes??0)/60);   restM.value   = (event.rest_minutes??0)%60;
    placesToVisit.value = [...(event.places_to_visit??[])];
    locationQuery.value = event.location??'';
    showAddEvent.value = true;
}
function closeEventSheet() { showAddEvent.value = false; editingEvent.value = null; eventForm.reset(); locationSuggestions.value = []; }
function submitEvent() {
    eventForm.places_to_visit = placesToVisit.value.filter(p => p.trim());
    const base = `/trips/${props.trip.id}/itinerary/days/${activeDay.value.id}/events`;
    if (editingEvent.value) eventForm.patch(`${base}/${editingEvent.value.id}`, { onSuccess: closeEventSheet });
    else eventForm.post(base, { onSuccess: closeEventSheet });
}
function deleteEvent(day, event) {
    if (confirm('Supprimer cette étape ?'))
        router.delete(`/trips/${props.trip.id}/itinerary/days/${day.id}/events/${event.id}`, { preserveScroll: true });
}

const eventTypes = [
    { value:'activity',      icon:'🎯', label:'Activité'    },
    { value:'transport',     icon:'🚗', label:'Transport'    },
    { value:'accommodation', icon:'🏨', label:'Hébergement'  },
    { value:'food',          icon:'🍽️', label:'Repas'       },
    { value:'rest',          icon:'☕', label:'Pause'        },
    { value:'other',         icon:'📌', label:'Autre'        },
];
</script>
