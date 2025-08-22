export const registerComponent = () => {
    const stateProps = {
        ':data-state'() {
            return this.__open
                ? this.delayDuration > 0
                    ? 'delayed-open'
                    : 'instant-open'
                : 'closed'
        },
    }

    const handleRoot = (
        el,
        Alpine,
        { delayDuration, skipDelayDuration, defaultOpen },
    ) => {
        Alpine.bind(el, () => ({
            '@keydown.escape.window'() {
                if (!this.__open) return

                this.__onOpenChange(false, true)
            },
            '@keydown.space.window'(event) {
                if (!this.__open) return

                event.preventDefault()

                this.__onOpenChange(false, true)
            },
            '@keydown.enter.window'() {
                if (!this.__open) return

                this.__onOpenChange(false, true)
            },
            'x-data'() {
                return {
                    __main: el,
                    __open: defaultOpen,
                    __timeouts: new Map(),
                    defaultOpen,
                    delayDuration,
                    skipDelayDuration,

                    get __trigger() {
                        return this.__main._x_ewa_trigger
                    },

                    get __arrow() {
                        return this.__main._x_ewa_arrow
                    },

                    __onOpenChange(newValue, skipDelay = false) {
                        if (newValue && !skipDelay && this.delayDuration > 0) {
                            this.__setTimeout(
                                'open',
                                () => {
                                    this.__open = true
                                },
                                this.delayDuration,
                            )
                        } else if (newValue) {
                            this.__clearTimeout('open')
                            this.__open = true
                        } else {
                            this.__clearTimeout('open')
                            this.__open = false
                        }
                    },

                    __setTimeout(key, callback, delay) {
                        this.__clearTimeout(key)

                        if (delay <= 0) {
                            callback()
                            return
                        }

                        const timeoutId = setTimeout(() => {
                            callback()
                            this.__timeouts.delete(key)
                        }, delay)

                        this.__timeouts.set(key, timeoutId)
                    },

                    __clearTimeout(key) {
                        if (this.__timeouts.has(key)) {
                            clearTimeout(this.__timeouts.get(key))
                            this.__timeouts.delete(key)
                        }
                    },

                    init() {
                        this.$el.removeAttribute('x-tooltip')
                        this.__main._x_ewa_arrow = this.$el.querySelector(
                            '[data-slot="tooltip-arrow"]',
                        )
                    },

                    destroy() {
                        this.__timeouts.forEach((timeoutId) =>
                            clearTimeout(timeoutId),
                        )
                        this.__timeouts.clear()
                    },
                }
            },
            'x-modelable': '__open',
        }))
    }

    const handleTrigger = (el, Alpine, {}) => {
        Alpine.bind(el, () => ({
            ...stateProps,
            '@mouseenter'() {
                this.__onOpenChange(true)
            },
            '@mouseleave'() {
                this.__onOpenChange(false)
            },
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-tooltip:trigger')
                this.__main._x_ewa_trigger = this.$el
            },
        }))
    }

    const handleContent = (el, Alpine, { side, align, sideOffset, arrow }) => {
        Alpine.bind(el, () => ({
            ...stateProps,
            ':data-side'() {
                return side
            },
            ':data-align'() {
                return align
            },
            'x-show'() {
                return this.__open
            },
            'x-transition:enter': 'ease-in duration-300 transition-opacity',
            'x-transition:enter-start': 'opacity-0',
            'x-transition:enter-end': 'opacity-100',
            'x-transition:leave': 'ease duration-150',
            'x-transition:leave-start': 'opacity-100',
            'x-transition:leave-end': 'opacity-0',
            'x-anchorplus'() {
                return {
                    reference: this.__trigger,
                    placement:
                        this.side +
                        (this.align === 'center' ? '' : `-${this.align}`),
                    sideOffset: this.sideOffset,
                    arrowEl: this.arrow ? this.__arrow : undefined,
                }
            },
            'x-data'() {
                return {
                    side,
                    align,
                    sideOffset,
                    arrow,

                    init() {
                        this.$el.removeAttribute('x-tooltip:content')
                    },
                }
            },
        }))
    }

    Alpine.directive(
        'tooltip',
        (el, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(el, Alpine, params)
            else if (value === 'trigger') handleTrigger(el, Alpine, params)
            else if (value === 'content') handleContent(el, Alpine, params)
            else {
                console.warn(`Unknown tooltip directive value: ${value}`)
            }
        },
    ).before('bind')
}
