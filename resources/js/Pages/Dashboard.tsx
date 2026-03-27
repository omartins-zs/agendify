import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { ColumnDef, flexRender, getCoreRowModel, useReactTable } from '@tanstack/react-table';
import { useMemo } from 'react';

import { Badge } from '@/Components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';

type UpcomingAppointment = {
    id: number;
    starts_at: string;
    starts_at_label: string;
    status: string;
    service: string | null;
    client: string | null;
    phone: string | null;
};

type DashboardProps = {
    company: {
        name: string;
        slug: string;
    };
    cards: {
        todayAppointments: number;
        upcomingAppointments: number;
        cancelledThisMonth: number;
        noShowThisMonth: number;
        attendanceRate: number;
        clients: number;
    };
    upcomingAppointments: UpcomingAppointment[];
};

export default function Dashboard({ company, cards, upcomingAppointments }: DashboardProps) {
    const columns = useMemo<ColumnDef<UpcomingAppointment>[]>(
        () => [
            {
                accessorKey: 'starts_at_label',
                header: 'Horario',
            },
            {
                accessorKey: 'service',
                header: 'Servico',
                cell: ({ row }) => row.original.service ?? '-',
            },
            {
                accessorKey: 'client',
                header: 'Cliente',
                cell: ({ row }) => row.original.client ?? '-',
            },
            {
                accessorKey: 'phone',
                header: 'Telefone',
                cell: ({ row }) => row.original.phone ?? '-',
            },
            {
                accessorKey: 'status',
                header: 'Status',
                cell: ({ row }) => (
                    <Badge
                        variant={
                            row.original.status === 'cancelado'
                                ? 'danger'
                                : row.original.status === 'confirmado'
                                  ? 'success'
                                  : 'default'
                        }
                    >
                        {row.original.status}
                    </Badge>
                ),
            },
        ],
        [],
    );

    const table = useReactTable({
        data: upcomingAppointments,
        columns,
        getCoreRowModel: getCoreRowModel(),
    });

    return (
        <AuthenticatedLayout
            header={
                <div>
                    <p className="text-xs font-semibold uppercase tracking-[0.2em] text-brand-600 dark:text-brand-400">
                        Painel Principal
                    </p>
                    <h2 className="text-2xl font-bold text-slate-900 dark:text-slate-100">{company.name}</h2>
                </div>
            }
        >
            <Head title="Dashboard" />

            <div className="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
                <Card>
                    <CardHeader>
                        <CardTitle>Agendamentos Hoje</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-3xl font-bold text-brand-600 dark:text-brand-300">{cards.todayAppointments}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Proximos Agendamentos</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-3xl font-bold text-brand-600 dark:text-brand-300">{cards.upcomingAppointments}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Taxa de Comparecimento</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-3xl font-bold text-teal-600 dark:text-teal-300">{cards.attendanceRate}%</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Cancelamentos no Mes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-3xl font-bold text-amber-600 dark:text-amber-300">{cards.cancelledThisMonth}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Faltas no Mes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-3xl font-bold text-red-600 dark:text-red-300">{cards.noShowThisMonth}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Total de Clientes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <p className="text-3xl font-bold text-slate-700 dark:text-slate-200">{cards.clients}</p>
                    </CardContent>
                </Card>
            </div>

            <Card className="mt-6">
                <CardHeader>
                    <CardTitle>Agenda dos proximos horarios</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            {table.getHeaderGroups().map((headerGroup) => (
                                <TableRow key={headerGroup.id}>
                                    {headerGroup.headers.map((header) => (
                                        <TableHead key={header.id}>
                                            {header.isPlaceholder
                                                ? null
                                                : flexRender(header.column.columnDef.header, header.getContext())}
                                        </TableHead>
                                    ))}
                                </TableRow>
                            ))}
                        </TableHeader>
                        <TableBody>
                            {table.getRowModel().rows.length ? (
                                table.getRowModel().rows.map((row) => (
                                    <TableRow key={row.id}>
                                        {row.getVisibleCells().map((cell) => (
                                            <TableCell key={cell.id}>
                                                {flexRender(cell.column.columnDef.cell, cell.getContext())}
                                            </TableCell>
                                        ))}
                                    </TableRow>
                                ))
                            ) : (
                                <TableRow>
                                    <TableCell colSpan={columns.length} className="text-center text-slate-500">
                                        Sem agendamentos proximos.
                                    </TableCell>
                                </TableRow>
                            )}
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </AuthenticatedLayout>
    );
}
