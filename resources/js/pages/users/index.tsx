import Heading from '@/components/heading';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler } from 'react';

interface ManagedUser {
    id: number;
    name: string;
    email: string;
    is_admin: boolean;
    created_at: string;
}

interface UsersIndexProps {
    users: ManagedUser[];
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Utilisateurs',
        href: '/users',
    },
];

export default function UsersIndex({ users }: UsersIndexProps) {
    const { errors: pageErrors } = usePage().props as { errors?: { user?: string } };

    const { data, setData, post, processing, errors, reset, recentlySuccessful } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (event) => {
        event.preventDefault();

        post(route('users.store'), {
            onSuccess: () => reset('name', 'email', 'password', 'password_confirmation'),
        });
    };

    const destroyUser = (user: ManagedUser) => {
        if (!window.confirm(`Supprimer « ${user.email} » et tous ses tickets ?`)) {
            return;
        }

        router.delete(route('users.destroy', user.id), {
            preserveScroll: true,
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Utilisateurs" />
            <div className="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
                <Heading
                    title="Utilisateurs"
                    description="Seul le compte admin peut créer des utilisateurs. Chaque utilisateur ne voit que ses propres tickets et tâches."
                />

                {pageErrors?.user ? <p className="text-destructive text-sm">{pageErrors.user}</p> : null}

                <form onSubmit={submit} className="border-sidebar-border/70 dark:border-sidebar-border max-w-xl space-y-4 rounded-xl border p-4">
                    <h2 className="text-sm font-semibold">Créer un utilisateur</h2>

                    <div className="grid gap-2">
                        <Label htmlFor="name">Nom</Label>
                        <Input id="name" value={data.name} onChange={(event) => setData('name', event.target.value)} required />
                        <InputError message={errors.name} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="email">Email</Label>
                        <Input
                            id="email"
                            type="email"
                            value={data.email}
                            onChange={(event) => setData('email', event.target.value)}
                            required
                        />
                        <InputError message={errors.email} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password">Mot de passe</Label>
                        <Input
                            id="password"
                            type="password"
                            value={data.password}
                            onChange={(event) => setData('password', event.target.value)}
                            required
                        />
                        <InputError message={errors.password} />
                    </div>

                    <div className="grid gap-2">
                        <Label htmlFor="password_confirmation">Confirmation</Label>
                        <Input
                            id="password_confirmation"
                            type="password"
                            value={data.password_confirmation}
                            onChange={(event) => setData('password_confirmation', event.target.value)}
                            required
                        />
                    </div>

                    <div className="flex items-center gap-3">
                        <Button type="submit" disabled={processing}>
                            Créer
                        </Button>
                        {recentlySuccessful ? <span className="text-muted-foreground text-sm">Utilisateur créé.</span> : null}
                    </div>
                </form>

                <div className="border-sidebar-border/70 dark:border-sidebar-border overflow-hidden rounded-xl border">
                    <table className="w-full text-left text-sm">
                        <thead className="bg-muted/50 text-muted-foreground">
                            <tr>
                                <th className="px-4 py-3 font-medium">Nom</th>
                                <th className="px-4 py-3 font-medium">Email</th>
                                <th className="px-4 py-3 font-medium">Rôle</th>
                                <th className="px-4 py-3 font-medium" />
                            </tr>
                        </thead>
                        <tbody>
                            {users.map((user) => (
                                <tr key={user.id} className="border-t">
                                    <td className="px-4 py-3">{user.name}</td>
                                    <td className="px-4 py-3">{user.email}</td>
                                    <td className="px-4 py-3">{user.is_admin ? 'Admin' : 'Utilisateur'}</td>
                                    <td className="px-4 py-3 text-right">
                                        {user.is_admin ? (
                                            <span className="text-muted-foreground text-xs">Protégé</span>
                                        ) : (
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                className="text-destructive hover:text-destructive"
                                                onClick={() => destroyUser(user)}
                                            >
                                                Supprimer
                                            </Button>
                                        )}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>
        </AppLayout>
    );
}
