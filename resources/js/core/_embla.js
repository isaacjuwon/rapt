import EmblaCarousel from 'embla-carousel'

export default function (Alpine) {
    Alpine.magic(
        'emblaApi',
        () =>
            (el, options, plugins = []) =>
                new EmblaCarousel(el, options, plugins),
    )
}
