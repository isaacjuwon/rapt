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
    }

    const handleRoot = (
        el,
        Alpine,
        type,
        { defaultValue, rovingFocus, orientation, dir, loop, disabled },
    ) => {
        Alpine.bind(el, () => ({
            ...commonProps,
            '@keydown.right'() {
                if (
                    this.__disabled ||
                    !this.rovingFocus ||
                    this.orientation !== 'horizontal'
                ) {
                    return
                }

                if (this.$focus.getNext()) {
                    this.$focus.next()
                } else if (this.loop) {
                    this.$focus.first()
                }
            },
            '@keydown.left'() {
                if (
                    this.__disabled ||
                    !this.rovingFocus ||
                    this.orientation !== 'horizontal'
                ) {
                    return
                }

                if (this.$focus.getPrevious()) {
                    this.$focus.previous()
                } else if (this.loop) {
                    this.$focus.last()
                }
            },
            'x-data'() {
                return {
                    __value: defaultValue,
                    __disabled: disabled,
                    defaultValue,
                    rovingFocus,
                    orientation,
                    dir,
                    loop,
                    type,

                    __onValueChange(newValue) {
                        if (this.__disabled) {
                            return
                        }

                        if (this.type === 'single') {
                            this.__value = [newValue]
                        } else {
                            this.__value = this.__value.includes(newValue)
                                ? this.__value.filter(
                                      (item) => item !== newValue,
                                  )
                                : [...new Set([...this.__value, newValue])]
                        }
                    },

                    init() {
                        this.$el.removeAttribute(`x-toggle-group.${type}`)
                    },
                }
            },
            'x-modelable': '__value',
        }))
    }

    const handleItem = (
        el,
        Alpine,
        type,
        { value, disabled, orientation, dir },
    ) => {
        Alpine.bind(el, () => ({
            ...commonProps,
            ':data-state'() {
                return this.selected ? 'on' : 'off'
            },
            ':aria-checked'() {
                return this.selected ? 'true' : 'false'
            },
            ':data-disabled'() {
                return this.disabled ? true : undefined
            },
            ':disabled'() {
                return this.disabled ? 'disabled' : undefined
            },
            '@click'() {
                if (!this.disabled) {
                    this.__onValueChange(this.value)
                }
            },
            '@keydown.enter.prevent'() {
                if (!this.disabled) {
                    this.__onValueChange(this.value)
                }
            },
            'x-data'() {
                return {
                    value,
                    disabled,
                    type,
                    orientation,
                    dir,

                    get selected() {
                        return this.__value.includes(this.value)
                    },

                    init() {
                        this.$el.removeAttribute(`x-toggle-group.${type}:item`)
                    },
                }
            },
        }))
    }

    Alpine.directive(
        'toggle-group',
        (el, { value, modifiers, expression }, { Alpine, evaluate }) => {
            const type = modifiers[0].split(':')[0]
            const params = expression ? evaluate(expression) : {}

            if (!['single', 'multiple'].includes(type)) {
                throw new Error(
                    `Invalid toggle group type: ${type}. Expected 'single' or 'multiple'.`,
                )
            }

            if (!value) handleRoot(el, Alpine, type, params)
            else if (value === 'item') handleItem(el, Alpine, type, params)
            else {
                console.warn(`Unknown toggle group directive value: ${value}`)
            }
        },
    ).before('bind')
}
