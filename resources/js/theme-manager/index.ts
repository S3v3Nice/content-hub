class ThemeManager {
    private static readonly lightTheme = 'light'
    private static readonly darkTheme = 'dark'
    private static readonly localStorageThemeKey = 'theme'

    private currentTheme = ThemeManager.lightTheme

    constructor() {
        this.loadThemeFromLocalStorage()
    }

    public toggleTheme() {
        const newTheme = this.currentTheme === ThemeManager.lightTheme ? ThemeManager.darkTheme : ThemeManager.lightTheme

        this.changeTheme(newTheme)
        localStorage.setItem(ThemeManager.localStorageThemeKey, newTheme)
    }

    public isLight() {
        return this.currentTheme === ThemeManager.lightTheme
    }

    public isDark() {
        return this.currentTheme === ThemeManager.darkTheme
    }

    private loadThemeFromLocalStorage() {
        const theme = localStorage.getItem(ThemeManager.localStorageThemeKey)

        if (theme != null && theme !== this.currentTheme) {
            this.changeTheme(theme)
        }
    }

    private changeTheme(newTheme: string) {
        const themeLinkElementId = 'theme-link'

        if (this.currentTheme !== newTheme) {
            const linkElement = document.getElementById(themeLinkElementId)

            if (linkElement === null) {
                return
            }

            const newThemeUrl = linkElement.getAttribute('href')!.replace(this.currentTheme, newTheme)

            const cloneLinkElement = document.createElement('link')
            cloneLinkElement.setAttribute('id', themeLinkElementId + '-clone')
            cloneLinkElement.setAttribute('rel', 'stylesheet')
            cloneLinkElement.setAttribute('href', newThemeUrl)
            cloneLinkElement.addEventListener('load', () => {
                linkElement?.remove()
                cloneLinkElement.setAttribute('id', themeLinkElementId);
                this.currentTheme = newTheme
            })

            linkElement.parentNode?.insertBefore(cloneLinkElement, linkElement.nextSibling)
        }
    }
}

const themeManager = new ThemeManager()

export default function useThemeManager() {
    return themeManager
}