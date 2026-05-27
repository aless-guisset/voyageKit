<template>
    <Link
        :href="href"
        class="flex items-center px-3 py-2.5 rounded-lg text-sm font-medium transition-all"
        :class="active
            ? 'bg-white/10 text-white'
            : 'text-white/60 hover:bg-white/5 hover:text-white'"
    >
        <slot />
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ href: String, active: Boolean });
</script>
