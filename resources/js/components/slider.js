export const registerComponent = () => {
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
        ':data-disabled'() {
            return this.disabled ? true : undefined
        },
    }

    const handleRoot = (
        el,
        Alpine,
        {
            min,
            max,
            step,
            minStepBetweenThumbs,
            defaultValue,
            rovingFocus,
            orientation,
            dir,
            disabled,
        },
    ) => {
        Alpine.bind(el, () => ({
            ...commonProps,
            'x-data'() {
                return {
                    __values: defaultValue,
                    defaultValue,
                    min,
                    max,
                    step,
                    minStepBetweenThumbs,
                    rovingFocus,
                    orientation,
                    dir,
                    disabled,

                    get minValue() {
                        return this.__values.length > 0
                            ? Math.min(...this.__values)
                            : 0
                    },

                    get maxValue() {
                        return this.__values.length > 0
                            ? Math.max(...this.__values)
                            : 0
                    },

                    get trackElement() {
                        return this.$el.getAttribute('data-slot') === 'slider'
                            ? this.$el.querySelector([
                                  '[data-slot="slider-track"]',
                              ])
                            : this.$el
                                  .closest('[data-slot="slider"]')
                                  .querySelector('[data-slot="slider-track"]')
                    },

                    __getClosestThumbIndex(value, index = undefined) {
                        return (
                            this.__values.reduce(
                                (closestIndex, thumbValue, thumbIndex) => {
                                    // Skip index if provided
                                    if (
                                        index !== undefined &&
                                        thumbIndex === index
                                    )
                                        return closestIndex
                                    if (closestIndex === undefined)
                                        return thumbIndex

                                    const closest = Math.abs(
                                        this.__values[thumbIndex] - value,
                                    )
                                    const diff = Math.abs(thumbValue - value)

                                    return diff < Math.abs(closest - value)
                                        ? thumbIndex
                                        : closestIndex
                                },
                                undefined,
                            ) ?? -1
                        )
                    },

                    __acceptsNewValue(newValue, index) {
                        const closestThumbIndex = this.__getClosestThumbIndex(
                            newValue,
                            index,
                        )
                        const closestThumbValue =
                            this.__values[closestThumbIndex]

                        return (
                            closestThumbIndex === -1 ||
                            Math.abs(newValue - closestThumbValue) >=
                                this.minStepBetweenThumbs
                        )
                    },

                    __onValueChange(newValue, index) {
                        if (this.disabled) {
                            return
                        }

                        if (newValue < this.min || newValue > this.max) {
                            console.warn(
                                `Value ${newValue} is out of bounds for slider with min ${this.min} and max ${this.max}.`,
                            )
                            return
                        }

                        this.__values.splice(index, 1, newValue)
                    },

                    init() {
                        this.$el.removeAttribute('x-slider')

                        const thumbs = this.$el.querySelectorAll(
                            '[data-slot="slider-thumb"]',
                        )

                        if (thumbs.length !== this.__values.length) {
                            console.warn(
                                `Number of thumbs (${thumbs.length}) does not match the number of values (${this.__values.length}).`,
                            )
                        }
                    },
                }
            },
            'x-modelable': '__values',
        }))
    }

    const handleTrack = (el, Alpine, {}) => {
        Alpine.bind(el, () => ({
            ...commonProps,
            '@click'() {
                const track = this.$el.getBoundingClientRect()

                const clientX = event.clientX ?? event.touches[0].clientX
                const clientY = event.clientY ?? event.touches[0].clientY
                const mouseOnTrackPosition =
                    this.orientation === 'horizontal' ? clientX : clientY
                const minPosition =
                    this.orientation === 'horizontal' ? track.left : track.top
                const maxPosition =
                    this.orientation === 'horizontal'
                        ? track.right
                        : track.bottom
                const percentage =
                    (mouseOnTrackPosition - minPosition) /
                    (maxPosition - minPosition)
                const value =
                    Math.round(
                        this.min +
                            (percentage * (this.max - this.min)) / this.step,
                    ) * this.step

                const closestThumbIndex = this.__getClosestThumbIndex(value)

                if (closestThumbIndex === -1) {
                    console.warn(
                        'No closest thumb found for the clicked position.',
                    )
                    return
                }

                this.__onValueChange(value, closestThumbIndex)
            },
            'x-data'() {
                return {
                    init() {
                        this.$el.removeAttribute('x-slider:track')
                    },
                }
            },
        }))
    }

    const handleRange = (el, Alpine, {}) => {
        Alpine.bind(el, () => ({
            ...commonProps,
            ':style'() {
                return this.orientation === 'horizontal'
                    ? {
                          width: `${this.maxRange - this.minRange}%`,
                          left: `${this.minRange}%`,
                      }
                    : {
                          height: `${this.maxRange - this.minRange}%`,
                          bottom: `${this.minRange}%`,
                      }
            },
            'x-data'() {
                return {
                    get minRange() {
                        return this.minValue === this.maxValue
                            ? this.dir === 'ltr'
                                ? this.min
                                : this.minValue
                            : this.minValue
                    },

                    get maxRange() {
                        return this.minValue === this.maxValue
                            ? this.dir === 'ltr'
                                ? this.maxValue
                                : this.max
                            : this.maxValue
                    },

                    init() {
                        this.$el.removeAttribute('x-slider:range')
                    },
                }
            },
        }))
    }

    const handleThumb = (el, Alpine, {}) => {
        Alpine.bind(el, () => ({
            ...commonProps,
            ':aria-valuemin'() {
                return this.min
            },
            ':aria-valuemax'() {
                return this.max
            },
            ':aria-valuenow'() {
                return this.__value
            },
            '@mousedown'() {
                if (this.disabled) {
                    return
                }

                this.__isDragging = true
            },
            '@touchstart'() {
                if (this.disabled) {
                    return
                }

                this.__isDragging = true
            },
            '@mousemove.window'(event) {
                if (this.disabled) {
                    return
                }

                this.__handleDragging(event)
            },
            '@touchmove.window'(event) {
                if (this.disabled) {
                    return
                }

                this.__handleDragging(event)
            },
            '@mouseup.window'() {
                if (this.disabled) {
                    return
                }

                this.__isDragging = false
            },
            '@touchend.window'() {
                if (this.disabled) {
                    return
                }

                this.__isDragging = false
            },
            '@keydown.left'() {
                if (
                    this.disabled ||
                    !this.rovingFocus ||
                    this.dir !== 'ltr' ||
                    this.orientation === 'vertical'
                ) {
                    return
                }

                const newValue = Math.max(this.min, this.__value - this.step)
                const closestThumbValue =
                    this.__values[
                        this.__getClosestThumbIndex(this.__value, this.__index)
                    ]

                this.__value = closestThumbValue
                    ? Math.abs(newValue - closestThumbValue) <=
                      this.minStepBetweenThumbs
                        ? closestThumbValue - this.minStepBetweenThumbs
                        : newValue
                    : newValue
            },
            '@keydown.right'() {
                if (
                    this.disabled ||
                    !this.rovingFocus ||
                    this.dir !== 'ltr' ||
                    this.orientation === 'vertical'
                ) {
                    return
                }

                const newValue = Math.min(this.max, this.__value + this.step)
                const closestThumbValue =
                    this.__values[
                        this.__getClosestThumbIndex(this.__value, this.__index)
                    ]

                this.__value = closestThumbValue
                    ? Math.abs(newValue - closestThumbValue) <=
                      this.minStepBetweenThumbs
                        ? closestThumbValue + this.minStepBetweenThumbs
                        : newValue
                    : newValue
            },
            '@keydown.up'(event) {
                if (
                    this.disabled ||
                    !this.rovingFocus ||
                    this.dir !== 'ltr' ||
                    this.orientation !== 'vertical'
                ) {
                    return
                }

                event.preventDefault()

                const newValue = Math.min(this.max, this.__value + this.step)
                const closestThumbValue =
                    this.__values[
                        this.__getClosestThumbIndex(this.__value, this.__index)
                    ]

                this.__value = closestThumbValue
                    ? Math.abs(newValue - closestThumbValue) <=
                      this.minStepBetweenThumbs
                        ? closestThumbValue + this.minStepBetweenThumbs
                        : newValue
                    : newValue
            },
            '@keydown.down'(event) {
                if (
                    this.disabled ||
                    !this.rovingFocus ||
                    this.dir !== 'ltr' ||
                    this.orientation !== 'vertical'
                ) {
                    return
                }

                event.preventDefault()

                const newValue = Math.max(this.min, this.__value - this.step)
                const closestThumbValue =
                    this.__values[
                        this.__getClosestThumbIndex(this.__value, this.__index)
                    ]

                this.__value = closestThumbValue
                    ? Math.abs(newValue - closestThumbValue) <=
                      this.minStepBetweenThumbs
                        ? closestThumbValue - this.minStepBetweenThumbs
                        : newValue
                    : newValue
            },
            ':data-thumb-index'() {
                return this.__values.length > 1 ? this.__index : undefined
            },
            'x-data'() {
                return {
                    __index: 0,
                    __value: 0,
                    __isDragging: false,

                    __handleDragging(event) {
                        if (!this.__isDragging) return

                        if (this.orientation === 'horizontal') {
                            this.__handleHorizontalDragging(event)
                        } else {
                            this.__handleVerticalDragging(event)
                        }
                    },

                    __handleHorizontalDragging(event) {
                        if (!this.__isDragging) return

                        const track = this.trackElement.getBoundingClientRect()
                        const minPosition = track.left
                        const maxPosition = track.right
                        const clientX =
                            event.clientX ?? event.touches[0].clientX
                        const mouseOnTrackPosition = Math.min(
                            Math.max(track.left, clientX),
                            track.right,
                        )
                        const percentage =
                            (mouseOnTrackPosition - minPosition) /
                            (maxPosition - minPosition)
                        const value =
                            Math.round(
                                this.min +
                                    (percentage * (this.max - this.min)) /
                                        this.step,
                            ) * this.step

                        if (!this.__acceptsNewValue(value, this.__index)) {
                            return
                        }

                        this.__value = value
                    },

                    __handleVerticalDragging(event) {
                        if (!this.__isDragging) return

                        const track = this.trackElement.getBoundingClientRect()
                        const minPosition = track.top
                        const maxPosition = track.bottom
                        const clientY =
                            event.clientY ?? event.touches[0].clientY
                        const mouseOnTrackPosition = Math.min(
                            Math.max(track.top, clientY),
                            track.bottom,
                        )
                        const percentage =
                            (mouseOnTrackPosition - minPosition) /
                            (maxPosition - minPosition)
                        const value =
                            this.max -
                            Math.round(
                                this.min +
                                    (percentage * (this.max - this.min)) /
                                        this.step,
                            ) *
                                this.step

                        if (!this.__acceptsNewValue(value, this.__index)) {
                            return
                        }

                        this.__value = value
                    },

                    init() {
                        this.$el.removeAttribute('x-slider:thumb')

                        this.$watch('__value', (newValue) => {
                            if (this.disabled) {
                                return
                            }

                            if (this.orientation === 'horizontal') {
                                this.$el.parentElement.style.setProperty(
                                    'left',
                                    `${newValue}%`,
                                )
                                this.$el.parentElement.style.setProperty(
                                    'transform',
                                    'var(--ewa-slider-thumb-transform-x)',
                                )
                            } else {
                                this.$el.parentElement.style.setProperty(
                                    'left',
                                    '50%',
                                )
                                this.$el.parentElement.style.setProperty(
                                    'bottom',
                                    `${newValue}%`,
                                )
                                this.$el.parentElement.style.setProperty(
                                    'transform',
                                    'var(--ewa-slider-thumb-transform)',
                                )
                            }

                            this.__onValueChange(newValue, this.__index)
                        })

                        this.$watch('__values', (newValues) => {
                            const newThumbValue =
                                newValues[this.__index] ??
                                this.defaultValue[this.__index] ??
                                this.min

                            if (newThumbValue !== this.__value) {
                                this.__value = newThumbValue
                            }
                        })

                        this.__index = Array.from(
                            this.$el
                                .closest('[data-slot="slider"]')
                                .querySelectorAll('[data-slot="slider-thumb"]'),
                        ).findIndex((thumb) => thumb === this.$el)

                        if (this.__index === -1) {
                            console.warn(
                                'Slider thumb not found in the slider.',
                            )
                            return
                        }

                        if (
                            typeof this.__values[this.__index] === 'undefined'
                        ) {
                            this.__values[this.__index] =
                                this.defaultValue[this.__index] ?? this.min
                        }

                        this.__value = this.__values[this.__index] ?? this.min
                    },
                }
            },
            'x-modelable': '__value',
        }))
    }

    Alpine.directive(
        'slider',
        (el, { value, expression }, { Alpine, evaluate }) => {
            const params = expression ? evaluate(expression) : {}

            if (!value) handleRoot(el, Alpine, params)
            else if (value === 'track') handleTrack(el, Alpine, params)
            else if (value === 'range') handleRange(el, Alpine, params)
            else if (value === 'thumb') handleThumb(el, Alpine, params)
            else {
                console.warn(`Unknown slider directive value: ${value}`)
            }
        },
    ).before('bind')
}
