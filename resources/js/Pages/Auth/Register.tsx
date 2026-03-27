import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        company_name: '',
        company_slug: '',
        company_phone: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();

        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title="Criar Conta" />

            <form onSubmit={submit} className="space-y-4">
                <div>
                    <InputLabel htmlFor="company_name" value="Nome da Empresa" />

                    <TextInput
                        id="company_name"
                        name="company_name"
                        value={data.company_name}
                        className="mt-1 block w-full"
                        autoComplete="organization"
                        isFocused={true}
                        onChange={(e) => {
                            const name = e.target.value;
                            setData('company_name', name);

                            if (!data.company_slug) {
                                setData(
                                    'company_slug',
                                    name
                                        .normalize('NFD')
                                        .replace(/[^\w\s-]/g, '')
                                        .trim()
                                        .toLowerCase()
                                        .replace(/\s+/g, '-'),
                                );
                            }
                        }}
                        required
                    />

                    <InputError message={errors.company_name} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="company_slug" value="Slug Publico" />

                    <TextInput
                        id="company_slug"
                        name="company_slug"
                        value={data.company_slug}
                        className="mt-1 block w-full"
                        onChange={(e) => setData('company_slug', e.target.value)}
                        required
                    />

                    <p className="mt-1 text-xs text-slate-500">Ex: barbearia-do-joao</p>
                    <InputError message={errors.company_slug} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="company_phone" value="Telefone da Empresa" />

                    <TextInput
                        id="company_phone"
                        name="company_phone"
                        value={data.company_phone}
                        className="mt-1 block w-full"
                        onChange={(e) => setData('company_phone', e.target.value)}
                    />

                    <InputError message={errors.company_phone} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="name" value="Seu Nome" />

                    <TextInput
                        id="name"
                        name="name"
                        value={data.name}
                        className="mt-1 block w-full"
                        autoComplete="name"
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />

                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="email" value="Email" />

                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        onChange={(e) => setData('email', e.target.value)}
                        required
                    />

                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="password" value="Senha" />

                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) => setData('password', e.target.value)}
                        required
                    />

                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div>
                    <InputLabel htmlFor="password_confirmation" value="Confirmar Senha" />

                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        required
                    />

                    <InputError message={errors.password_confirmation} className="mt-2" />
                </div>

                <div className="flex items-center justify-between pt-2">
                    <Link
                        href={route('login')}
                        className="rounded-md text-sm text-slate-600 underline hover:text-slate-900 focus:outline-none focus:ring-2 focus:ring-brand-500"
                    >
                        Ja tenho conta
                    </Link>

                    <PrimaryButton className="ms-4" disabled={processing}>
                        Criar Conta
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
