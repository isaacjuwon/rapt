import {
    computePosition,
    autoUpdate,
    flip,
    offset,
    shift,
    arrow,
    size,
} from '@floating-ui/dom'

export default function (Alpine) {
    const getHiddenElementWidth = (el) => {
        const clone = el.cloneNode(true)
        clone.style.visibility = 'hidden'
        clone.style.position = 'absolute'
        clone.style.display = 'block'
        document.body.appendChild(clone)
        const width = clone.getBoundingClientRect().width
        document.body.removeChild(clone)
        return width
    }

    const setStyles = (el, x, y) => {
        Object.assign(el.style, {
            left: x + 'px',
            top: y + 'px',
            position: 'absolute',
        })
    }

    Alpine.magic('anchorplus', (el) => {
        if (!el._x_anchor)
            throw 'Alpine: No x-anchor directive found on element using $anchor...'

        return el._x_anchor
    })

    Alpine.interceptClone((from, to) => {
        if (from && from._x_anchor && !to._x_anchor) {
            to._x_anchor = from._x_anchor
        }
    })

    Alpine.directive(
        'anchorplus',
        Alpine.skipDuringClone(
            (el, { expression }, { cleanup, evaluate }) => {
                el._x_anchor = Alpine.reactive({ x: 0, y: 0 })

                const options = evaluate(expression)

                const {
                    reference,
                    placement,
                    sideOffset,
                    noStyle: unstyled,
                    calculateSize = undefined,
                    arrowEl = undefined,
                } = options

                if (!reference)
                    throw 'Alpine: no element provided to x-anchor...'

                const compute = () => {
                    let previousValue

                    const offsetValue = arrowEl
                        ? sideOffset + arrowEl.clientHeight
                        : sideOffset
                    const arrowWidth = arrowEl
                        ? getHiddenElementWidth(arrowEl)
                        : 0

                    const middleware = [
                        flip(),
                        size({
                            padding: sideOffset,
                            apply: ({ availableHeight, elements }) => {
                                if (!calculateSize) {
                                    return
                                }

                                const space =
                                    sideOffset +
                                        calculateSize['additionalSpace'] || 0

                                const varName =
                                    calculateSize['varName'] ||
                                    '--anchor-height'
                                elements.floating?.style.setProperty(
                                    varName,
                                    `${Math.max(availableHeight, Math.floor(window.innerHeight * 0.3)) - space}px`,
                                )
                            },
                        }),
                        shift({ padding: 5 }),
                        offset(offsetValue),
                    ]

                    if (arrowEl) {
                        middleware.push(
                            arrow({
                                element: arrowEl,
                            }),
                        )
                    }

                    computePosition(reference, el, {
                        placement,
                        middleware,
                    }).then(({ x, y, middlewareData, placement }) => {
                        unstyled || setStyles(el, x, y)

                        // Only trigger Alpine reactivity when the value actually changes...
                        if (JSON.stringify({ x, y }) !== previousValue) {
                            el._x_anchor.x = x
                            el._x_anchor.y = y

                            const side = placement.split('-')[0]
                            const alignment = placement.split('-')[1]

                            const staticSide = {
                                top: 'bottom',
                                right: 'left',
                                bottom: 'top',
                                left: 'right',
                            }[side]

                            const alignmentAdjustment = {
                                start: -arrowWidth / 2,
                                end: arrowWidth / 2,
                                center: 0,
                            }[alignment ?? 'center']

                            if (middlewareData.arrow) {
                                const { x, y } = middlewareData.arrow

                                Object.assign(arrowEl.style, {
                                    left:
                                        x != null
                                            ? `${x + alignmentAdjustment}px`
                                            : '',
                                    top:
                                        y != null
                                            ? `${y + alignmentAdjustment}px`
                                            : '',
                                    // Ensure the static side gets unset when
                                    // flipping to other placements' axes.
                                    right: '',
                                    bottom: '',
                                    [staticSide]: `${-arrowWidth / 4}px`,
                                })
                            }
                        }

                        previousValue = JSON.stringify({ x, y })
                    })
                }

                const release = autoUpdate(reference, el, () => compute())

                cleanup(() => release())
            },

            // When cloning (or "morphing"), we will graft the style and position data from the live tree...
            (el, { expression }, { evaluate }) => {
                const options = evaluate(expression)
                const { noStyle: unstyled } = options

                if (el._x_anchor) {
                    unstyled || setStyles(el, el._x_anchor.x, el._x_anchor.y)
                }
            },
        ),
    )
}
