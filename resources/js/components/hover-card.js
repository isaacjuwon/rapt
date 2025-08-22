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
        { defaultOpen, delayDuration, closeDelay },
    ) => {
        Alpine.bind(el, () => ({
            '@keydown.escape.window': '__onOpenChange(false, true)',
            'x-data'() {
                return {
                    __main: el,
                    __open: defaultOpen,
                    __timeouts: new Map(),
                    defaultOpen,
                    delayDuration,
                    closeDelay,

                    get __trigger() {
                        return this.__main._x_ewa_trigger
                    },

                    __onOpenChange(newValue, skipDelay = false) {
                        if (newValue && !skipDelay && this.delayDuration > 0) {
                            this.__clearTimeout('close')
                            this.__setTimeout(
                                'open',
                                () => {
                                    this.__open = true
                                },
                                this.delayDuration,
                            )
                        } else if (newValue) {
                            this.__clearTimeout('open')
                            this.__clearTimeout('close')
                            this.__open = true
                        } else if (!skipDelay && this.closeDelay > 0) {
                            this.__clearTimeout('open')
                            this.__setTimeout(
                                'close',
                                () => {
                                    this.__open = false
                                },
                                this.closeDelay,
                            )
                        } else {
                            this.__clearTimeout('open')
                            this.__clearTimeout('close')
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
                        this.$el.removeAttribute('x-hover-card')
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

    const handleTrigger = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ...stateProps,
            '@mouseenter': '__onOpenChange(true)',
            '@mouseleave': '__onOpenChange(false)',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-hover-card:trigger')
                this.__main._x_ewa_trigger = this.$el
            },
        }))
    }

    const handleContent = (el, Alpine, { side, align, sideOffset }) => {
        Alpine.bind(el, () => ({
            ...stateProps,
            ':data-side': 'side',
            ':data-align': 'align',
            'x-show': '__open',
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
                }
            },
            'x-data'() {
                return {
                    side,
                    align,
                    sideOffset,

                    init() {
                        this.$el.removeAttribute('x-hover-card:content')
                    },
                }
            },
        }))
    }

    Alpine.directive(
        'hover-card',
        (el, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(el, Alpine, params)
            else if (value === 'trigger') handleTrigger(el, Alpine, params)
            else if (value === 'content') handleContent(el, Alpine, params)
            else {
                console.warn(`Unknown hover-card directive value: ${value}`)
            }
        },
    ).before('bind')
}
