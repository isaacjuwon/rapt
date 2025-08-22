export const RegisterToggleThemeStore = ({
    storeName = 'ewaThemeStore',
    localeStorageKey = 'ewa::theme',
    defaultTheme = 'light',
    rememberFavorite = true,
} = {}) => {
    Alpine.store(storeName, {
        theme: defaultTheme,
        rememberFavorite,

        toggleTheme() {
            this.theme = this.theme === 'light' ? 'dark' : 'light'

            if (this.rememberFavorite) {
                localStorage.setItem(localeStorageKey, this.theme)
            }

            this.__applyTheme()
        },

        __applyTheme() {
            if (this.theme === 'light') {
                document.documentElement.classList.remove('dark')
            } else {
                document.documentElement.classList.add('dark')
            }

            document.documentElement.style.setProperty(
                'color-scheme',
                this.theme,
            )
        },

        init() {
            if (!this.rememberFavorite) {
                return
            }

            this.theme = localStorage.getItem(localeStorageKey) || defaultTheme
            this.__applyTheme()
        },
    })

    document.addEventListener('livewire:navigated', (e) => {
        if (Alpine.store(storeName).theme === 'dark') {
            e.target?.documentElement?.classList.add('dark')
            e.target?.documentElement?.style.setProperty('color-scheme', 'dark')
        }
    })
}
