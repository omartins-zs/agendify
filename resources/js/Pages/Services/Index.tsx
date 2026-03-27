import { router, useForm } from '@inertiajs/react';
import { FormEvent, useMemo, useState } from 'react';

import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

type Service = {
    id: number;
    name: string;
    duration_minutes: number;
    price: string | null;
    is_active: boolean;
};

type ServicesPageProps = {
    services: Service[];
};

export default function ServicesIndex({ services }: ServicesPageProps) {
    const [editingId, setEditingId] = useState<number | null>(null);

    const serviceById = useMemo(
        () => new Map(services.map((service) => [service.id, service])),
        [services],
    );

    const form = useForm({
        name: '',
        duration_minutes: '30',
        price: '',
        is_active: true,
    });

    const submit = (e: FormEvent) => {
        e.preventDefault();

        if (editingId) {
            form.put(route('services.update', editingId), {
                preserveScroll: true,
                onSuccess: () => {
                    setEditingId(null);
                    form.reset();
                    form.setData('is_active', true);
                },
            });

            return;
        }

        form.post(route('services.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                form.setData('duration_minutes', '30');
                form.setData('is_active', true);
            },
        });
    };

    const startEdit = (service: Service) => {
        setEditingId(service.id);
        form.setData({
            name: service.name,
            duration_minutes: String(service.duration_minutes),
            price: service.price ?? '',
            is_active: service.is_active,
        });
    };

    const cancelEdit = () => {
        setEditingId(null);
        form.reset();
        form.setData('duration_minutes', '30');
        form.setData('is_active', true);
    };

    return (
        <AuthenticatedLayout
            header={<h2 className="text-2xl font-bold text-slate-900 dark:text-slate-100">Servicos</h2>}
        >
            <div className="grid gap-6 lg:grid-cols-[360px_1fr]">
                <Card>
                    <CardHeader>
                        <CardTitle>{editingId ? 'Editar Servico' : 'Novo Servico'}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
                            <div>
                                <Label htmlFor="name">Nome</Label>
                                <Input
                                    id="name"
                                    value={form.data.name}
                                    onChange={(event) => form.setData('name', event.target.value)}
                                    required
                                />
                                {form.errors.name && <p className="mt-1 text-xs text-red-600">{form.errors.name}</p>}
                            </div>

                            <div>
                                <Label htmlFor="duration">Duracao (minutos)</Label>
                                <Input
                                    id="duration"
                                    type="number"
                                    min={5}
                                    value={form.data.duration_minutes}
                                    onChange={(event) => form.setData('duration_minutes', event.target.value)}
                                    required
                                />
                                {form.errors.duration_minutes && (
                                    <p className="mt-1 text-xs text-red-600">{form.errors.duration_minutes}</p>
                                )}
                            </div>

                            <div>
                                <Label htmlFor="price">Preco (opcional)</Label>
                                <Input
                                    id="price"
                                    type="number"
                                    min={0}
                                    step="0.01"
                                    value={form.data.price}
                                    onChange={(event) => form.setData('price', event.target.value)}
                                />
                            </div>

                            <label className="flex items-center gap-2 text-sm text-slate-600 dark:text-slate-300">
                                <input
                                    type="checkbox"
                                    checked={form.data.is_active}
                                    onChange={(event) => form.setData('is_active', event.target.checked)}
                                    className="rounded border-slate-300"
                                />
                                Servico ativo
                            </label>

                            <div className="flex flex-wrap gap-2">
                                <Button type="submit" disabled={form.processing}>
                                    {editingId ? 'Salvar Alteracoes' : 'Cadastrar Servico'}
                                </Button>
                                {editingId && (
                                    <Button type="button" variant="outline" onClick={cancelEdit}>
                                        Cancelar
                                    </Button>
                                )}
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Lista de Servicos</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Servico</TableHead>
                                    <TableHead>Duracao</TableHead>
                                    <TableHead>Preco</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">Acoes</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {services.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={5} className="text-center text-slate-500">
                                            Nenhum servico cadastrado.
                                        </TableCell>
                                    </TableRow>
                                )}

                                {services.map((service) => (
                                    <TableRow key={service.id}>
                                        <TableCell className="font-medium">{service.name}</TableCell>
                                        <TableCell>{service.duration_minutes} min</TableCell>
                                        <TableCell>{service.price ? `R$ ${service.price}` : '-'}</TableCell>
                                        <TableCell>{service.is_active ? 'Ativo' : 'Inativo'}</TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                <Button size="sm" variant="outline" onClick={() => startEdit(service)}>
                                                    Editar
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="ghost"
                                                    onClick={() => {
                                                        const selected = serviceById.get(service.id);
                                                        if (!selected) {
                                                            return;
                                                        }

                                                        router.put(
                                                            route('services.update', service.id),
                                                            {
                                                                name: selected.name,
                                                                duration_minutes: selected.duration_minutes,
                                                                price: selected.price,
                                                                is_active: !selected.is_active,
                                                            },
                                                            { preserveScroll: true },
                                                        );
                                                    }}
                                                >
                                                    {service.is_active ? 'Desativar' : 'Ativar'}
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="destructive"
                                                    onClick={() =>
                                                        router.delete(route('services.destroy', service.id), {
                                                            preserveScroll: true,
                                                        })
                                                    }
                                                >
                                                    Remover
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
