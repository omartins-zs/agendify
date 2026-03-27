export type ThemeMode = 'light' | 'dark';

export const THEME_STORAGE_KEY = 'agendify-theme';

export function getInitialTheme(): ThemeMode {
    if (typeof window === 'undefined') {
        return 'light';
    }

    const storedTheme = localStorage.getItem(THEME_STORAGE_KEY);

    if (storedTheme === 'dark' || storedTheme === 'light') {
        return storedTheme;
    }

    return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
}

export function applyTheme(theme: ThemeMode): void {
    if (typeof document === 'undefined') {
        return;
    }

    const isDark = theme === 'dark';

    document.documentElement.classList.toggle('dark', isDark);
    document.body.classList.toggle('dark', isDark);
    document.documentElement.style.colorScheme = isDark ? 'dark' : 'light';
}

export function persistTheme(theme: ThemeMode): void {
    if (typeof window === 'undefined') {
        return;
    }

    localStorage.setItem(THEME_STORAGE_KEY, theme);
}

export function resolveAndApplyTheme(): ThemeMode {
    const theme = getInitialTheme();

    applyTheme(theme);

    return theme;
}
