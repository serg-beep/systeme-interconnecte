import { onMounted, onUnmounted } from 'vue'

export function useReveal(selector = '.reveal') {
  let observer = null

  onMounted(() => {
    observer = new IntersectionObserver(
      entries => entries.forEach(e => {
        if (e.isIntersecting) {
          e.target.classList.add('visible')
          observer.unobserve(e.target)
        }
      }),
      { threshold: 0.12 }
    )
    document.querySelectorAll(selector).forEach(el => observer.observe(el))
  })

  onUnmounted(() => observer?.disconnect())
}
