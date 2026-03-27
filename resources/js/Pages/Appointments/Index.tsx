import axios from 'axios';
import { router, useForm } from '@inertiajs/react';
import { FormEvent, useEffect, useState } from 'react';

import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

type Appointment = {
    id: number;
    service: string | null;
    client: string | null;
    phone: string | null;
    starts_at: string;
    starts_at_label: string;
    status: string;
    status_label: string;
    notes: string | null;
};

type Service = {
    id: number;
    name: string;
    duration_minutes: number;
};

type Client = {
    id: number;
    name: string;
    phone: string;
    email: string | null;
};

type Status = {
    value: string;
    label: string;
};

type Slot = {
    value: string;
    label: string;
};

type AppointmentsPageProps = {
    appointments: Appointment[];
    services: Service[];
    clients: Client[];
    statuses: Status[];
    filters: {
        status?: string;
        date?: string;
    };
};

export default function AppointmentsIndex({ appointments, services, clients, statuses, filters }: AppointmentsPageProps) {
    const [slots, setSlots] = useState<Slot[]>([]);
    const [loadingSlots, setLoadingSlots] = useState(false);
    const [activeClientMode, setActiveClientMode] = useState<'existing' | 'new'>('existing');

    const [statusFilter, setStatusFilter] = useState(filters.status ?? '');
    const [dateFilter, setDateFilter] = useState(filters.date ?? '');

    const form = useForm({
        service_id: '',
        client_id: '',
        client_name: '',
        client_phone: '',
        client_email: '',
        date: '',
        starts_at: '',
        notes: '',
    });

    useEffect(() => {
        const serviceId = form.data.service_id;
        const date = form.data.date;

        if (!serviceId || !date) {
            setSlots([]);
            form.setData('starts_at', '');
            return;
        }

        setLoadingSlots(true);

        axios
            .get(route('appointments.slots'), {
                params: {
                    service_id: serviceId,
                    date,
                },
            })
            .then((response) => {
                setSlots(response.data.slots ?? []);
                if (!response.data.slots?.some((slot: Slot) => slot.value === form.data.starts_at)) {
                    form.setData('starts_at', '');
                }
            })
            .finally(() => setLoadingSlots(false));
    }, [form.data.service_id, form.data.date]);

    const submitAppointment = (event: FormEvent) => {
        event.preventDefault();

        form.post(route('appointments.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                setSlots([]);
                setActiveClientMode('existing');
            },
        });
    };

    const applyFilters = () => {
        router.get(
            route('appointments.index'),
            {
                status: statusFilter || undefined,
                date: dateFilter || undefined,
            },
            {
                preserveState: true,
                replace: true,
            },
        );
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-2xl font-bold">Agenda</h2>}>
            <div className="grid gap-6 xl:grid-cols-[380px_1fr]">
                <Card>
                    <CardHeader>
                        <CardTitle>Novo Agendamento</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form className="space-y-4" onSubmit={submitAppointment}>
                            <div>
                                <Label>Servico</Label>
                                <select
                                    value={form.data.service_id}
                                    onChange={(event) => form.setData('service_id', event.target.value)}
                                    className="mt-1 block h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900"
                                    required
                                >
                                    <option value="">Selecione</option>
                                    {services.map((service) => (
                                        <option key={service.id} value={service.id}>
                                            {service.name} ({service.duration_minutes} min)
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div>
                                    <Label>Data</Label>
                                    <Input
                                        type="date"
                                        className="mt-1"
                                        value={form.data.date}
                                        onChange={(event) => form.setData('date', event.target.value)}
                                        required
                                    />
                                </div>

                                <div>
                                    <Label>Horario</Label>
                                    <select
                                        value={form.data.starts_at}
                                        onChange={(event) => form.setData('starts_at', event.target.value)}
                                        className="mt-1 block h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900"
                                        required
                                        disabled={loadingSlots || slots.length === 0}
                                    >
                                        <option value="">{loadingSlots ? 'Carregando...' : 'Selecione'}</option>
                                        {slots.map((slot) => (
                                            <option key={slot.value} value={slot.value}>
                                                {slot.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div className="rounded-md border border-slate-200 p-3 dark:border-slate-800">
                                <p className="mb-2 text-xs font-semibold uppercase tracking-wide text-slate-500">Cliente</p>
                                <div className="mb-3 flex gap-2">
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={activeClientMode === 'existing' ? 'default' : 'outline'}
                                        onClick={() => setActiveClientMode('existing')}
                                    >
                                        Existente
                                    </Button>
                                    <Button
                                        type="button"
                                        size="sm"
                                        variant={activeClientMode === 'new' ? 'default' : 'outline'}
                                        onClick={() => setActiveClientMode('new')}
                                    >
                                        Novo
                                    </Button>
                                </div>

                                {activeClientMode === 'existing' ? (
                                    <select
                                        value={form.data.client_id}
                                        onChange={(event) => form.setData('client_id', event.target.value)}
                                        className="block h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900"
                                    >
                                        <option value="">Selecionar cliente</option>
                                        {clients.map((client) => (
                                            <option key={client.id} value={client.id}>
                                                {client.name} - {client.phone}
                                            </option>
                                        ))}
                                    </select>
                                ) : (
                                    <div className="space-y-3">
                                        <Input
                                            placeholder="Nome do cliente"
                                            value={form.data.client_name}
                                            onChange={(event) => form.setData('client_name', event.target.value)}
                                            required
                                        />
                                        <Input
                                            placeholder="Telefone"
                                            value={form.data.client_phone}
                                            onChange={(event) => form.setData('client_phone', event.target.value)}
                                            required
                                        />
                                        <Input
                                            placeholder="Email (opcional)"
                                            type="email"
                                            value={form.data.client_email}
                                            onChange={(event) => form.setData('client_email', event.target.value)}
                                        />
                                    </div>
                                )}
                            </div>

                            <div>
                                <Label>Observacoes</Label>
                                <textarea
                                    value={form.data.notes}
                                    onChange={(event) => form.setData('notes', event.target.value)}
                                    className="min-h-[90px] w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                                />
                            </div>

                            <Button type="submit" disabled={form.processing} className="w-full">
                                Criar Agendamento
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Agenda da Empresa</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div className="mb-4 grid gap-3 sm:grid-cols-3">
                            <select
                                value={statusFilter}
                                onChange={(event) => setStatusFilter(event.target.value)}
                                className="h-10 rounded-md border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900"
                            >
                                <option value="">Todos os status</option>
                                {statuses.map((status) => (
                                    <option key={status.value} value={status.value}>
                                        {status.label}
                                    </option>
                                ))}
                            </select>

                            <Input type="date" value={dateFilter} onChange={(event) => setDateFilter(event.target.value)} />

                            <Button type="button" variant="outline" onClick={applyFilters}>
                                Aplicar Filtros
                            </Button>
                        </div>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Horario</TableHead>
                                    <TableHead>Servico</TableHead>
                                    <TableHead>Cliente</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">Acoes</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {appointments.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={5} className="text-center text-slate-500">
                                            Nenhum agendamento encontrado.
                                        </TableCell>
                                    </TableRow>
                                )}

                                {appointments.map((appointment) => (
                                    <TableRow key={appointment.id}>
                                        <TableCell>{appointment.starts_at_label}</TableCell>
                                        <TableCell>{appointment.service ?? '-'}</TableCell>
                                        <TableCell>
                                            <p className="font-medium">{appointment.client ?? '-'}</p>
                                            <p className="text-xs text-slate-500">{appointment.phone ?? ''}</p>
                                        </TableCell>
                                        <TableCell>
                                            <Badge
                                                variant={
                                                    appointment.status === 'cancelado'
                                                        ? 'danger'
                                                        : appointment.status === 'confirmado'
                                                          ? 'success'
                                                          : appointment.status === 'faltou'
                                                            ? 'warning'
                                                            : 'default'
                                                }
                                            >
                                                {appointment.status_label}
                                            </Badge>
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                <select
                                                    defaultValue={appointment.status}
                                                    onChange={(event) =>
                                                        router.put(
                                                            route('appointments.update', appointment.id),
                                                            { status: event.target.value },
                                                            { preserveScroll: true, preserveState: true },
                                                        )
                                                    }
                                                    className="h-9 rounded-md border border-slate-300 bg-white px-2 text-xs dark:border-slate-700 dark:bg-slate-900"
                                                >
                                                    {statuses.map((status) => (
                                                        <option key={status.value} value={status.value}>
                                                            {status.label}
                                                        </option>
                                                    ))}
                                                </select>
                                                <Button
                                                    variant="destructive"
                                                    size="sm"
                                                    onClick={() =>
                                                        router.delete(route('appointments.destroy', appointment.id), {
                                                            preserveScroll: true,
                                                            preserveState: true,
                                                        })
                                                    }
                                                >
                                                    Cancelar
                                                </Button>
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </AuthenticatedLayout>
    );
}
