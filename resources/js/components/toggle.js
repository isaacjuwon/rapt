export const registerComponent = () => {
    const handleRoot = (el, Alpine, { defaultPressed, disabled }) => {
        Alpine.bind(el, () => ({
            ':aria-pressed'() {
                return this.__value ? 'true' : 'false'
            },
            ':disabled'() {
                return this.disabled ? 'disabled' : undefined
            },
            ':data-disabled'() {
                return this.disabled ? 'disabled' : undefined
            },
            ':data-state'() {
                return this.__value ? 'on' : 'off'
            },
            '@click'() {
                if (!this.disabled) {
                    this.__onValueChange(!this.__value)
                }
            },
            '@keydown.enter.prevent'() {
                if (!this.disabled) {
                    this.__onValueChange(!this.__value)
                }
            },
            'x-data'() {
                return {
                    __value: defaultPressed,
                    defaultPressed,
                    disabled,

                    get pressed() {
                        return this.__value
                    },

                    __onValueChange(value) {
                        this.__value = value
                    },
                }
            },
            'x-modelable': '__value',
        }))
    }

    Alpine.directive(
        'toggle',
        (e, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(e, Alpine, params)
            else {
                console.warn(`Unknown checkbox directive value: ${value}`)
            }
        },
    ).before('bind')
}
