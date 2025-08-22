export const registerComponent = () => {
    const commonItemProps = {
        ':data-state': 'selected ? "open" : "closed"',
        ':data-disabled': 'disabled || __disabled',
    }

    const commonProps = {
        ':data-orientation': 'orientation',
    }

    const handleRoot = (
        el,
        Alpine,
        { defaultValue, type, orientation, dir, collapsible, loop, disabled },
    ) => {
        Alpine.bind(el, {
            ...commonProps,
            ':data-dir': 'dir',
            ':data-type': 'type',
            ':data-collapsible': 'collapsible',
            ':data-disabled': '__disabled',
            '@keydown.down.prevent': '__focusNextItem',
            '@keydown.up.prevent': '__focusPreviousItem',
            'x-data'() {
                return {
                    __value: defaultValue,
                    __disabled: disabled,
                    defaultValue,
                    type,
                    orientation,
                    dir,
                    loop,
                    collapsible,

                    __onItemClick(value) {
                        if (this.disabled || this.__disabled) return

                        if (this.type === 'single')
                            this.__handleItemClickSinge(value)
                        else this.__handleItemClickMultiple(value)
                    },

                    __handleItemClickSinge(value) {
                        if (this.disabled || this.__disabled) return

                        this.__value = this.__value.includes(value)
                            ? this.collapsible
                                ? []
                                : [value]
                            : [value]
                    },

                    __handleItemClickMultiple(value) {
                        if (this.disabled || this.__disabled) return

                        if (this.__value.includes(value)) {
                            this.__value = this.__value.filter(
                                (item) => item !== value,
                            )
                        } else {
                            this.__value.push(value)
                        }
                    },

                    __focusPreviousItem() {
                        if (this.__disabled) return

                        const previous = this.$focus.getPrevious()
                        if (previous) {
                            this.$focus.previous()
                        } else if (this.loop) {
                            const last = this.$focus.getLast()
                            this.$focus.last()
                        }
                    },

                    __focusNextItem() {
                        if (this.__disabled) return

                        const next = this.$focus.getNext()
                        if (next) {
                            this.$focus.next()
                        } else if (this.loop) {
                            const first = this.$focus.getFirst()
                            this.$focus.first()
                        }
                    },

                    init() {
                        this.$el.removeAttribute('x-accordion')
                    },
                }
            },
            'x-modelable': '__value',
        })
    }

    const handleItem = (el, Alpine, { value, disabled }) => {
        Alpine.bind(el, {
            ...commonProps,
            ...commonItemProps,
            ':data-value': 'value',
            ':data-disabled': 'disabled || __disabled',
            ':disabled': 'disabled || __disabled',
            'x-data'() {
                return {
                    disabled,
                    value,

                    get selected() {
                        return this.__value.includes(this.value)
                    },

                    init() {
                        this.$el.removeAttribute('x-accordion:item')
                    },
                }
            },
        })
    }

    const handleTrigger = (el, Alpine) => {
        Alpine.bind(el, {
            ...commonProps,
            ...commonItemProps,
            '@click': '__onItemClick(value)',
            'x-data': '',
            'x-init': '$el.removeAttribute("x-accordion:trigger")',
        })
    }

    const handleContent = (el, Alpine) => {
        Alpine.bind(el, {
            ...commonProps,
            ...commonItemProps,
            'x-show': 'selected',
            'x-collapse': '',
            'x-data': '',
            'x-init': '$el.removeAttribute("x-accordion:content")',
        })
    }

    Alpine.directive(
        'accordion',
        (el, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(el, Alpine, params)
            else if (value === 'item') handleItem(el, Alpine, params)
            else if (value === 'trigger') handleTrigger(el, Alpine)
            else if (value === 'content') handleContent(el, Alpine)
            else {
                console.warn(`Unknown accordion directive value: ${value}`)
            }
        },
    ).before('bind')
}
