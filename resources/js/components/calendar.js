export const registerComponent = () => {
    const handleRoot = (el, Alpine, { settings }) => {
        Alpine.bind(el, {
            '@keydown.down.prevent': '',
            '@keydown.up.prevent': '',
            'x-data'() {
                return {
                    __values: [],
                    __calendarSettings: settings,
                    __calendar: undefined,

                    init() {
                        this.$el.removeAttribute('x-calendar')

                        this.$nextTick(() => {
                            this.__calendarSettings.selectedDates =
                                this.$calendarUtils.parseDates(
                                    this.__calendarSettings.selectedDates ?? [],
                                )
                            this.__values =
                                this.__calendarSettings.selectedDates

                            this.__calendar = this.$calendar(this.$el, {
                                ...this.__calendarSettings,
                                onClickDate: (self, event) => {
                                    this.__values = self.context.selectedDates
                                },
                            })

                            this.__calendar.init()
                        })

                        this.$watch('__values', (newValues) => {
                            if (
                                this.__calendar &&
                                JSON.stringify(newValues) !==
                                    JSON.stringify(
                                        this.__calendar.context.selectedDates,
                                    )
                            ) {
                                this.__calendar.set({
                                    selectedDates: newValues,
                                })
                            }
                        })
                    },

                    destroy() {
                        this.__calendar?.destroy()
                    },
                }
            },
            'x-modelable': '__values',
        })
    }

    Alpine.directive(
        'calendar',
        (el, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(el, Alpine, params)
            else {
                console.warn(`Unknown calendar directive value: ${value}`)
            }
        },
    ).before('bind')
}
