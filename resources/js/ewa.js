//import focus from '@alpinejs/focus'
import collapse from '@alpinejs/collapse'
import anchor from './core/_anchor'
//import carousel from './core/_embla'
//import calendar from './core/_calendarpro'

document.addEventListener('alpine:init', () => {
    Alpine.plugin((Alpine) => {
        //Alpine.plugin(focus)
        Alpine.plugin(anchor)
        Alpine.plugin(collapse)
        //   Alpine.plugin(carousel)
        // Alpine.plugin(calendar)
    })
})
