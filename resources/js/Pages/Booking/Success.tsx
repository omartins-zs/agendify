import { router } from '@inertiajs/react';

import ThemeToggle from '@/Components/ThemeToggle';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';

type BookingSuccessProps = {
    company: {
        name: string;
        slug: string;
    };
    appointment: {
        id: number;
        status: string;
        status_label: string;
        service: string | null;
        client: string | null;
        starts_at_label: string;
        token: string;
    };
    can_cancel: boolean;
};

export default function BookingSuccess({ company, appointment, can_cancel }: BookingSuccessProps) {
    return (
        <div className="min-h-screen bg-gradient-to-br from-cyan-100 via-slate-50 to-teal-100 px-4 py-10 dark:from-slate-950 dark:via-slate-900 dark:to-cyan-950">
            <div className="mx-auto mb-4 flex max-w-xl justify-end">
                <ThemeToggle className="border border-slate-300 bg-white/80 dark:border-slate-700 dark:bg-slate-900/80" />
            </div>
            <div className="mx-auto max-w-xl">
                <Card>
                    <CardHeader>
                        <CardTitle>Agendamento Confirmado</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <p className="text-sm text-slate-600 dark:text-slate-300">{company.name}</p>

                        <div className="rounded-md border border-slate-200 p-4 dark:border-slate-800">
                            <p className="text-sm">
                                <span className="font-semibold">Cliente:</span> {appointment.client ?? '-'}
                            </p>
                            <p className="text-sm">
                                <span className="font-semibold">Servico:</span> {appointment.service ?? '-'}
                            </p>
                            <p className="text-sm">
                                <span className="font-semibold">Horario:</span> {appointment.starts_at_label}
                            </p>
                            <div className="mt-2">
                                <Badge variant={appointment.status === 'confirmado' ? 'success' : 'default'}>
                                    {appointment.status_label}
                                </Badge>
                            </div>
                        </div>

                        {can_cancel ? (
                            <Button
                                variant="destructive"
                                onClick={() =>
                                    router.post(route('booking.cancel', { company: company.slug, appointment: appointment.id }), {
                                        token: appointment.token,
                                    })
                                }
                            >
                                Cancelar Agendamento
                            </Button>
                        ) : (
                            <p className="text-sm text-slate-500 dark:text-slate-400">
                                O cancelamento automatico nao esta disponivel para este horario.
                            </p>
                        )}

                        <a
                            href={route('booking.show', { company: company.slug })}
                            className="inline-block text-sm font-medium text-brand-600 hover:underline dark:text-brand-400"
                        >
                            Fazer novo agendamento
                        </a>
                    </CardContent>
                </Card>
            </div>
        </div>
    );
}
