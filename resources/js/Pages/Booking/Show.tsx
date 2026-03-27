import axios from 'axios';
import { router } from '@inertiajs/react';
import { zodResolver } from '@hookform/resolvers/zod';
import { CalendarClock } from 'lucide-react';
import { useEffect, useState } from 'react';
import { useForm } from 'react-hook-form';
import { z } from 'zod';

import ThemeToggle from '@/Components/ThemeToggle';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';

type Slot = {
    value: string;
    label: string;
};

type BookingPageProps = {
    company: {
        id: number;
        name: string;
        slug: string;
        phone: string | null;
        timezone: string;
    };
    services: Array<{
        id: number;
        name: string;
        duration_minutes: number;
        price: string | null;
    }>;
    settings: {
        allow_client_cancellation: boolean;
        cancellation_window_hours: number;
    };
};

const bookingSchema = z.object({
    service_id: z
        .string()
        .min(1, 'Selecione um servico')
        .refine((value) => value !== '0', 'Selecione um servico'),
    date: z.string().min(1, 'Selecione uma data'),
    starts_at: z.string().min(1, 'Selecione um horario'),
    client_name: z.string().min(2, 'Informe seu nome'),
    client_phone: z.string().min(8, 'Informe um telefone valido'),
    client_email: z
        .string()
        .trim()
        .optional()
        .or(z.literal(''))
        .refine((value) => !value || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value), {
            message: 'Informe um email valido',
        }),
    notes: z.string().optional(),
});

type BookingFormValues = z.infer<typeof bookingSchema>;

export default function BookingShow({ company, services, settings }: BookingPageProps) {
    const [slots, setSlots] = useState<Slot[]>([]);
    const [loadingSlots, setLoadingSlots] = useState(false);

    const {
        register,
        watch,
        handleSubmit,
        setValue,
        setError,
        formState: { errors, isSubmitting },
    } = useForm<BookingFormValues>({
        resolver: zodResolver(bookingSchema),
        defaultValues: {
            service_id: '',
            date: '',
            starts_at: '',
            client_name: '',
            client_phone: '',
            client_email: '',
            notes: '',
        },
    });

    const selectedService = watch('service_id');
    const selectedDate = watch('date');
    const selectedSlot = watch('starts_at');

    useEffect(() => {
        if (!selectedService || !selectedDate) {
            setSlots([]);
            setValue('starts_at', '');
            return;
        }

        setLoadingSlots(true);

        axios
            .get(route('booking.slots', { company: company.slug }), {
                params: {
                    service_id: selectedService,
                    date: selectedDate,
                },
            })
            .then((response) => {
                const incomingSlots = response.data.slots ?? [];
                setSlots(incomingSlots);

                if (!incomingSlots.some((slot: Slot) => slot.value === selectedSlot)) {
                    setValue('starts_at', '');
                }
            })
            .finally(() => setLoadingSlots(false));
    }, [selectedService, selectedDate]);

    const submit = (data: BookingFormValues) => {
        router.post(route('booking.store', { company: company.slug }), data, {
            onError: (serverErrors) => {
                Object.entries(serverErrors).forEach(([key, value]) => {
                    setError(key as keyof BookingFormValues, {
                        type: 'server',
                        message: String(value),
                    });
                });
            },
        });
    };

    return (
        <div className="min-h-screen bg-gradient-to-br from-cyan-100 via-slate-50 to-teal-100 px-4 py-10 dark:from-slate-950 dark:via-slate-900 dark:to-cyan-950">
            <div className="mx-auto mb-4 flex max-w-3xl justify-end">
                <ThemeToggle className="border border-slate-300 bg-white/80 dark:border-slate-700 dark:bg-slate-900/80" />
            </div>
            <div className="mx-auto max-w-3xl">
                <div className="mb-8 text-center">
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-600 dark:text-brand-400">Agendify</p>
                    <h1 className="mt-2 text-3xl font-bold text-slate-900 dark:text-slate-100">{company.name}</h1>
                    <p className="mt-2 text-sm text-slate-600 dark:text-slate-300">
                        Escolha o servico, horario e finalize seu agendamento em poucos cliques.
                    </p>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>Novo Agendamento</CardTitle>
                        <CardDescription>Fluxo rapido e sem necessidade de aplicativo.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form className="space-y-5" onSubmit={handleSubmit(submit)}>
                            <div>
                                <Label>Servico</Label>
                                <select
                                    {...register('service_id')}
                                    className="mt-1 block h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900"
                                >
                                    <option value="">Selecione um servico</option>
                                    {services.map((service) => (
                                        <option key={service.id} value={service.id}>
                                            {service.name} ({service.duration_minutes} min)
                                        </option>
                                    ))}
                                </select>
                                {errors.service_id?.message && (
                                    <p className="mt-1 text-xs text-red-600">{errors.service_id.message}</p>
                                )}
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label>Data</Label>
                                    <Input type="date" className="mt-1" {...register('date')} />
                                    {errors.date?.message && <p className="mt-1 text-xs text-red-600">{errors.date.message}</p>}
                                </div>
                                <div>
                                    <Label>Horario</Label>
                                    <div className="mt-1 grid grid-cols-3 gap-2 rounded-md border border-slate-200 p-2 dark:border-slate-800">
                                        {loadingSlots && (
                                            <p className="col-span-3 text-sm text-slate-500">Carregando horarios...</p>
                                        )}
                                        {!loadingSlots && slots.length === 0 && (
                                            <p className="col-span-3 text-sm text-slate-500">Selecione servico e data para ver horarios.</p>
                                        )}
                                        {slots.map((slot) => (
                                            <button
                                                key={slot.value}
                                                type="button"
                                                onClick={() => setValue('starts_at', slot.value, { shouldValidate: true })}
                                                className={`rounded-md border px-2 py-2 text-sm transition ${selectedSlot === slot.value
                                                    ? 'border-brand-500 bg-brand-500 text-white'
                                                    : 'border-slate-300 bg-white text-slate-700 hover:border-brand-400 hover:text-brand-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200'
                                                    }`}
                                            >
                                                {slot.label}
                                            </button>
                                        ))}
                                    </div>
                                    {errors.starts_at?.message && (
                                        <p className="mt-1 text-xs text-red-600">{errors.starts_at.message}</p>
                                    )}
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label>Nome</Label>
                                    <Input placeholder="Seu nome" {...register('client_name')} />
                                    {errors.client_name?.message && (
                                        <p className="mt-1 text-xs text-red-600">{errors.client_name.message}</p>
                                    )}
                                </div>
                                <div>
                                    <Label>Telefone</Label>
                                    <Input placeholder="(11) 99999-9999" {...register('client_phone')} />
                                    {errors.client_phone?.message && (
                                        <p className="mt-1 text-xs text-red-600">{errors.client_phone.message}</p>
                                    )}
                                </div>
                            </div>

                            <div>
                                <Label>Email (opcional)</Label>
                                <Input type="email" placeholder="voce@email.com" {...register('client_email')} />
                                {errors.client_email?.message && (
                                    <p className="mt-1 text-xs text-red-600">{errors.client_email.message}</p>
                                )}
                            </div>

                            <div>
                                <Label>Observacoes (opcional)</Label>
                                <textarea
                                    {...register('notes')}
                                    className="min-h-[90px] w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                                />
                            </div>

                            <Button type="submit" className="w-full" disabled={isSubmitting}>
                                <CalendarClock className="mr-2 h-4 w-4" />
                                Confirmar Agendamento
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                {settings.allow_client_cancellation && (
                    <p className="mt-4 text-center text-xs text-slate-500 dark:text-slate-400">
                        Cancelamento permitido ate {settings.cancellation_window_hours}h antes do horario.
                    </p>
                )}
            </div>
        </div>
    );
}
