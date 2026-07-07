<template>
  <article class="stat-card" :class="[`stat-card--${tone}`]">
    <div class="stat-card__icon">
      <slot name="icon" />
    </div>
    <div class="stat-card__content">
      <div class="stat-card__value">{{ value }}</div>
      <div class="stat-card__label">{{ label }}</div>
      <div v-if="description" class="stat-card__description">{{ description }}</div>
    </div>
  </article>
</template>

<script setup>
defineProps({
    value: { type: [String, Number], required: true },
    label: { type: String, required: true },
    description: { type: String, default: '' },
    tone: { type: String, default: 'primary' },
})
</script>

<style scoped>
.stat-card {
  position: relative;
  display: flex;
  align-items: flex-start;
  gap: 14px;
  min-height: 104px;
  overflow: hidden;
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  padding: 18px;
}

.stat-card::before {
  content: '';
  position: absolute;
  inset: 0 auto 0 0;
  width: 4px;
  background: var(--color-primary);
}

.stat-card__icon {
  width: 44px;
  height: 44px;
  border-radius: 12px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  background: var(--color-primary-soft);
  color: var(--color-primary-strong);
}

.stat-card__content {
  min-width: 0;
}

.stat-card__value {
  color: var(--color-text-primary);
  font-size: 32px;
  font-weight: 800;
  line-height: 1;
}

.stat-card__label {
  color: var(--color-text-secondary);
  font-size: 13px;
  font-weight: 700;
  line-height: 1.25;
  margin-top: 5px;
}

.stat-card__description {
  color: var(--color-text-muted);
  font-size: 11px;
  line-height: 1.3;
  margin-top: 3px;
}

.stat-card--danger::before { background: var(--color-danger); }
.stat-card--danger .stat-card__icon {
  background: var(--color-danger-soft);
  color: #b91c1c;
}

.stat-card--warning::before { background: var(--color-warning); }
.stat-card--warning .stat-card__icon {
  background: var(--color-warning-soft);
  color: #92400e;
}

.stat-card--accent::before { background: var(--color-accent); }
.stat-card--accent .stat-card__icon {
  background: var(--color-accent-soft);
  color: #1d4ed8;
}

.stat-card--primary::before { background: var(--color-primary); }

@media (max-width: 680px) {
  .stat-card {
    min-height: 96px;
    padding: 16px;
  }

  .stat-card__value {
    font-size: 28px;
  }
}
</style>
