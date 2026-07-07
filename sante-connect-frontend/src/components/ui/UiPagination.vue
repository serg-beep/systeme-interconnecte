<template>
  <nav v-if="meta && meta.last_page > 1" class="ui-pagination" aria-label="Pagination">
    <button class="page-btn" :disabled="meta.current_page <= 1" @click="$emit('change', meta.current_page - 1)">
      Precedent
    </button>
    <span class="page-state">Page {{ meta.current_page }} / {{ meta.last_page }}</span>
    <button class="page-btn" :disabled="meta.current_page >= meta.last_page" @click="$emit('change', meta.current_page + 1)">
      Suivant
    </button>
  </nav>
</template>

<script setup>
defineProps({
    meta: {
        type: Object,
        default: null,
    },
})

defineEmits(['change'])
</script>

<style scoped>
.ui-pagination {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    gap: 10px;
    margin-top: 16px;
    flex-wrap: wrap;
}
.page-btn {
    min-width: 92px;
    border: 1px solid var(--color-border);
    background: var(--color-surface);
    color: var(--color-text-secondary);
    border-radius: 8px;
    padding: 7px 12px;
    font-size: 13px;
    cursor: pointer;
}
.page-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
.page-state {
    color: var(--color-text-muted);
    font-size: 13px;
}
</style>
