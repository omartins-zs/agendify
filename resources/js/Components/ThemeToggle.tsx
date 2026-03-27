import { Moon, Sun } from 'lucide-react';
import { useEffect, useState } from 'react';

import { Button } from '@/Components/ui/button';
import { cn } from '@/lib/utils';
import { applyTheme, getInitialTheme, persistTheme, ThemeMode } from '@/lib/theme';

type ThemeToggleProps = {
    className?: string;
};

export default function ThemeToggle({ className }: ThemeToggleProps) {
    const [theme, setTheme] = useState<ThemeMode>(() => getInitialTheme());

    useEffect(() => {
        applyTheme(theme);
        persistTheme(theme);
    }, [theme]);

    const isDark = theme === 'dark';

    return (
        <Button
            type="button"
            variant="ghost"
            size="icon"
            className={cn(className)}
            onClick={() => setTheme(isDark ? 'light' : 'dark')}
            aria-label={isDark ? 'Ativar tema claro' : 'Ativar tema escuro'}
            title={isDark ? 'Ativar tema claro' : 'Ativar tema escuro'}
        >
            {isDark ? <Sun className="h-4 w-4" /> : <Moon className="h-4 w-4" />}
        </Button>
    );
}
