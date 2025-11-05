import InputError from '@/components/input-error';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { BreadcrumbItem, CentralType, Country, Provider } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { MinusCircleIcon, PlusCircleIcon } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';

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
    street: string;
    house_number?: string;
    postal_code: string;
    city: string;
    country_code: string;
    categoryId: number | string;
    pictures: File[] | null;
    users: ContactPerson[];
};

export default function CreateUpdateProvider({
    provider,
    providerCategories,
    countries,
}: {
    provider?: Provider;
    providerCategories: CentralType[];
    countries: Country[];
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create/Update providers`,
            href: `/providers`,
        },
    ];

    const { showToast } = useToast();
    const [errors, setErrors] = useState<TypeFormData>();
    const [isSubmitting, setIsSubmitting] = useState(false);
    const { data, setData } = useForm<TypeFormData>({
        name: provider?.name ?? '',
        phone_number: provider?.phone_number ?? '',
        email: provider?.email ?? '',
        website: provider?.website ?? '',
        vat_number: provider?.vat_number ?? '',
        street: provider?.street ?? '',
        house_number: provider?.house_number ?? '',
        postal_code: provider?.postal_code ?? '',
        city: provider?.city ?? '',
        country_code: provider?.country.iso_code ?? '',
        categoryId: provider?.category_type_id ?? '',
        pictures: [],
        users: [],
    });

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsSubmitting(true);
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
                setIsSubmitting(false);
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
                setIsSubmitting(false);
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

    const [selectedCountry, setSelectedCountry] = useState(data.country_code ?? '');

    useEffect(() => {
        setData('country_code', selectedCountry);
    }, [selectedCountry]);

    console.log(data);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sites" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <form onSubmit={submit} className="space-y-4">
                    <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                        <h2>Provider information</h2>
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
                        <div className="flex gap-4">
                            <div>
                                <Label>Street</Label>
                                <Input type="text" onChange={(e) => setData('street', e.target.value)} value={data.street} required />
                                <InputError className="mt-2" message={errors?.street ?? ''} />
                            </div>
                            <div>
                                <Label>House nr.</Label>
                                <Input type="text" onChange={(e) => setData('house_number', e.target.value)} value={data.house_number} />
                                <InputError className="mt-2" message={errors?.house_number ?? ''} />
                            </div>
                            <div>
                                <Label>Postal Code</Label>
                                <Input type="text" onChange={(e) => setData('postal_code', e.target.value)} value={data.postal_code} required />
                                <InputError className="mt-2" message={errors?.postal_code ?? ''} />
                            </div>
                            <div>
                                <Label>City</Label>
                                <Input type="text" onChange={(e) => setData('city', e.target.value)} value={data.city} required />
                                <InputError className="mt-2" message={errors?.city ?? ''} />
                            </div>
                            <div>
                                <Label>Country</Label>
                                <Select value={selectedCountry} onValueChange={setSelectedCountry}>
                                    <SelectTrigger className="w-[180px]">
                                        <SelectValue placeholder="Select a country" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {countries.map((country: Country) => (
                                            <SelectItem key={country.iso_code} value={country.iso_code}>
                                                {country.label}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
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
                                <p className="text-xs">Accepted files: png, jpg, jpeg - Maximum file size: 4MB</p>
                                {errors?.pictures &&
                                    errors?.pictures.map((error) => {
                                        <InputError className="mt-2" message={error ?? ''} />;
                                    })}
                            </>
                        )}
                    </div>
                    {!provider && (
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
                                                <div className="flex w-fit gap-4">
                                                    <p>
                                                        Contact {index + 1}{' '}
                                                        {data.users[index]?.first_name ? `- ${data.users[index]?.first_name}` : ''}
                                                        {data.users[index]?.last_name ? ` ${data.users[index]?.last_name}` : ''}
                                                    </p>
                                                    <MinusCircleIcon onClick={() => handleRemoveContactPerson(index)} />
                                                </div>
                                            </summary>
                                            <div>
                                                <div className="flex w-full flex-col gap-4 lg:flex-row">
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
                                                        <InputError
                                                            className="mt-2"
                                                            message={errors?.users ? errors?.users[index]?.first_name : ''}
                                                        />
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
                                                        <InputError
                                                            className="mt-2"
                                                            message={errors?.users ? errors?.users[index]?.phone_number : ''}
                                                        />
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
                    )}

                    <div className="flex gap-4">
                        <Button type="submit" disabled={isSubmitting}>
                            {provider ? 'Update' : 'Submit'}
                        </Button>
                        <a href={provider ? route('tenant.providers.show', provider.id) : route('tenant.providers.index')}>
                            <Button type="button" tabIndex={6} variant={'secondary'} disabled={isSubmitting}>
                                Cancel
                            </Button>
                        </a>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
