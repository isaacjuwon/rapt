export const registerComponent = () => {
    const handleRoot = (
        el,
        Alpine,
        { orientation, direction, loop, pluginNames, pluginsObjectName },
    ) => {
        const plugins = pluginNames
            .map((name) => window[pluginsObjectName]?.[name])
            .filter(Boolean)

        Alpine.bind(el, {
            'x-effect'() {
                this.__emblaApi?.reInit(this.__emblaOptions, this.plugins)
            },
            'x-data'() {
                return {
                    __main: el,
                    __emblaApi: null,
                    __canScrollNext: false,
                    __canScrollPrev: false,
                    orientation,
                    loop,
                    direction,
                    plugins,

                    get __content() {
                        return this.__main._x_ewa_content
                    },

                    get __emblaOptions() {
                        return {
                            loop: this.loop,
                            direction: this.direction,
                            axis: this.orientation === 'horizontal' ? 'x' : 'y',
                            container: this.__content.firstChild,
                        }
                    },

                    __updateCanScroll() {
                        this.__canScrollNext =
                            this.__emblaApi?.canScrollNext() ?? false
                        this.__canScrollPrev =
                            this.__emblaApi?.canScrollPrev() ?? false
                    },

                    init() {
                        this.$el.removeAttribute('x-carousel')
                        this.__main = this.$el

                        this.$nextTick(() => {
                            this.__emblaApi = this.$emblaApi(
                                this.$el,
                                this.__emblaOptions,
                                this.plugins,
                            )
                            this.__emblaApi.on('select', () =>
                                this.__updateCanScroll(),
                            )
                            this.__emblaApi.on('init', () =>
                                this.__updateCanScroll(),
                            )
                        })
                    },

                    destroy() {
                        this.__emblaApi?.destroy()
                    },
                }
            },
        })
    }

    const handleContent = (el, Alpine) => {
        Alpine.bind(el, {
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-carousel:content')
                this.__main._x_ewa_content = this.$el
            },
        })
    }

    const handleNext = (el, Alpine) => {
        Alpine.bind(el, {
            '@click': '__emblaApi?.scrollNext()',
            ':disabled': '! __canScrollNext',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-carousel:next')
            },
        })
    }

    const handlePrevious = (el, Alpine) => {
        Alpine.bind(el, {
            '@click': '__emblaApi?.scrollPrev()',
            ':disabled': '! __canScrollPrev',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-carousel:previous')
            },
        })
    }

    Alpine.directive(
        'carousel',
        (e, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(e, Alpine, params)
            else if (value === 'content') handleContent(e, Alpine)
            else if (value === 'next') handleNext(e, Alpine)
            else if (value === 'previous') handlePrevious(e, Alpine)
            else {
                console.warn(`Unknown carousel directive value: ${value}`)
            }
        },
    ).before('bind')
}
