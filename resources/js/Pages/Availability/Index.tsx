import { router, useForm } from '@inertiajs/react';
import { FormEvent, useState } from 'react';

import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

type Rule = {
    id: number;
    weekday: number;
    start_time: string;
    end_time: string;
    slot_interval_minutes: number;
    is_active: boolean;
};

type AvailabilityPageProps = {
    rules: Rule[];
};

const weekdays = [
    { value: 0, label: 'Domingo' },
    { value: 1, label: 'Segunda' },
    { value: 2, label: 'Terca' },
    { value: 3, label: 'Quarta' },
    { value: 4, label: 'Quinta' },
    { value: 5, label: 'Sexta' },
    { value: 6, label: 'Sabado' },
];

export default function AvailabilityIndex({ rules }: AvailabilityPageProps) {
    const [editingId, setEditingId] = useState<number | null>(null);

    const form = useForm({
        weekday: '1',
        start_time: '09:00',
        end_time: '18:00',
        slot_interval_minutes: '15',
        is_active: true,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();

        if (editingId) {
            form.put(route('availability-rules.update', editingId), {
                preserveScroll: true,
                onSuccess: () => {
                    setEditingId(null);
                    form.reset();
                    form.setData('weekday', '1');
                    form.setData('start_time', '09:00');
                    form.setData('end_time', '18:00');
                    form.setData('slot_interval_minutes', '15');
                    form.setData('is_active', true);
                },
            });

            return;
        }

        form.post(route('availability-rules.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                form.setData('weekday', '1');
                form.setData('start_time', '09:00');
                form.setData('end_time', '18:00');
                form.setData('slot_interval_minutes', '15');
                form.setData('is_active', true);
            },
        });
    };

    const startEdit = (rule: Rule) => {
        setEditingId(rule.id);
        form.setData({
            weekday: String(rule.weekday),
            start_time: rule.start_time,
            end_time: rule.end_time,
            slot_interval_minutes: String(rule.slot_interval_minutes),
            is_active: rule.is_active,
        });
    };

    return (
        <AuthenticatedLayout header={<h2 className="text-2xl font-bold">Horarios de Funcionamento</h2>}>
            <div className="grid gap-6 lg:grid-cols-[360px_1fr]">
                <Card>
                    <CardHeader>
                        <CardTitle>{editingId ? 'Editar Regra' : 'Nova Regra'}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form className="space-y-4" onSubmit={submit}>
                            <div>
                                <Label>Dia da Semana</Label>
                                <select
                                    value={form.data.weekday}
                                    onChange={(event) => form.setData('weekday', event.target.value)}
                                    className="mt-1 block h-10 w-full rounded-md border border-slate-300 bg-white px-3 text-sm dark:border-slate-700 dark:bg-slate-900"
                                >
                                    {weekdays.map((weekday) => (
                                        <option key={weekday.value} value={weekday.value}>
                                            {weekday.label}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div>
                                <Label htmlFor="start_time">Inicio</Label>
                                <Input
                                    id="start_time"
                                    type="time"
                                    value={form.data.start_time}
                                    onChange={(event) => form.setData('start_time', event.target.value)}
                                    required
                                />
                            </div>

                            <div>
                                <Label htmlFor="end_time">Fim</Label>
                                <Input
                                    id="end_time"
                                    type="time"
                                    value={form.data.end_time}
                                    onChange={(event) => form.setData('end_time', event.target.value)}
                                    required
                                />
                            </div>

                            <div>
                                <Label htmlFor="slot_interval_minutes">Intervalo entre slots (min)</Label>
                                <Input
                                    id="slot_interval_minutes"
                                    type="number"
                                    min={5}
                                    value={form.data.slot_interval_minutes}
                                    onChange={(event) => form.setData('slot_interval_minutes', event.target.value)}
                                    required
                                />
                            </div>

                            <label className="flex items-center gap-2 text-sm">
                                <input
                                    type="checkbox"
                                    checked={form.data.is_active}
                                    onChange={(event) => form.setData('is_active', event.target.checked)}
                                />
                                Regra ativa
                            </label>

                            <div className="flex gap-2">
                                <Button type="submit">{editingId ? 'Salvar' : 'Cadastrar'}</Button>
                                {editingId && (
                                    <Button type="button" variant="outline" onClick={() => setEditingId(null)}>
                                        Cancelar
                                    </Button>
                                )}
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Regras Cadastradas</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Dia</TableHead>
                                    <TableHead>Inicio</TableHead>
                                    <TableHead>Fim</TableHead>
                                    <TableHead>Intervalo</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead className="text-right">Acoes</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {rules.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={6} className="text-center text-slate-500">
                                            Nenhuma regra cadastrada.
                                        </TableCell>
                                    </TableRow>
                                )}
                                {rules.map((rule) => (
                                    <TableRow key={rule.id}>
                                        <TableCell>{weekdays.find((day) => day.value === rule.weekday)?.label}</TableCell>
                                        <TableCell>{rule.start_time}</TableCell>
                                        <TableCell>{rule.end_time}</TableCell>
                                        <TableCell>{rule.slot_interval_minutes} min</TableCell>
                                        <TableCell>{rule.is_active ? 'Ativa' : 'Inativa'}</TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                <Button variant="outline" size="sm" onClick={() => startEdit(rule)}>
                                                    Editar
                                                </Button>
                                                <Button
                                                    variant="destructive"
                                                    size="sm"
                                                    onClick={() =>
                                                        router.delete(route('availability-rules.destroy', rule.id), {
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
