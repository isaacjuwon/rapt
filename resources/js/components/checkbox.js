export const registerComponent = () => {
    const handleRoot = (el, Alpine, { defaultChecked, disabled }) => {
        Alpine.bind(el, () => ({
            ':aria-checked'() {
                return this.__value ? 'true' : 'false'
            },
            ':data-state'() {
                return this.__value ? 'checked' : 'unchecked'
            },
            '@click'() {
                if (!this.disabled) {
                    this.__onValueChange(!this.__value)
                }
            },
            'x-data'() {
                return {
                    __value: defaultChecked,
                    defaultChecked,
                    disabled,

                    get checked() {
                        return this.__value
                    },

                    __onValueChange(newValue) {
                        this.__value = newValue
                    },

                    init() {
                        this.$el.removeAttribute('x-checkbox')
                    },
                }
            },
            'x-modelable': '__value',
        }))
    }

    Alpine.directive(
        'checkbox',
        (e, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(e, Alpine, params)
            else {
                console.warn(`Unknown checkbox directive value: ${value}`)
            }
        },
    ).before('bind')
}
