import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { BreadcrumbItem, CentralType, Provider } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { FormEventHandler } from 'react';

type TypeFormData = {
    name: string;
    phone_number: string;
    email: string;
    website: string;
    vat_number: string;
    address: string;
    categoryId: number | string;
    logo: File | null;
};

export default function ProviderCreateUpdate({ provider, providerCategories }: { provider?: Provider; providerCategories: CentralType[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create/Update providers`,
            href: `/providers`,
        },
    ];

    console.log(provider);
    const { data, setData } = useForm<TypeFormData>({
        name: provider?.name ?? '',
        phone_number: provider?.phone_number ?? '',
        email: provider?.email ?? '',
        website: provider?.website ?? '',
        vat_number: provider?.vat_number ?? '',
        address: provider?.address ?? '',
        categoryId: provider?.category_type_id ?? '',
        logo: null,
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
                    <Input type="text" onChange={(e) => setData('name', e.target.value)} value={data.name} required />
                    <Label htmlFor="name">Category</Label>
                    <select
                        name="level"
                        required
                        value={data.categoryId === '' ? 0 : data.categoryId}
                        onChange={(e) => setData('categoryId', e.target.value)}
                        id=""
                        className={cn(
                            'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                            'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                            'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                        )}
                    >
                        {providerCategories && providerCategories.length > 0 && (
                            <>
                                <option value="0" disabled className="bg-background text-foreground">
                                    Select an option
                                </option>
                                {providerCategories?.map((category) => (
                                    <option value={category.id} key={category.id} className="bg-background text-foreground">
                                        {category.label}
                                    </option>
                                ))}
                            </>
                        )}
                    </select>
                    <Label>Email</Label>
                    <Input type="email" onChange={(e) => setData('email', e.target.value)} value={data.email} required />
                    <Label>Website</Label>
                    <Input type="text" onChange={(e) => setData('website', e.target.value)} value={data.website} />
                    <Label>Address</Label>
                    <Input type="text" onChange={(e) => setData('address', e.target.value)} value={data.address} />
                    <Label>VAT</Label>
                    <Input type="text" onChange={(e) => setData('vat_number', e.target.value)} value={data.vat_number} />
                    <Label>Phone</Label>
                    <Input type="text" onChange={(e) => setData('phone_number', e.target.value)} value={data.phone_number} required />
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
