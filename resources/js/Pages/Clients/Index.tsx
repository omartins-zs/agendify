import { router, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

type Client = {
    id: number;
    name: string;
    phone: string;
    email: string | null;
    notes: string | null;
    appointments_count: number;
    last_visit_at: string | null;
};

type ClientsPageProps = {
    clients: Client[];
};

export default function ClientsIndex({ clients }: ClientsPageProps) {
    const [editingId, setEditingId] = useState<number | null>(null);

    const form = useForm({
        name: '',
        phone: '',
        email: '',
        notes: '',
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (editingId) {
            form.put(route('clients.update', editingId), {
                preserveScroll: true,
                onSuccess: () => {
                    setEditingId(null);
                    form.reset();
                },
            });

            return;
        }

        form.post(route('clients.store'), {
            preserveScroll: true,
            onSuccess: () => form.reset(),
        });
    };

    const startEdit = (client: Client) => {
        setEditingId(client.id);
        form.setData({
            name: client.name,
            phone: client.phone,
            email: client.email ?? '',
            notes: client.notes ?? '',
        });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-2xl font-bold">Clientes</h2>}>
            <div className="grid gap-6 lg:grid-cols-[360px_1fr]">
                <Card>
                    <CardHeader>
                        <CardTitle>{editingId ? 'Editar Cliente' : 'Novo Cliente'}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form className="space-y-4" onSubmit={submit}>
                            <div>
                                <Label htmlFor="name">Nome</Label>
                                <Input
                                    id="name"
                                    value={form.data.name}
                                    onChange={(event) => form.setData('name', event.target.value)}
                                    required
                                />
                            </div>

                            <div>
                                <Label htmlFor="phone">Telefone</Label>
                                <Input
                                    id="phone"
                                    value={form.data.phone}
                                    onChange={(event) => form.setData('phone', event.target.value)}
                                    required
                                />
                            </div>

                            <div>
                                <Label htmlFor="email">Email</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    value={form.data.email}
                                    onChange={(event) => form.setData('email', event.target.value)}
                                />
                            </div>

                            <div>
                                <Label htmlFor="notes">Observacoes</Label>
                                <textarea
                                    id="notes"
                                    value={form.data.notes}
                                    onChange={(event) => form.setData('notes', event.target.value)}
                                    className="min-h-[100px] w-full rounded-md border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-900"
                                />
                            </div>

                            <div className="flex gap-2">
                                <Button type="submit">{editingId ? 'Salvar' : 'Cadastrar'}</Button>
                                {editingId && (
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => {
                                            setEditingId(null);
                                            form.reset();
                                        }}
                                    >
                                        Cancelar
                                    </Button>
                                )}
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Base de Clientes</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Nome</TableHead>
                                    <TableHead>Telefone</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>Historico</TableHead>
                                    <TableHead className="text-right">Acoes</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {clients.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={5} className="text-center text-slate-500">
                                            Nenhum cliente cadastrado.
                                        </TableCell>
                                    </TableRow>
                                )}
                                {clients.map((client) => (
                                    <TableRow key={client.id}>
                                        <TableCell className="font-medium">{client.name}</TableCell>
                                        <TableCell>{client.phone}</TableCell>
                                        <TableCell>{client.email ?? '-'}</TableCell>
                                        <TableCell>{client.appointments_count} atendimentos</TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                <Button size="sm" variant="outline" onClick={() => startEdit(client)}>
                                                    Editar
                                                </Button>
                                                <Button
                                                    size="sm"
                                                    variant="destructive"
                                                    onClick={() =>
                                                        router.delete(route('clients.destroy', client.id), {
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
