export const registerComponent = () => {
    const TAB_COMPONENT_ID = 'tabs'

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
        { defaultValue, orientation, dir, activationMode },
    ) => {
        Alpine.bind(el, () => ({
            ...commonProps,
            'x-id'() {
                return [TAB_COMPONENT_ID]
            },
            'x-data'() {
                return {
                    __value: defaultValue,
                    defaultValue,
                    orientation,
                    dir,
                    activationMode,

                    get isAutomaticActivation() {
                        return this.activationMode === 'automatic'
                    },

                    __onValueChange(newValue) {
                        this.__value = newValue
                    },

                    __makeTriggerId(baseId, value) {
                        return `${baseId}-trigger-${value}`
                    },

                    __makeContentId(baseId, value) {
                        return `${baseId}-content-${value}`
                    },

                    init() {
                        this.$el.removeAttribute('x-tab')

                        if (!this.__value) {
                            this.__value = this.defaultValue
                        }
                    },
                }
            },
            'x-modelable': '__value',
        }))
    }

    const handleList = (el, Alpine, { loop }) => {
        Alpine.bind(el, () => ({
            ...commonProps,
            '@keydown.right'() {
                if (this.orientation !== 'horizontal') {
                    return
                }

                if (this.$focus.getNext()) {
                    this.$focus.next()
                } else if (this.loop) {
                    this.$focus.first()
                }
            },
            '@keydown.left'() {
                if (this.orientation !== 'horizontal') {
                    return
                }

                if (this.$focus.getPrevious()) {
                    this.$focus.previous()
                } else if (this.loop) {
                    this.$focus.last()
                }
            },
            '@keydown.down'(event) {
                if (this.orientation !== 'vertical') {
                    return
                }

                event.preventDefault()

                if (this.$focus.getNext()) {
                    this.$focus.next()
                } else if (this.loop) {
                    this.$focus.first()
                }
            },
            '@keydown.up'(event) {
                if (this.orientation !== 'vertical') {
                    return
                }

                event.preventDefault()

                if (this.$focus.getPrevious()) {
                    this.$focus.previous()
                } else if (this.loop) {
                    this.$focus.last()
                }
            },
            'x-data'() {
                return {
                    loop,

                    init() {
                        this.$el.removeAttribute('x-tab:list')
                    },
                }
            },
        }))
    }

    const handleTrigger = (el, Alpine, { value }) => {
        Alpine.bind(el, () => ({
            ':aria-selected'() {
                return this.isSelected ? 'true' : 'false'
            },
            ':aria-controls'() {
                return this.__makeContentId(
                    this.$id(TAB_COMPONENT_ID),
                    this.value,
                )
            },
            ':data-state'() {
                return this.isSelected ? 'active' : 'inactive'
            },
            ':data-value'() {
                return this.value
            },
            ':data-disabled'() {
                return this.disabled ? 'true' : 'false'
            },
            ':id'() {
                return this.__makeTriggerId(
                    this.$id(TAB_COMPONENT_ID),
                    this.value,
                )
            },
            '@mousedown'(event) {
                if (
                    !this.disabled &&
                    event.button === 0 &&
                    event.ctrlKey === false
                ) {
                    this.__onValueChange(this.value)
                } else {
                    event.preventDefault()
                }
            },
            '@keydown.enter'() {
                this.__onValueChange(this.value)
            },
            '@keydown.space'() {
                this.__onValueChange(this.value)
            },
            '@focus'() {
                if (
                    !this.isSelected &&
                    !this.disabled &&
                    this.isAutomaticActivation
                ) {
                    this.__onValueChange(this.value)
                }
            },
            'x-data'() {
                return {
                    value,
                    disabled: false,

                    get isSelected() {
                        return this.__value === this.value
                    },

                    init() {
                        this.$el.removeAttribute('x-tab:trigger')
                        this.disabled = this.$el.hasAttribute('disabled')
                    },
                }
            },
        }))
    }

    const handleContent = (el, Alpine, { value }) => {
        Alpine.bind(el, () => ({
            ...commonProps,
            ':data-state'() {
                return this.isSelected ? 'active' : 'inactive'
            },
            ':data-value'() {
                return this.value
            },
            ':id'() {
                return this.__makeContentId(
                    this.$id(TAB_COMPONENT_ID),
                    this.value,
                )
            },
            ':aria-labelledby'() {
                return this.__makeTriggerId(
                    this.$id(TAB_COMPONENT_ID),
                    this.value,
                )
            },
            ':hidden'() {
                return !this.isSelected
            },
            'x-data'() {
                return {
                    value,

                    get isSelected() {
                        return this.__value === this.value
                    },

                    init() {
                        this.$el.removeAttribute('x-tab:content')
                    },
                }
            },
        }))
    }

    Alpine.directive(
        'tab',
        (el, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(el, Alpine, params)
            else if (value === 'list') handleList(el, Alpine, params)
            else if (value === 'trigger') handleTrigger(el, Alpine, params)
            else if (value === 'content') handleContent(el, Alpine, params)
            else {
                console.warn(`Unknown tab directive value: ${value}`)
            }
        },
    ).before('bind')
}
