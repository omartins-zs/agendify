import { Head, Link } from '@inertiajs/react';

import ThemeToggle from '@/Components/ThemeToggle';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

export default function Welcome() {
    return (
        <>
            <Head title="Agendify" />

            <div className="relative min-h-screen overflow-hidden">
                <div className="absolute inset-0 -z-10 bg-gradient-to-br from-cyan-100 via-slate-50 to-teal-100 dark:from-slate-950 dark:via-slate-900 dark:to-cyan-950" />
                <div className="mx-auto flex min-h-screen max-w-6xl flex-col px-6 py-10">
                    <header className="flex items-center justify-between py-2">
                        <div>
                            <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-600 dark:text-brand-300">
                                Agendify
                            </p>
                            <h1 className="text-xl font-bold text-slate-900 dark:text-slate-100">Agendamento Inteligente</h1>
                        </div>

                        <div className="flex items-center gap-2">
                            <ThemeToggle />
                            <Link href={route('login')}>
                                <Button variant="ghost">Entrar</Button>
                            </Link>
                            <Link href={route('register')}>
                                <Button>Comecar Gratis</Button>
                            </Link>
                        </div>
                    </header>

                    <main className="grid flex-1 items-center gap-6 py-10 md:grid-cols-2">
                        <div className="space-y-6">
                            <h2 className="text-4xl font-extrabold leading-tight text-slate-900 dark:text-slate-100">
                                Pare de perder clientes por falta de organizacao na agenda
                            </h2>
                            <p className="text-lg text-slate-600 dark:text-slate-300">
                                Organize horarios, confirme atendimentos automaticamente e reduza faltas sem depender de planilhas ou caos no WhatsApp.
                            </p>
                            <div className="flex flex-wrap gap-3">
                                <Link href={route('register')}>
                                    <Button size="lg">Criar Minha Conta</Button>
                                </Link>
                                <Link href={route('login')}>
                                    <Button size="lg" variant="outline">
                                        Ver Dashboard
                                    </Button>
                                </Link>
                            </div>
                        </div>

                        <div className="grid gap-4">
                            <Card>
                                <CardHeader>
                                    <CardTitle>Fluxo em 4 passos</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-2 text-sm text-slate-600 dark:text-slate-300">
                                    <p>1. Escolha o servico</p>
                                    <p>2. Selecione data e horario</p>
                                    <p>3. Preencha nome e telefone</p>
                                    <p>4. Confirme em segundos</p>
                                </CardContent>
                            </Card>

                            <Card>
                                <CardHeader>
                                    <CardTitle>Ideal para negocios locais</CardTitle>
                                </CardHeader>
                                <CardContent className="text-sm text-slate-600 dark:text-slate-300">
                                    Barbearias, saloes, clinicas, estetica, fisioterapia, tatuadores e qualquer empresa que queira operar com agenda profissional.
                                </CardContent>
                            </Card>
                        </div>
                    </main>
                </div>
            </div>
        </>
    );
}
