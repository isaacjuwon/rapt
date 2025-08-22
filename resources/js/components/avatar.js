export const registerComponent = () => {
    const resolveLoadingStatus = (image, src = null) => {
        if (!image) return 'idle'
        else if (!src) return 'error'
        else if (image.src !== src) {
            image.src = src
        }

        return image.complete && image.naturalWidth > 0 ? 'loaded' : 'loading'
    }

    const handleRoot = (el, Alpine) => {
        Alpine.bind(el, () => ({
            'x-data'() {
                return {
                    __main: el,
                    __status: 'idle', // 'idle', 'loading', 'loaded', 'error'

                    __onStatusChange(image, src) {
                        this.__status = resolveLoadingStatus(image, src)
                    },

                    init() {
                        this.$el.removeAttribute('x-avatar')
                    },
                }
            },
        }))
    }

    const handleImage = (el, Alpine, { src }) => {
        Alpine.bind(el, () => ({
            'x-show'() {
                return this.__status === 'loading' || this.__status === 'loaded'
            },
            'x-data'() {
                return {
                    src,
                }
            },
            'x-init'() {
                this.$el.removeAttribute('x-avatar:image')
                this.$el.onload = () =>
                    this.__onStatusChange(this.$el, this.src)
                this.$el.onerror = () => this.__onStatusChange(this.$el, null)
                this.$el.src = this.src
            },
        }))
    }

    const handleFallback = (el, Alpine) => {
        Alpine.bind(el, () => ({
            'x-show'() {
                return this.__status === 'error' || this.__status === 'idle'
            },
            'x-data': '',
            'x-init'() {
                this.$el.removeAttribute('x-avatar:fallback')
                this.__main._x_ewa_fallback = this.$el
            },
        }))
    }

    Alpine.directive(
        'avatar',
        (el, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(el, Alpine, params)
            else if (value === 'image') handleImage(el, Alpine, params)
            else if (value === 'fallback') handleFallback(el, Alpine, params)
            else {
                console.warn(`Unknown avatar directive value: ${value}`)
            }
        },
    ).before('bind')
}
