export const registerComponent = () => {
    const commonProps = {
        ':data-state'() {
            return this.state
        },
        ':data-value'() {
            return this.__value
        },
        ':data-max'() {
            return this.max
        },
    }

    const handleRoot = (el, Alpine, { value, max }) => {
        Alpine.bind(el, () => ({
            ...commonProps,
            ':aria-valuemin'() {
                return this.initialValue
            },
            ':aria-valuemax'() {
                return this.max
            },
            'x-data'() {
                return {
                    initialValue: value,
                    __value: value,
                    max,

                    get state() {
                        return this._value >= this.max ? 'complete' : 'loading'
                    },

                    init() {
                        this.$el.removeAttribute('x-progress')
                    },
                }
            },
            'x-modelable': '__value',
        }))
    }

    const handleIndicator = (el, Alpine, {}) => {
        Alpine.bind(el, () => ({
            ...commonProps,
            ':style'() {
                return {
                    transform: `translateX(-${100 - (this.__value ?? 0)}%)`,
                }
            },
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-progress:indicator')
            },
        }))
    }

    Alpine.directive(
        'progress',
        (e, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(e, Alpine, params)
            else if (value === 'indicator') handleIndicator(e, Alpine, params)
            else {
                console.warn(`Unknown progress directive value: ${value}`)
            }
        },
    ).before('bind')
}
