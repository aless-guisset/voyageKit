<template>
    <div class="space-y-1">
        <div class="flex items-center justify-between text-xs text-stone-500" v-if="showLabel">
            <span>{{ label }}</span>
            <span class="font-medium">{{ percent }}%</span>
        </div>
        <div class="h-2 bg-stone-100 rounded-full overflow-hidden">
            <div class="h-full rounded-full transition-all duration-500" :style="{ width: percent + '%', background: color }" />
        </div>
    </div>
</template>
<script setup>
defineProps({
    percent: { type: Number, default: 0 },
    color: { type: String, default: '#F97316' },
    label: String,
    showLabel: { type: Boolean, default: true },
});
</script>
