<template>
  <div class="empty-state">
    <span class="empty-state__icon">{{ icon }}</span>
    <span class="empty-state__text">{{ message }}</span>
    <slot />
  </div>
</template>

<script setup>
defineProps({
    icon: { type: String, default: 'OK' },
    message: { type: String, required: true },
})
</script>

<style scoped>
.empty-state {
  display: flex;
  align-items: center;
  gap: 8px;
  min-height: 44px;
  padding: 12px;
  background: var(--color-surface-muted);
  border: 1px solid transparent;
  border-radius: 10px;
  color: var(--color-text-secondary);
  font-size: 13px;
  line-height: 1.35;
}

.empty-state__icon {
  color: var(--color-primary);
  font-weight: 800;
  flex-shrink: 0;
}

.empty-state__text {
  min-width: 0;
}
</style>
