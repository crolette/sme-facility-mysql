import InputError from '@/components/input-error';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { BreadcrumbItem, CentralType, Provider } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { MinusCircleIcon, PlusCircleIcon } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

type ContactPerson = {
    first_name: string;
    last_name: string;
    email: string;
    phone_number: string;
    job_position: string;
};

type TypeFormData = {
    name: string;
    phone_number: string;
    email: string;
    website: string;
    vat_number: string;
    address: string;
    categoryId: number | string;
    pictures: File[] | null;
    users: ContactPerson[];
};

export default function CreateUpdateProvider({ provider, providerCategories }: { provider?: Provider; providerCategories: CentralType[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create/Update providers`,
            href: `/providers`,
        },
    ];

    const { showToast } = useToast();
    const [errors, setErrors] = useState<TypeFormData>();
    const { data, setData } = useForm<TypeFormData>({
        name: provider?.name ?? '',
        phone_number: provider?.phone_number ?? '',
        email: provider?.email ?? '',
        website: provider?.website ?? '',
        vat_number: provider?.vat_number ?? '',
        address: provider?.address ?? '',
        categoryId: provider?.category_type_id ?? '',
        pictures: [],
        users: [],
    });

    console.log(data);

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        if (provider) {
            try {
                const response = await axios.patch(route('api.providers.update', provider.id), data);
                if (response.data.status === 'success') {
                    router.visit(route('tenant.providers.show', provider.id), {
                        preserveScroll: false,
                    });
                }
            } catch (error) {
                showToast(error.response.data.message, error.response.data.status);
                setErrors(error.response.data.errors);
            }
        } else {
            try {
                const response = await axios.post(route('api.providers.store'), data, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                if (response.data.status === 'success') {
                    router.visit(route('tenant.providers.index'), {
                        preserveScroll: false,
                    });
                }
            } catch (error) {
                console.log(error);
                showToast(error.response.data.message, error.response.data.status);
                setErrors(error.response.data.errors);
            }
        }
    };

    const [countContactPersons, setCountContactPersons] = useState(0);
    const handleRemoveContactPerson = (index: number) => {
        setData((prev) => {
            const updatedContactPersons = prev.users.filter((_, i) => i !== index);

            return { ...prev, users: updatedContactPersons };
        });

        setCountContactPersons((prev) => prev - 1);
    };

    const handleChangeContactPerson = (index: number, field: keyof ContactPerson, value: any) => {
        setData((prev) => {
            const updatedContactPersons = [...prev.users];
            updatedContactPersons[index] = {
                ...updatedContactPersons[index],
                [field]: value,
            };
            return { ...prev, users: updatedContactPersons };
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sites" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <form onSubmit={submit} className="space-y-4">
                    <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                        <h2>Company information</h2>
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
                        <InputError className="mt-2" message={errors?.email ?? ''} />
                        <Label>Website</Label>
                        <Input type="text" onChange={(e) => setData('website', e.target.value)} value={data.website} />
                        <InputError className="mt-2" message={errors?.website ?? ''} />
                        <Label>Address</Label>
                        <Input type="text" onChange={(e) => setData('address', e.target.value)} value={data.address} />
                        <InputError className="mt-2" message={errors?.address ?? ''} />
                        <Label>VAT</Label>
                        <Input type="text" onChange={(e) => setData('vat_number', e.target.value)} value={data.vat_number} />
                        <InputError className="mt-2" message={errors?.vat_number ?? ''} />
                        <Label>Phone number</Label>
                        <Input
                            type="text"
                            onChange={(e) => setData('phone_number', e.target.value)}
                            value={data.phone_number}
                            required
                            maxLength={16}
                            placeholder={`Phone number : +32123456789`}
                        />
                        <InputError className="mt-2" message={errors?.phone_number ?? ''} />
                        {!provider && (
                            <>
                                <Label>Logo</Label>
                                <Input
                                    type="file"
                                    name="logo"
                                    id="logo"
                                    onChange={(e) => setData('pictures', e.target.files ? [e.target.files[0]] : null)}
                                    // accept="image/png, image/jpeg, image/jpg"
                                />
                                <p className="text-xs">Accepted files: png, jpg - Maximum file size: 4MB</p>
                                {errors?.pictures &&
                                    errors?.pictures.map((error) => {
                                        <InputError className="mt-2" message={error ?? ''} />;
                                    })}
                            </>
                        )}
                    </div>
                    <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                        <h2>Contact persons</h2>
                        <p className="">
                            Add new contact person{' '}
                            <PlusCircleIcon className="inline-block" onClick={() => setCountContactPersons((prev) => prev + 1)} />
                        </p>
                        <div className="space-y-4">
                            {countContactPersons > 0 &&
                                [...Array(countContactPersons)].map((_, index) => (
                                    <details key={index} className="flex flex-col rounded-md border-2 border-slate-400 p-4" open>
                                        <summary>
                                            <div className="flex w-fit gap-2">
                                                <p>
                                                    Contact {index + 1} {data.users[index]?.first_name ? `- ${data.users[index]?.first_name}` : ''}
                                                    {data.users[index]?.last_name ? ` ${data.users[index]?.last_name}` : ''}
                                                </p>
                                                <MinusCircleIcon onClick={() => handleRemoveContactPerson(index)} />
                                            </div>
                                        </summary>
                                        <div>
                                            <div className="flex w-full flex-col gap-2 lg:flex-row">
                                                <div className="w-full">
                                                    <Label className="font-medium" htmlFor={data.users[index]?.first_name}>
                                                        First name
                                                    </Label>
                                                    <Input
                                                        id={data.users[index]?.first_name}
                                                        type="text"
                                                        value={data.users[index]?.first_name ?? ''}
                                                        placeholder={`First name ${index + 1}`}
                                                        minLength={4}
                                                        maxLength={100}
                                                        required
                                                        onChange={(e) => handleChangeContactPerson(index, 'first_name', e.target.value)}
                                                    />
                                                    <InputError className="mt-2" message={errors?.users ? errors?.users[index]?.first_name : ''} />
                                                </div>
                                                <div className="w-full">
                                                    <Label className="font-medium" htmlFor={data.users[index]?.last_name}>
                                                        Last name
                                                    </Label>
                                                    <Input
                                                        id={data.users[index]?.last_name}
                                                        type="text"
                                                        value={data.users[index]?.last_name ?? ''}
                                                        placeholder={`Last name ${index + 1}`}
                                                        minLength={4}
                                                        maxLength={100}
                                                        required
                                                        onChange={(e) => handleChangeContactPerson(index, 'last_name', e.target.value)}
                                                    />
                                                    <InputError className="mt-2" message={errors?.users ? errors?.users[index]?.last_name : ''} />
                                                </div>
                                            </div>
                                            <div className="flex w-full flex-col gap-2 lg:flex-row">
                                                <div className="w-full">
                                                    <Label className="font-medium" htmlFor={data.users[index]?.email}>
                                                        Email
                                                    </Label>
                                                    <Input
                                                        id={data.users[index]?.email}
                                                        type="text"
                                                        value={data.users[index]?.email ?? ''}
                                                        placeholder={`Email ${index + 1}`}
                                                        minLength={4}
                                                        maxLength={100}
                                                        required
                                                        onChange={(e) => handleChangeContactPerson(index, 'email', e.target.value)}
                                                    />
                                                    <InputError className="mt-2" message={errors?.users ? errors?.users[index]?.email : ''} />
                                                </div>
                                                <div className="w-full">
                                                    <Label className="font-medium" htmlFor={data.users[index]?.phone_number}>
                                                        Phone number
                                                    </Label>
                                                    <Input
                                                        id={data.users[index]?.phone_number}
                                                        type="text"
                                                        value={data.users[index]?.phone_number ?? ''}
                                                        placeholder={`Phone number ${index + 1} : +32123456789`}
                                                        maxLength={16}
                                                        onChange={(e) => handleChangeContactPerson(index, 'phone_number', e.target.value)}
                                                    />
                                                    <InputError className="mt-2" message={errors?.users ? errors?.users[index]?.phone_number : ''} />
                                                </div>
                                            </div>

                                            <Label className="font-medium" htmlFor={data.users[index]?.job_position}>
                                                Job position
                                            </Label>
                                            <Input
                                                id={data.users[index]?.job_position}
                                                type="text"
                                                value={data.users[index]?.job_position ?? ''}
                                                placeholder={`Job position ${index + 1}`}
                                                minLength={4}
                                                maxLength={100}
                                                onChange={(e) => handleChangeContactPerson(index, 'job_position', e.target.value)}
                                            />
                                            <InputError className="mt-2" message={errors?.users ? errors?.users[index]?.job_position : ''} />
                                        </div>
                                    </details>
                                ))}
                        </div>
                    </div>

                    <div className="flex gap-4">
                        <Button type="submit">{provider ? 'Update' : 'Submit'}</Button>
                        <a href={provider ? route('tenant.providers.show', provider.id) : route('tenant.providers.index')}>
                            <Button type="button" tabIndex={6} variant={'secondary'}>
                                Cancel
                            </Button>
                        </a>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
