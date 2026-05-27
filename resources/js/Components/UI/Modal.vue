<template>
    <Teleport to="body">
        <Transition name="fade">
            <div v-if="show" class="fixed inset-0 z-50 bg-black/40 backdrop-blur-sm flex items-end sm:items-center justify-center p-0 sm:p-4" @click="$emit('close')">
                <Transition name="slide-up">
                    <div v-if="show" class="bg-white w-full sm:max-w-md rounded-t-3xl sm:rounded-2xl shadow-2xl max-h-[92vh] overflow-y-auto pb-safe sm:pb-0" @click.stop>
                        <div class="w-10 h-1 bg-stone-200 rounded-full mx-auto mt-3 mb-1 sm:hidden" />
                        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
                            <h3 class="font-display font-700 text-lg">{{ title }}</h3>
                            <button @click="$emit('close')" class="w-8 h-8 flex items-center justify-center rounded-xl hover:bg-stone-100 text-stone-400 transition-colors">✕</button>
                        </div>
                        <div class="px-5 py-5">
                            <slot />
                        </div>
                    </div>
                </Transition>
            </div>
        </Transition>
    </Teleport>
</template>
<script setup>
defineProps({ show: Boolean, title: String });
defineEmits(['close']);
</script>
