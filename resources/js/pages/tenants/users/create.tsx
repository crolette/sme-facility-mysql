import SearchableInput from '@/components/SearchableInput';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Provider, User } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { FormEventHandler, useState } from 'react';

interface FormDataUser {
    first_name: string;
    last_name: string;
    email: string;
    job_position: string;
    can_login: boolean;
    avatar: File | string | null;
    provider_id: string | number;
    provider_name: string;
    role: string;
}

export default function UserCreateUpdate({ user, roles }: { user?: User; roles: [] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create/Update providers`,
            href: `/providers`,
        },
    ];
    console.log(user);
    console.log(roles);

    const { data, setData, reset } = useForm<FormDataUser>({
        first_name: user?.first_name ?? '',
        last_name: user?.last_name ?? '',
        email: user?.email ?? '',
        can_login: user?.can_login ?? false,
        avatar: '',
        job_position: user?.job_position ?? '',
        provider_id: user?.provier_id ?? '',
        provider_name: user?.provider?.name ?? '',
        role: user?.roles?.length > 0 ? user?.roles[0].name : '',
    });

    const [password, setPassword] = useState();

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        if (user) {
            try {
                const response = await axios.patch(route('api.users.update', user.id), data);
                if (response.data.status === 'success') {
                    router.visit(route('tenant.users.show', user.id), {
                        preserveScroll: false,
                    });
                }
            } catch (error) {
                console.log(error);
            }
        } else {
            try {
                const response = await axios.post(route('api.users.store'), data, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                if (response.data.status === 'success' && response.data.data.password) {
                    setPassword(response.data.data.password);
                }
                reset();
            } catch (error) {
                console.log(error);
            }
        }
    };
    console.log(data);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sites" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {password && (
                    <div className="bg-red-100">
                        <p>USER CREATED:</p>
                        <p>PASSWORD: {password}</p>
                    </div>
                )}
                <form onSubmit={submit}>
                    <Label>First name</Label>
                    <Input type="text" onChange={(e) => setData('first_name', e.target.value)} value={data.first_name} />
                    <Label>Last name</Label>
                    <Input type="text" onChange={(e) => setData('last_name', e.target.value)} value={data.last_name} />
                    <Label>Job position</Label>
                    <Input type="text" onChange={(e) => setData('job_position', e.target.value)} value={data.job_position} />
                    <Label>Email</Label>
                    <Input type="email" onChange={(e) => setData('email', e.target.value)} value={data.email} />
                    <Label>Can login</Label>
                    <Input
                        type="checkbox"
                        onChange={(e) => setData('can_login', e.target.checked ? true : false)}
                        checked={data.can_login ?? false}
                    />
                    {!user && (
                        <>
                            <Label>Avatar</Label>
                            <Input
                                type="file"
                                name="logo"
                                id="logo"
                                onChange={(e) => setData('avatar', e.target.files ? e.target.files[0] : null)}
                                accept="image/png, image/jpeg, image/jpg"
                            />
                            <p className="text-xs">Accepted files: png, jpg - Maximum file size: 4MB</p>
                        </>
                    )}
                    {data.can_login && (
                        <select name="role" id="role" value={data.role} onChange={(e) => setData('role', e.target.value)}>
                            <option value="">Select a role</option>
                            {roles.map((role, index) => (
                                <option key={index} value={role}>
                                    {role}
                                </option>
                            ))}
                        </select>
                    )}
                    <div>
                        <label className="mb-2 block text-sm font-medium">Providers</label>

                        <SearchableInput<Provider>
                            searchUrl={route('api.providers.search')}
                            displayValue={data.provider_name}
                            getDisplayText={(provider) => provider.name}
                            getKey={(provider) => provider.id}
                            onSelect={(provider) => {
                                setData('provider_id', provider.id);
                                setData('provider_name', provider.name);
                            }}
                            placeholder="Search provider"
                            className="mb-4"
                        />
                    </div>

                    <Button>Submit</Button>
                </form>
            </div>
        </AppLayout>
    );
}
