import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';
import ThemeToggle from '@/Components/ThemeToggle';

export default function GuestLayout({ children }: PropsWithChildren) {
    return (
        <div className="relative flex min-h-screen items-center justify-center px-4 py-8">
            <div className="absolute inset-0 -z-10 bg-gradient-to-br from-cyan-100 via-slate-100 to-teal-100 dark:from-slate-950 dark:via-slate-900 dark:to-cyan-950" />
            <div className="absolute right-4 top-4">
                <ThemeToggle className="border border-slate-300 bg-white/80 dark:border-slate-700 dark:bg-slate-900/80" />
            </div>

            <div className="w-full max-w-md">
                <Link href="/" className="mb-6 block text-center">
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-600 dark:text-brand-400">
                        Agendify
                    </p>
                    <h1 className="text-2xl font-bold text-slate-900 dark:text-slate-100">
                        Agenda inteligente para negocios locais
                    </h1>
                </Link>

                <div className="rounded-xl border border-slate-200 bg-white/95 p-6 shadow-xl backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
                    {children}
                </div>
            </div>
        </div>
    );
}
