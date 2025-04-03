<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <img src="/images/image.png" alt="Welcome Image" />
    <!-- <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100">
        <circle cx="50" cy="50" r="45" stroke="green" stroke-width="4" fill="lightgreen" />

        <rect x="30" y="40" width="30" height="20" fill="darkgreen" />
        <circle cx="35" cy="65" r="8" fill="black" />
        <circle cx="55" cy="65" r="10" fill="black" />
        <rect x="40" y="30" width="10" height="10" fill="darkgreen" />
        
        <circle cx="70" cy="30" r="10" fill="gold" />
        <path d="M 65 30 L 75 30 M 70 25 L 70 35" stroke="black" stroke-width="2" />
    </svg> -->
</template>
