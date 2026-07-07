<template>
  <div class="skeleton-list" :class="{ grid }">
    <div v-for="item in count" :key="item" class="skeleton-card">
      <span class="skeleton-line wide"></span>
      <span class="skeleton-line"></span>
      <span class="skeleton-line short"></span>
    </div>
  </div>
</template>

<script setup>
defineProps({
    count: {
        type: Number,
        default: 4,
    },
    grid: {
        type: Boolean,
        default: false,
    },
})
</script>

<style scoped>
.skeleton-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}
.skeleton-list.grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
}
.skeleton-card {
    border: 1px solid var(--color-border);
    border-radius: 8px;
    background: var(--color-surface);
    padding: 16px;
}
.skeleton-line {
    display: block;
    height: 12px;
    width: 62%;
    border-radius: 999px;
    background: linear-gradient(90deg, #eef2f7 0%, #f8fafc 50%, #eef2f7 100%);
    background-size: 220% 100%;
    animation: skeletonPulse 1.2s ease-in-out infinite;
    margin-top: 10px;
}
.skeleton-line.wide {
    width: 86%;
    margin-top: 0;
}
.skeleton-line.short {
    width: 38%;
}
@keyframes skeletonPulse {
    from { background-position: 100% 0; }
    to { background-position: -100% 0; }
}
</style>
