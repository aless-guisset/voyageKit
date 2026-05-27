<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="show" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm" @click="$emit('close')" />
        </Transition>
        <Transition name="slide-up">
            <div v-if="show" class="sheet" @click.stop>
                <div class="sheet-handle" />
                <div class="px-5 pt-2 pb-2 flex items-center justify-between">
                    <h3 class="font-display font-700 text-lg">{{ title }}</h3>
                    <button @click="$emit('close')" class="btn-ghost btn-sm p-2 rounded-xl">✕</button>
                </div>
                <div class="px-5 pb-6">
                    <slot />
                </div>
            </div>
        </Transition>
    </Teleport>
</template>
<script setup>
defineProps({ show: Boolean, title: String });
defineEmits(['close']);
</script>
