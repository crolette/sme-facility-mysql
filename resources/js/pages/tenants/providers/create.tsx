import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Provider } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { FormEventHandler } from 'react';

export default function ProviderCreateUpdate({ provider }: { provider?: Provider }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create/Update providers`,
            href: `/providers`,
        },
    ];

    console.log(provider);
    const { data, setData } = useForm({
        name: provider?.name ?? '',
        phone_number: provider?.phone_number ?? '',
        email: provider?.email ?? '',
        vat_number: provider?.vat_number ?? '',
        address: provider?.address ?? '',
    });

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        if (provider) {
            try {
                const response = await axios.patch(route('api.providers.update', provider.id), data);
                if (response.data.status === 'success') {
                    window.location.href = route('tenant.providers.show', provider.id);
                }
            } catch (error) {
                console.log(error);
            }
        } else {
            try {
                const response = await axios.post(route('api.providers.store'), data, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                if (response.data.status === 'success') {
                    window.location.href = provider ? route('tenant.providers.show', provider.id) : route('tenant.providers.index');
                }
            } catch (error) {
                console.log(error);
            }
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sites" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <form onSubmit={submit}>
                    <Label>Company Name</Label>
                    <Input type="text" onChange={(e) => setData('name', e.target.value)} value={data.name} />
                    <Label>Email</Label>
                    <Input type="email" onChange={(e) => setData('email', e.target.value)} value={data.email} />
                    <Label>Address</Label>
                    <Input type="address" onChange={(e) => setData('address', e.target.value)} value={data.address} />
                    <Label>VAT</Label>
                    <Input type="vat_number" onChange={(e) => setData('vat_number', e.target.value)} value={data.vat_number} />
                    <Label>Phone</Label>
                    <Input type="phone_number" onChange={(e) => setData('phone_number', e.target.value)} value={data.phone_number} />
                    {!provider && (
                        <>
                            <Label>Logo</Label>
                            <Input
                                type="file"
                                name="logo"
                                id="logo"
                                onChange={(e) => setData('logo', e.target.files ? e.target.files[0] : null)}
                                accept="image/png, image/jpeg, image/jpg"
                            />
                            <p className="text-xs">Accepted files: png, jpg - Maximum file size: 4MB</p>
                        </>
                    )}

                    <Button>Submit</Button>
                </form>
            </div>
        </AppLayout>
    );
}
