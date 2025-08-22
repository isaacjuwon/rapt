export const registerComponent = () => {
    const DRAWER_COMPONENT_ID = 'drawer'

    const stateProps = {
        ':data-state'() {
            return this.__open ? 'open' : 'closed'
        },
    }

    const handleRoot = (el, Alpine, { defaultOpen, modal }) => {
        Alpine.bind(el, () => ({
            ...stateProps,
            'x-data'() {
                return {
                    __main: el,
                    __open: defaultOpen,
                    __modal: modal,
                    defaultOpen,

                    get __dialog() {
                        return this.__main._x_ewa_dialog
                    },

                    get __trigger() {
                        return this.__main._x_ewa_trigger
                    },

                    get __handle() {
                        return this.__main._x_ewa_handle
                    },

                    __onOpenChange(newValue) {
                        this.__open = newValue
                    },

                    __makeTitleId() {
                        return this.$id(DRAWER_COMPONENT_ID + '-title')
                    },

                    __makeDescriptionId() {
                        return this.$id(DRAWER_COMPONENT_ID + '-description')
                    },

                    init() {
                        this.$el.removeAttribute('x-drawer')

                        this.$watch('__open', (newValue) => {
                            if (newValue) {
                                this.__dialog?.showModal()
                            } else {
                                const duration =
                                    Array.from(this.__dialog?.classList || [])
                                        .find((cls) =>
                                            cls.startsWith('duration-'),
                                        )
                                        ?.split('-')[1] || 0

                                setTimeout(
                                    () => this.__dialog?.close(),
                                    duration,
                                )
                            }
                        })
                    },
                }
            },
            'x-id'() {
                return [DRAWER_COMPONENT_ID]
            },
            'x-modelable': '__open',
        }))
    }

    const handleContent = (el, Alpine, { side }) => {
        Alpine.bind(el, () => ({
            ...stateProps,
            ':id'() {
                return this.$id(DRAWER_COMPONENT_ID + '-dialog')
            },
            ':data-side'() {
                return side
            },
            '@keydown.escape.window'() {
                if (this.__open) this.__onOpenChange(false)
            },
            '@click'() {
                const rect = this.__dialog?.getBoundingClientRect()

                if (!rect) return

                const clientX =
                    'clientX' in event
                        ? event.clientX
                        : event.touches[0]?.clientX
                const clientY =
                    'clientY' in event
                        ? event.clientY
                        : event.touches[0]?.clientY

                const top = rect.top
                const right = rect.right
                const bottom = rect.bottom
                const left = rect.left

                if (
                    rect.left > clientX ||
                    clientX > rect.right ||
                    rect.top > clientY ||
                    clientY > rect.bottom
                ) {
                    if (!this.__modal) return

                    this.__onOpenChange(false)
                }
            },
            'x-show': '__open',
            'x-transition:enter':
                'transition backdrop:transition ease-out duration-200',
            'x-transition:enter-start'() {
                return (
                    {
                        top: 'opacity-0 backdrop:opacity-0 -translate-y-full',
                        right: 'opacity-0 backdrop:opacity-0 translate-x-full',
                        bottom: 'opacity-0 backdrop:opacity-0 translate-y-full',
                        left: 'opacity-0 backdrop:opacity-0 -translate-x-full',
                    }[side] || 'opacity-0 backdrop:opacity-0'
                )
            },
            'x-transition:enter-end'() {
                return (
                    {
                        top: 'opacity-100 backdrop:opacity-100 translate-y-0',
                        right: 'opacity-100 backdrop:opacity-100 translate-x-0',
                        bottom: 'opacity-100 backdrop:opacity-100 translate-y-0',
                        left: 'opacity-100 backdrop:opacity-100 translate-x-0',
                    }[side] || 'opacity-100 backdrop:opacity-100'
                )
            },
            'x-transition:leave':
                'transition backdrop:transition ease-in duration-200',
            'x-transition:leave-start'() {
                return (
                    {
                        top: 'opacity-100 backdrop:opacity-100 translate-y-0',
                        right: 'opacity-100 backdrop:opacity-100 translate-x-0',
                        bottom: 'opacity-100 backdrop:opacity-100 translate-y-0',
                        left: 'opacity-100 backdrop:opacity-100 translate-x-0',
                    }[side] || 'opacity-100 backdrop:opacity-100'
                )
            },
            'x-transition:leave-end'() {
                return (
                    {
                        top: 'opacity-0 backdrop:opacity-0 -translate-y-full',
                        right: 'opacity-0 backdrop:opacity-0 translate-x-full',
                        bottom: 'opacity-0 backdrop:opacity-0 translate-y-full',
                        left: 'opacity-0 backdrop:opacity-0 -translate-x-full',
                    }[side] || 'opacity-0 backdrop:opacity-0'
                )
            },
            'x-data'() {
                return {
                    side,

                    init() {
                        this.$el.removeAttribute('x-drawer:content')
                        this.__main._x_ewa_dialog = this.$el
                    },
                }
            },
        }))
    }

    const handleTrigger = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ...stateProps,
            ':id'() {
                return this.$id(DRAWER_COMPONENT_ID + '-trigger')
            },
            '@click': '__onOpenChange(true)',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-drawer:trigger')
                this.__main._x_ewa_trigger = this.$el
            },
        }))
    }

    const handleClose = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ':id'() {
                return this.$id(DRAWER_COMPONENT_ID + '-close')
            },
            '@click': '__onOpenChange(false)',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-drawer:close')
            },
        }))
    }

    const handleTitle = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ':id': '__makeTitleId()',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-drawer:title')
                this.$el
                    .closest('dialog')
                    ?.setAttribute('aria-labelledby', this.__makeTitleId())
            },
        }))
    }

    const handleDescription = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ':id': '__makeDescriptionId()',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-drawer:description')
                this.$el
                    .closest('dialog')
                    ?.setAttribute(
                        'aria-describedby',
                        this.__makeDescriptionId(),
                    )
            },
        }))
    }

    Alpine.directive(
        'drawer',
        (el, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(el, Alpine, params)
            else if (value === 'content') handleContent(el, Alpine, params)
            else if (value === 'trigger') handleTrigger(el, Alpine, params)
            else if (value === 'title') handleTitle(el, Alpine, params)
            else if (value === 'description')
                handleDescription(el, Alpine, params)
            else if (value === 'close') handleClose(el, Alpine, params)
            else {
                console.warn(`Unknown dialog directive value: ${value}`)
            }
        },
    ).before('bind')
}
