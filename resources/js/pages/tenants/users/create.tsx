import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Provider, User } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { FormEventHandler, useEffect, useState } from 'react';

export default function UserCreateUpdate({ user }: { user?: User }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create/Update providers`,
            href: `/providers`,
        },
    ];

    const { data, setData, reset } = useForm({
        first_name: user?.first_name ?? '',
        last_name: user?.last_name ?? '',
        email: user?.email ?? '',
        can_login: user?.can_login ?? false,
        avatar: '',
        provider_id: user?.provider_id ?? '',
        provider_name: '',
    });

    const [providers, setProviders] = useState<Provider[] | null>(null);

    const fetchProviders = async () => {
        try {
            const response = await axios.get(route('api.providers.search', { q: search }));
            setProviders(response.data.data);
            setIsSearching(false);
            setListIsOpen(true);
        } catch (error) {
            console.log(error);
        }
    };

    const [search, setSearch] = useState<string>('');
    const [debouncedSearch, setDebouncedSearch] = useState(search);
    const [listIsOpen, setListIsOpen] = useState(false);
    const [isSearching, setIsSearching] = useState(false);

    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedSearch(search);
        }, 500);

        return () => {
            clearTimeout(handler);
        };
    }, [search]);

    useEffect(() => {
        if (debouncedSearch.length < 2) {
            setProviders([]);
        }
        if (debouncedSearch.length >= 2) {
            setIsSearching(true);
            setListIsOpen(true);
            if (debouncedSearch) {
                fetchProviders();
            }
        }
    }, [debouncedSearch]);

    const [password, setPassword] = useState();
    console.log(providers);

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        if (user) {
            try {
                const response = await axios.patch(route('api.users.update', user.id), data);
                if (response.data.status === 'success') {
                    window.location.href = route('tenant.users.show', user.id);
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
                if (response.data.status === 'success') {
                    console.log(response.data.data, response.data.data.password);
                    setPassword(response.data.data.password);
                    reset();
                    // window.location.href = user ? route('tenant.users.show', user.id) : route('tenant.providers.index');
                }
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
                    <Label>Email</Label>
                    <Input type="email" onChange={(e) => setData('email', e.target.value)} value={data.email} />
                    <Label>Can login</Label>
                    <Input type="checkbox" onChange={(e) => setData('can_login', e.target.checked)} checked={data.can_login ?? false} />
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
                    <Label>Provider</Label>
                    <Input type="text" onChange={(e) => setSearch(e.target.value)} value={search.length > 0 ? search : data.provider_name} />
                    {providers && providers.length > 0 && (
                        <ul className="bg-background absolute z-10 flex w-full flex-col border" aria-autocomplete="list" role="listbox">
                            {isSearching && (
                                <li value="0" key="" className="">
                                    Searching...
                                </li>
                            )}
                            {listIsOpen &&
                                providers &&
                                providers.length > 0 &&
                                providers?.map((provider) => (
                                    <li
                                        role="option"
                                        value={provider.id}
                                        key={provider.id}
                                        onClick={() => {
                                            setData('provider_name', provider.name);
                                            setData('provider_id', provider.id);
                                            setSearch('');
                                            setListIsOpen(false);
                                            setProviders([]);
                                        }}
                                        // onClick={() => setSelectedLocation(location)}
                                        className="hover:bg-foreground hover:text-background cursor-pointer p-2 text-sm"
                                    >
                                        {provider.name}
                                    </li>
                                ))}
                        </ul>
                    )}

                    <Button>Submit</Button>
                </form>
            </div>
        </AppLayout>
    );
}
