import {reactive} from 'vue'

class ThemeManager {
    private static readonly lightTheme = 'light'
    private static readonly darkTheme = 'dark'
    private static readonly localStorageThemeKey = 'theme'
    private static readonly themeLinkElementId = 'theme-link'

    private currentTheme = ThemeManager.lightTheme

    public loadTheme() {
        const theme = localStorage.getItem(ThemeManager.localStorageThemeKey)

        if (theme != null && theme !== this.currentTheme) {
            this.changeTheme(theme)
        }
    }

    public toggleTheme() {
        const newTheme = this.currentTheme === ThemeManager.lightTheme ? ThemeManager.darkTheme : ThemeManager.lightTheme

        this.changeTheme(newTheme)
        localStorage.setItem(ThemeManager.localStorageThemeKey, newTheme)
    }

    public isLight() {
        return this.currentTheme === ThemeManager.lightTheme
    }

    private changeTheme(newTheme: string) {

        if (this.currentTheme !== newTheme) {
            const linkElement = document.getElementById(ThemeManager.themeLinkElementId)

            if (linkElement === null) {
                return
            }

            const newThemeUrl = linkElement.getAttribute('href')!.replace(this.currentTheme, newTheme)

            const cloneLinkElement = document.createElement('link')
            cloneLinkElement.setAttribute('id', ThemeManager.themeLinkElementId + '-clone')
            cloneLinkElement.setAttribute('rel', 'stylesheet')
            cloneLinkElement.setAttribute('href', newThemeUrl)
            cloneLinkElement.addEventListener('load', () => {
                linkElement?.remove()
                cloneLinkElement.setAttribute('id', ThemeManager.themeLinkElementId);
                this.currentTheme = newTheme
            })

            linkElement.parentNode?.insertBefore(cloneLinkElement, linkElement.nextSibling)
        }
    }
}

const themeManager = reactive(new ThemeManager())

export default function useThemeManager() {
    return themeManager
}
