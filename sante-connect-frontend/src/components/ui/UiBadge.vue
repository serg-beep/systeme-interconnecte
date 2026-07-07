<template>
  <span class="ui-badge" :class="[`ui-badge--${tone}`]">
    <slot>{{ label }}</slot>
  </span>
</template>

<script setup>
defineProps({
    label: { type: [String, Number], default: '' },
    tone: { type: String, default: 'neutral' },
})
</script>

<style scoped>
.ui-badge {
  display: inline-flex;
  align-items: center;
  max-width: 100%;
  min-height: 24px;
  padding: 3px 8px;
  border-radius: 8px;
  font-size: 10px;
  font-weight: 700;
  line-height: 1.2;
  text-transform: capitalize;
  white-space: nowrap;
}

.ui-badge--neutral {
  background: var(--color-surface-muted);
  color: var(--color-text-secondary);
}

.ui-badge--primary {
  background: var(--color-primary-soft);
  color: var(--color-primary-strong);
}

.ui-badge--accent {
  background: var(--color-accent-soft);
  color: #1d4ed8;
}

.ui-badge--warning {
  background: var(--color-warning-soft);
  color: #92400e;
}

.ui-badge--danger {
  background: var(--color-danger-soft);
  color: #991b1b;
}
</style>
