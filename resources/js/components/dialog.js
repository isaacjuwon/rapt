export const registerComponent = () => {
    const DIALOG_COMPONENT_ID = 'dialog'

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

                    __onOpenChange(newValue) {
                        this.__open = newValue
                    },

                    __makeTitleId() {
                        return this.$id(DIALOG_COMPONENT_ID + '-title')
                    },

                    __makeDescriptionId() {
                        return this.$id(DIALOG_COMPONENT_ID + '-description')
                    },

                    init() {
                        this.$el.removeAttribute('x-dialog')

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
                return [DIALOG_COMPONENT_ID]
            },
            'x-modelable': '__open',
        }))
    }

    const handleContent = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ...stateProps,
            ':id'() {
                return this.$id(DIALOG_COMPONENT_ID + '-dialog')
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
            'x-transition:enter-start': 'opacity-0 scale-90 backdrop:opacity-0',
            'x-transition:enter-end':
                'opacity-100 scale-100 backdrop:opacity-100',
            'x-transition:leave':
                'transition backdrop:transition ease-in duration-200',
            'x-transition:leave-start':
                'opacity-100 scale-100 backdrop:opacity-100',
            'x-transition:leave-end': 'opacity-0 scale-90 backdrop:opacity-0',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-dialog:content')
                this.__main._x_ewa_dialog = this.$el
            },
        }))
    }

    const handleTrigger = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ...stateProps,
            ':id'() {
                return this.$id(DIALOG_COMPONENT_ID + '-trigger')
            },
            '@click': '__onOpenChange(true)',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-dialog:trigger')
                this.__main._x_ewa_trigger = this.$el
            },
        }))
    }

    const handleClose = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ':id'() {
                return this.$id(DIALOG_COMPONENT_ID + '-close')
            },
            '@click': '__onOpenChange(false)',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-dialog:close')
            },
        }))
    }

    const handleTitle = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ':id': '__makeTitleId()',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-dialog:title')
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
                this.$el.removeAttribute('x-dialog:description')
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
        'dialog',
        (el, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(el, Alpine, params)
            else if (value === 'content') handleContent(el, Alpine)
            else if (value === 'trigger') handleTrigger(el, Alpine)
            else if (value === 'title') handleTitle(el, Alpine)
            else if (value === 'description') handleDescription(el, Alpine)
            else if (value === 'close') handleClose(el, Alpine)
            else {
                console.warn(`Unknown dialog directive value: ${value}`)
            }
        },
    ).before('bind')
}
