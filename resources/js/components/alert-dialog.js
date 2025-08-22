export const registerComponent = () => {
    const ALERT_DIALOG_COMPONENT_ID = 'alert-dialog'

    const stateProps = {
        ':data-state'() {
            return this.__open ? 'open' : 'closed'
        },
    }

    const handleRoot = (el, Alpine, { defaultOpen }) => {
        Alpine.bind(el, () => ({
            ...stateProps,
            'x-data'() {
                return {
                    __main: el,
                    __open: defaultOpen,
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
                        return this.$id(ALERT_DIALOG_COMPONENT_ID + '-title')
                    },

                    __makeDescriptionId() {
                        return this.$id(
                            ALERT_DIALOG_COMPONENT_ID + '-description',
                        )
                    },

                    init() {
                        this.$el.removeAttribute('x-alert-dialog')

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
                return [ALERT_DIALOG_COMPONENT_ID]
            },
            'x-modelable': '__open',
        }))
    }

    const handleContent = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ...stateProps,
            ':id'() {
                return this.$id(ALERT_DIALOG_COMPONENT_ID + '-dialog')
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
                this.$el.removeAttribute('x-alert-dialog:content')
                this.__main._x_ewa_dialog = this.$el
            },
        }))
    }

    const handleAlertTrigger = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ...stateProps,
            ':id'() {
                return this.$id(ALERT_DIALOG_COMPONENT_ID + '-trigger')
            },
            '@click': '__onOpenChange(true)',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-alert-dialog:trigger')
                this.__main._x_ewa_trigger = this.$el
            },
        }))
    }

    const handleAlertCancel = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ':id'() {
                return this.$id(ALERT_DIALOG_COMPONENT_ID + '-cancel')
            },
            '@click': '__onOpenChange(false)',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-alert-dialog:cancel')
            },
        }))
    }

    const handleAlertTitle = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ':id': '__makeTitleId()',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-alert-dialog:title')
                this.$el
                    .closest('dialog')
                    ?.setAttribute('aria-labelledby', this.__makeTitleId())
            },
        }))
    }

    const handleAlertDescription = (el, Alpine) => {
        Alpine.bind(el, () => ({
            ':id': '__makeDescriptionId()',
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-alert-dialog:description')
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
        'alert-dialog',
        (el, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(el, Alpine, params)
            else if (value === 'content') handleContent(el, Alpine)
            else if (value === 'trigger') handleAlertTrigger(el, Alpine)
            else if (value === 'cancel') handleAlertCancel(el, Alpine)
            else if (value === 'title') handleAlertTitle(el, Alpine)
            else if (value === 'description') handleAlertDescription(el, Alpine)
            else {
                console.warn(`Unknown alert-dialog directive value: ${value}`)
            }
        },
    ).before('bind')
}
