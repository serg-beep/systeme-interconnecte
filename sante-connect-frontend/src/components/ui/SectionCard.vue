<template>
  <section class="section-card">
    <header class="section-card__header">
      <div class="section-card__title-wrap">
        <div class="section-card__icon" :class="[`section-card__icon--${tone}`]">
          <slot name="icon" />
        </div>
        <h2 class="section-card__title">{{ title }}</h2>
      </div>
      <slot name="action" />
    </header>

    <slot />
  </section>
</template>

<script setup>
defineProps({
    title: { type: String, required: true },
    tone: { type: String, default: 'primary' },
})
</script>

<style scoped>
.section-card {
  background: var(--color-surface);
  border: 1px solid var(--color-border);
  border-radius: 16px;
  box-shadow: var(--shadow-sm);
  padding: 18px;
}

.section-card__header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  margin-bottom: 14px;
}

.section-card__title-wrap {
  display: flex;
  align-items: center;
  gap: 8px;
  min-width: 0;
}

.section-card__icon {
  width: 28px;
  height: 28px;
  border-radius: 8px;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.section-card__icon--primary {
  background: var(--color-primary-soft);
  color: var(--color-primary-strong);
}

.section-card__icon--accent {
  background: var(--color-accent-soft);
  color: #1d4ed8;
}

.section-card__icon--warning {
  background: var(--color-warning-soft);
  color: #92400e;
}

.section-card__icon--danger {
  background: var(--color-danger-soft);
  color: #991b1b;
}

.section-card__title {
  color: var(--color-text-primary);
  font-size: 14px;
  font-weight: 700;
  line-height: 1.25;
  margin: 0;
}

@media (max-width: 680px) {
  .section-card {
    padding: 16px;
  }

  .section-card__header {
    align-items: flex-start;
  }
}
</style>
