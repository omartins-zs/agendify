import { Link, usePage } from '@inertiajs/react';
import { PropsWithChildren, ReactNode, useMemo } from 'react';

import ThemeToggle from '@/Components/ThemeToggle';
import { PageProps } from '@/types';

const navItems = [
    { label: 'Dashboard', route: 'dashboard' },
    { label: 'Servicos', route: 'services.index' },
    { label: 'Horarios', route: 'availability-rules.index' },
    { label: 'Clientes', route: 'clients.index' },
    { label: 'Agenda', route: 'appointments.index' },
];

export default function AuthenticatedLayout({
    header,
    children,
}: PropsWithChildren<{ header?: ReactNode }>) {
    const page = usePage<PageProps>();
    const user = page.props.auth.user;
    const company = user?.company;

    const publicBookingLink = useMemo(() => {
        if (!company) {
            return null;
        }

        return route('booking.show', { company: company.slug });
    }, [company]);

    return (
        <div className="min-h-screen">
            <nav className="border-b border-slate-200 bg-white/90 backdrop-blur dark:border-slate-800 dark:bg-slate-900/90">
                <div className="mx-auto flex max-w-7xl flex-col gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <div className="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.18em] text-brand-600 dark:text-brand-400">
                                Agendify
                            </p>
                            <p className="text-sm text-slate-600 dark:text-slate-300">
                                {company?.name ?? 'Empresa'}
                            </p>
                        </div>

                        <div className="flex items-center gap-2">
                            {publicBookingLink && (
                                <a
                                    href={publicBookingLink}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="rounded-md border border-slate-300 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 transition hover:border-brand-400 hover:text-brand-600 dark:border-slate-700 dark:text-slate-200 dark:hover:text-brand-300"
                                >
                                    Link Publico
                                </a>
                            )}
                            <ThemeToggle />
                            <Link
                                href={route('logout')}
                                method="post"
                                as="button"
                                className="rounded-md bg-slate-200 px-3 py-2 text-xs font-semibold uppercase tracking-wide text-slate-700 transition hover:bg-slate-300 dark:bg-slate-800 dark:text-slate-200 dark:hover:bg-slate-700"
                            >
                                Sair
                            </Link>
                        </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-2">
                        {navItems.map((item) => (
                            <Link
                                key={item.route}
                                href={route(item.route)}
                                className={`rounded-md px-3 py-2 text-sm font-medium transition ${route().current(item.route)
                                    ? 'bg-brand-500 text-white'
                                    : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white'
                                    }`}
                            >
                                {item.label}
                            </Link>
                        ))}
                    </div>
                </div>
            </nav>

            {(page.props.flash.success || page.props.flash.error) && (
                <div className="mx-auto max-w-7xl px-4 pt-4 sm:px-6 lg:px-8">
                    {page.props.flash.success && (
                        <div className="rounded-md border border-teal-200 bg-teal-50 px-4 py-3 text-sm text-teal-700 dark:border-teal-900 dark:bg-teal-950/50 dark:text-teal-300">
                            {page.props.flash.success}
                        </div>
                    )}
                    {page.props.flash.error && (
                        <div className="rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700 dark:border-red-900 dark:bg-red-950/50 dark:text-red-300">
                            {page.props.flash.error}
                        </div>
                    )}
                </div>
            )}

            {header && (
                <header className="mx-auto mt-6 w-full max-w-7xl px-4 sm:px-6 lg:px-8">
                    {header}
                </header>
            )}

            <main className="mx-auto w-full max-w-7xl px-4 py-6 sm:px-6 lg:px-8">{children}</main>
        </div>
    );
}
