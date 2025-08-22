export const registerComponent = () => {
    const commonProps = {
        ':dir'() {
            return this.dir
        },
        ':data-orientation'() {
            return this.orientation
        },
        ':aria-orientation'() {
            return this.orientation
        },
        ':data-disabled'() {
            return this.disabled ? true : undefined
        },
    }

    const handleRoot = (
        el,
        Alpine,
        { defaultValue, orientation, dir, loop, disabled },
    ) => {
        Alpine.bind(el, () => ({
            ...commonProps,
            '@keydown.left'() {
                this.__selectPrevious()
            },
            '@keydown.up.prevent'() {
                this.__selectPrevious()
            },
            '@keydown.right'() {
                this.__selectNext()
            },
            '@keydown.down.prevent'() {
                this.__selectNext()
            },
            'x-data'() {
                return {
                    __value: defaultValue,
                    __disabled: disabled,
                    defaultValue,
                    orientation,
                    dir,
                    loop,

                    __selectPrevious() {
                        if (this.__disabled) {
                            return
                        }

                        let previous = this.$focus.getPrevious()
                        if (this.$focus.getPrevious()) {
                            this.$focus.previous()
                            this.__onValueChange(previous.dataset.value)
                        } else if (this.loop) {
                            previous = this.$focus.getLast()
                            this.$focus.last()
                            this.__onValueChange(previous.dataset.value)
                        }
                    },

                    __selectNext() {
                        if (this.__disabled) {
                            return
                        }

                        let next = this.$focus.getNext()
                        if (next) {
                            this.$focus.next()
                            this.__onValueChange(next.dataset.value)
                        } else if (this.loop) {
                            next = this.$focus.getFirst()
                            this.$focus.first()
                            this.__onValueChange(next.dataset.value)
                        }
                    },

                    __onValueChange(value) {
                        if (this.__disabled) {
                            return
                        }

                        this.__value = value
                    },

                    init() {
                        this.$el.removeAttribute('x-radio-group')
                    },
                }
            },
            'x-modelable': '__value',
        }))
    }

    const handleItem = (el, Alpine, { value, disabled }) => {
        Alpine.bind(el, () => ({
            ':aria-checked'() {
                return this.checked ? 'true' : 'false'
            },
            ':data-state'() {
                return this.checked ? 'checked' : 'unchecked'
            },
            ':data-disabled'() {
                return this.disabled ? true : undefined
            },
            ':disabled'() {
                return this.disabled ? true : undefined
            },
            '@click'() {
                if (this.disabled) {
                    return
                }
                this.__onValueChange(this.value)
            },
            'x-data'() {
                return {
                    value,
                    disabled,

                    get checked() {
                        return this.__value === this.value
                    },

                    init() {
                        this.$el.removeAttribute('x-radio-group:item')
                    },
                }
            },
        }))
    }

    Alpine.directive(
        'radio-group',
        (el, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(el, Alpine, params)
            else if (value === 'item') handleItem(el, Alpine, params)
            else {
                console.warn(`Unknown radio group directive value: ${value}`)
            }
        },
    ).before('bind')
}
