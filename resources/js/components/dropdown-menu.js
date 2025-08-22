export const registerComponent = () => {
    const DROPDOWN_MENU_COMPONENT_ID = 'dropdown-menu'
    const DROPDOWN_MENU_SUB_COMPONENT_ID = 'dropdown-menu-sub'

    const handleRoot = (el, Alpine, { defaultOpen, dir, modal }) => {
        Alpine.bind(el, () => ({
            'x-id'() {
                return [DROPDOWN_MENU_COMPONENT_ID]
            },
            '@keydown.escape'() {
                if (this.__open) {
                    this.__onOpenChange(false)
                }
            },
            '@click.window'() {
                if (this.__open) {
                    this.__onOpenChange(false)
                }
            },
            'x-data'() {
                return {
                    __main: el,
                    __open: defaultOpen,
                    defaultOpen,
                    dir,
                    modal,

                    get __trigger() {
                        return this.__main._x_ewa_trigger
                    },

                    __onOpenChange(newValue) {
                        this.$nextTick(() => {
                            this.__open = newValue
                        })
                    },

                    __makeTriggerId(baseId) {
                        return `${baseId}-trigger`
                    },

                    __makeContentId(baseId) {
                        return `${baseId}-content`
                    },

                    init() {
                        this.$el.removeAttribute('x-dropdown-menu')

                        this.$watch('__open', (newValue) => {
                            if (this.modal) {
                                if (newValue) {
                                    document.body.setAttribute(
                                        'data-scroll-locked',
                                        '1',
                                    )
                                    document.body.style.pointerEvents = 'none'
                                } else {
                                    document.body.removeAttribute(
                                        'data-scroll-locked',
                                    )
                                    document.body.style.pointerEvents = ''
                                }
                            }
                        })
                    },
                }
            },
            'x-modelable': '__open',
        }))
    }

    const handleTrigger = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ':id'() {
                return this.__makeTriggerId(
                    this.$id(DROPDOWN_MENU_COMPONENT_ID),
                )
            },
            ':aria-expanded'() {
                return this.__open ? 'true' : 'false'
            },
            ':aria-controls'() {
                return this.__open
                    ? this.__makeContentId(this.$id(DROPDOWN_MENU_COMPONENT_ID))
                    : undefined
            },
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-dropdown-menu:trigger')
                this.__main._x_ewa_trigger = this.$el
            },
            '@click'() {
                this.__onOpenChange(!this.__open)
            },
        }))
    }

    const handleContent = (el, Alpine, { side, align, sideOffset }) => {
        Alpine.bind(el, () => ({
            'x-show'() {
                return this.__open
            },
            ':data-state'() {
                return this.__open ? 'open' : 'closed'
            },
            ':data-side'() {
                return side
            },
            ':data-align'() {
                return align
            },
            ':id'() {
                return this.__makeContentId(
                    this.$id(DROPDOWN_MENU_COMPONENT_ID),
                )
            },
            ':aria-labelledby'() {
                return this.__makeTriggerId(
                    this.$id(DROPDOWN_MENU_COMPONENT_ID),
                )
            },
            'x-transition:enter': 'transition ease-in duration-150',
            'x-transition:enter-start': 'opacity-0 scale-95 translate-y-2',
            'x-transition:enter-end': 'opacity-100 scale-100 translate-y-0',
            'x-transition:leave': 'transition ease duration-150',
            'x-transition:leave-start': 'opacity-100 scale-100 translate-y-0',
            'x-transition:leave-end': 'opacity-0 scale-95 translate-y-2',
            'x-anchorplus'() {
                return {
                    reference: this.__trigger,
                    placement:
                        this.side +
                        (this.align === 'center' ? '' : `-${this.align}`),
                    sideOffset: this.sideOffset,
                }
            },
            '@keydown.down'() {
                if (this.__open) {
                    if (this.$focus.getNext()) this.$focus.next()
                    else if (this.$focus.focused() === el) {
                        this.$focus.first()
                    }
                }
            },
            '@keydown.up'() {
                if (this.__open) {
                    if (this.$focus.getPrevious()) this.$focus.previous()
                    else if (this.$focus.focused() === el) {
                        this.$focus.last()
                    }
                }
            },
            '@keydown.escape'() {
                if (this.__open) {
                    this.__onOpenChange(false)
                    this.$focus.focus(this.__trigger)
                }
            },
            'x-data'() {
                return {
                    side,
                    align,
                    sideOffset,

                    init() {
                        this.$el.removeAttribute('x-dropdown-menu:content')
                        this.__main._x_ewa_content = this.$el

                        this.$watch('__open', (newValue) => {
                            if (newValue) {
                                this.$nextTick(() => {
                                    this.$focus.focus(this.$el)
                                })
                            }
                        })
                    },
                }
            },
        }))
    }

    const menuItemCommonProps = (el) => ({
        '@mouseenter'() {
            this.$focus.focus(el)
            this.isFocused = true

            this.$el
                .closest(
                    `#${this.__makeContentId(this.$id(DROPDOWN_MENU_COMPONENT_ID))}`,
                )
                ?.querySelectorAll('[data-state="open"]')
                .forEach((el) => el.__closeDropdownSubMenu())
        },
        '@mouseleave'() {
            if (this.$focus.focused() === el) {
                el.blur()
                this.isFocused = false
            }
        },
        ':data-highlighted'() {
            return this.isFocused ? '' : undefined
        },
        ':data-disabled'() {
            return this.disabled ? 'true' : undefined
        },
        ':aria-disabled'() {
            return this.disabled ? 'true' : undefined
        },
        ':disabled'() {
            return this.disabled ? 'disabled' : undefined
        },
        ':tabindex'() {
            return this.disabled ? undefined : '-1'
        },
    })

    const handleItem = (el, Alpine, { disabled }) => {
        Alpine.bind(el, () => ({
            ...menuItemCommonProps(el),
            'x-data'() {
                return {
                    isFocused: false,
                    disabled,

                    init() {
                        this.$el.removeAttribute('x-dropdown-menu:item')
                    },
                }
            },
        }))
    }

    const handleCheckboxItem = (el, Alpine, { disabled }) => {
        Alpine.bind(el, () => ({
            ...menuItemCommonProps(el),
            ':aria-checked'() {
                return this.__checked ? 'true' : 'false'
            },
            ':data-state'() {
                return this.__checked ? 'checked' : 'unchecked'
            },
            ':checked'() {
                return this.__checked ? 'true' : 'false'
            },
            '@click'() {
                if (!this.disabled) {
                    this.__checked = !this.__checked
                    this.__onOpenChange(false)
                }
            },
            '@keydown.enter'() {
                if (!this.disabled) {
                    this.__checked = !this.__checked
                    this.__onOpenChange(false)
                }
            },
            '@keydown.space'() {
                if (!this.disabled) {
                    this.__checked = !this.__checked
                    this.__onOpenChange(false)
                }
            },
            'x-data'() {
                return {
                    __checked: false,
                    isFocused: false,
                    disabled,

                    init() {
                        this.$el.removeAttribute(
                            'x-dropdown-menu:checkbox-item',
                        )
                    },
                }
            },
            'x-modelable': '__checked',
        }))
    }

    const handleCheckboxItemIndicator = (el, Alpine) => {
        Alpine.bind(el, () => ({
            'x-show'() {
                return this.__checked
            },
            ':data-state'() {
                return this.__checked ? 'checked' : 'unchecked'
            },
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute(
                    'x-dropdown-menu:checkbox-item-indicator',
                )
            },
        }))
    }

    const handleRadioGroup = (el, Alpine, { defaultValue, disabled }) => {
        Alpine.bind(el, () => ({
            'x-data'() {
                return {
                    __value: defaultValue,
                    defaultValue,
                    disabled,

                    __onValueChange(newValue) {
                        if (this.disabled) return

                        this.__value = newValue
                    },

                    init() {
                        this.$el.removeAttribute('x-dropdown-menu:radio-group')
                    },
                }
            },
            'x-modelable': '__value',
        }))
    }

    const handleRadioItem = (el, Alpine, { value, disabled }) => {
        Alpine.bind(el, () => ({
            ...menuItemCommonProps(el),
            ':aria-checked'() {
                return this.__value === this.value ? 'true' : 'false'
            },
            ':data-state'() {
                return this.__value === this.value ? 'checked' : 'unchecked'
            },
            ':data-value'() {
                return this.value
            },
            '@click'() {
                if (!this.disabled) {
                    this.__onValueChange(this.value)
                }
            },
            '@keydown.enter'() {
                if (!this.disabled) {
                    this.__onValueChange(this.value)
                    this.__onOpenChange(false)
                }
            },
            '@keydown.space'() {
                if (!this.disabled) {
                    this.__onValueChange(this.value)
                    this.__onOpenChange(false)
                }
            },
            'x-data'() {
                return {
                    value,
                    disabled,
                    isFocused: false,

                    get __checked() {
                        return this.__value === this.value
                    },

                    init() {
                        this.$el.removeAttribute('x-dropdown-menu:radio-item')
                    },
                }
            },
        }))
    }

    const handleRadioGroupItemIndicator = (el, Alpine) => {
        Alpine.bind(el, () => ({
            'x-show'() {
                return this.__checked
            },
            ':data-state'() {
                return this.__checked ? 'checked' : 'unchecked'
            },
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-dropdown-menu:radio-item-indicator')
            },
        }))
    }

    const handleSubRoot = (el, Alpine) => {
        Alpine.bind(el, () => ({
            'x-id'() {
                return [DROPDOWN_MENU_SUB_COMPONENT_ID]
            },
            'x-data'() {
                return {
                    __subMain: el,
                    __subOpen: false,

                    get __trigger() {
                        return this.__subMain._x_ewa_trigger
                    },

                    get __content() {
                        return this.__subMain._x_ewa_content
                    },

                    __onSubOpenChange(newValue) {
                        this.__subOpen = newValue
                    },

                    init() {
                        this.$el.removeAttribute('x-dropdown-menu:sub')

                        this.$watch('__open', (newValue) => {
                            if (!newValue) {
                                this.$nextTick(() => {
                                    this.__subOpen = false
                                })
                            }
                        })
                    },
                }
            },
            'x-modelable': '__subOpen',
        }))
    }

    const handleSubTrigger = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ...menuItemCommonProps(el),
            '@mouseenter'() {
                this.__onSubOpenChange(true)
                this.isFocused = true
            },
            '@mouseleave'(event) {
                this.isFocused = false
            },
            '@keydown.right'() {
                if (!this.__subOpen) {
                    this.__onSubOpenChange(true)

                    const firstItem =
                        this.__content.querySelector('[role="menuitem"]')

                    if (firstItem) {
                        this.$nextTick(() => {
                            this.$nextTick(() => {
                                this.$focus.focus(firstItem)
                            })
                        })
                    }
                }
            },
            ':tabindex'() {
                return this.disabled ? undefined : '0'
            },
            ':id'() {
                return this.__makeTriggerId(
                    this.$id(DROPDOWN_MENU_SUB_COMPONENT_ID),
                )
            },
            ':aria-expanded'() {
                return this.__subOpen ? 'true' : 'false'
            },
            ':data-state'() {
                return this.__subOpen ? 'open' : 'closed'
            },
            ':aria-controls'() {
                return this.__subOpen
                    ? this.__makeContentId(
                          this.$id(DROPDOWN_MENU_SUB_COMPONENT_ID),
                      )
                    : undefined
            },
            'x-data'() {
                return {
                    isFocused: false,

                    init() {
                        this.$el.removeAttribute('x-dropdown-menu:sub-trigger')
                        this.__subMain._x_ewa_trigger = this.$el

                        this.$el.__closeDropdownSubMenu = () => {
                            this.__onSubOpenChange(false)
                        }
                    },
                }
            },
        }))
    }

    const handleSubContent = (el, Alpine, { side, align, sideOffset }) => {
        Alpine.bind(el, () => ({
            'x-show'() {
                return this.__subOpen
            },
            ':data-state'() {
                return this.__subOpen ? 'open' : 'closed'
            },
            ':data-side'() {
                return side
            },
            ':data-align'() {
                return align
            },
            ':id'() {
                return this.__makeContentId(
                    this.$id(DROPDOWN_MENU_SUB_COMPONENT_ID),
                )
            },
            ':aria-labelledby'() {
                return this.__makeTriggerId(
                    this.$id(DROPDOWN_MENU_SUB_COMPONENT_ID),
                )
            },
            'x-transition:enter': 'transition ease-in duration-150',
            'x-transition:enter-start': 'opacity-0 scale-95 translate-y-2',
            'x-transition:enter-end': 'opacity-100 scale-100 translate-y-0',
            'x-transition:leave': 'transition ease duration-150',
            'x-transition:leave-start': 'opacity-100 scale-100 translate-y-0',
            'x-transition:leave-end': 'opacity-0 scale-95 translate-y-2',
            'x-anchorplus'() {
                return {
                    reference: this.__trigger,
                    placement:
                        this.side +
                        (this.align === 'center' ? '' : `-${this.align}`),
                    sideOffset: this.sideOffset,
                }
            },
            '@keydown.down'() {
                if (this.__subOpen) {
                    if (this.$focus.getNext()) this.$focus.next()
                    else if (this.$focus.focused() === el) {
                        this.$focus.first()
                    }
                }
            },
            '@keydown.up'() {
                if (this.__subOpen) {
                    if (this.$focus.getPrevious()) this.$focus.previous()
                    else if (this.$focus.focused() === el) {
                        this.$focus.last()
                    }
                }
            },
            '@keydown.escape'() {
                if (this.__subOpen) {
                    this.__onSubOpenChange(false)
                    this.$focus.focus(this.__trigger)
                }
            },
            '@keydown.left'() {
                if (this.__subOpen) {
                    this.__onSubOpenChange(false)
                    this.$focus.focus(this.__trigger)
                }
            },
            'x-data'() {
                return {
                    side,
                    align,
                    sideOffset,

                    init() {
                        this.$el.removeAttribute('x-dropdown-menu:sub-content')
                        this.__subMain._x_ewa_content = this.$el

                        this.$watch('__subOpen', (newValue) => {
                            if (newValue) {
                                this.$nextTick(() => {
                                    this.$focus.focus(this.$el)
                                })
                            }
                        })
                    },
                }
            },
        }))
    }

    Alpine.directive(
        'dropdown-menu',
        (el, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(el, Alpine, params)
            else if (value === 'trigger') handleTrigger(el, Alpine, params)
            else if (value === 'content') handleContent(el, Alpine, params)
            else if (value === 'item') handleItem(el, Alpine, params)
            else if (value === 'checkbox-item')
                handleCheckboxItem(el, Alpine, params)
            else if (value === 'checkbox-item-indicator')
                handleCheckboxItemIndicator(el, Alpine)
            else if (value === 'radio-group')
                handleRadioGroup(el, Alpine, params)
            else if (value === 'radio-item') handleRadioItem(el, Alpine, params)
            else if (value === 'radio-item-indicator')
                handleRadioGroupItemIndicator(el, Alpine, params)
            else if (value === 'sub') handleSubRoot(el, Alpine, params)
            else if (value === 'sub-trigger')
                handleSubTrigger(el, Alpine, params)
            else if (value === 'sub-content')
                handleSubContent(el, Alpine, params)
            else {
                console.warn(`Unknown dropdown menu directive value: ${value}`)
            }
        },
    ).before('bind')
}
