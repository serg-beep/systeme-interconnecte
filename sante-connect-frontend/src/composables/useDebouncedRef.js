import { ref, watch } from 'vue'

export function useDebouncedRef(source, delay = 180) {
    const debounced = ref(source.value)
    let timer = null

    watch(source, value => {
        clearTimeout(timer)
        timer = setTimeout(() => {
            debounced.value = value
        }, delay)
    })

    return debounced
}
