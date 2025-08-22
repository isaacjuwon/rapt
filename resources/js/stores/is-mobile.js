export const RegisterIsMobileStore = ({
    storeName = 'ewaMobileChecker',
    mobileBreakdown = 768,
} = {}) => {
    const mql = window.matchMedia(`(max-width: ${mobileBreakdown - 1}px)`)

    const onChange = () => {
        Alpine.store(storeName).isMobile = window.innerWidth < mobileBreakdown
    }

    Alpine.store(storeName, {
        isMobile: undefined,
        mql,

        init() {
            mql.addEventListener('change', onChange)
            this.isMobile = window.innerWidth < mobileBreakdown
        },

        destroy() {
            mql.removeEventListener('change', onChange)
        },
    })
}
