import InputError from '@/components/input-error';
import Modale from '@/components/Modale';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pill } from '@/components/ui/pill';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { BreadcrumbItem, CentralType, Country, Provider } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { MinusCircleIcon, PlusCircleIcon, X } from 'lucide-react';
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
    categories: CentralType[];
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
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: provider ? `Update ${tChoice('providers.title', 1)} ${provider.name}` : `Create ${tChoice('providers.title', 1)}`,
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
        categories: provider?.categories ?? [],
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

    const handleCategoryProvider = (categoryId: number) => {
        const category = providerCategories.find((elem) => elem.id == categoryId);
        if (!data.categories.find((elem) => elem.id == categoryId)) {
            if (category) {
                const newCategories = data.categories;

                setData((prev) => ({ ...prev, categories: [...newCategories, category] }));
            }
        }
    };

    const handleRemoveCategoryProvider = (categoryId: number) => (e: React.MouseEvent<SVGElement, MouseEvent>) => {
        e.stopPropagation();
        e.preventDefault();
        const newCategories = data.categories.filter((elem) => elem.id !== categoryId);
        setData((prev) => ({ ...prev, categories: newCategories }));
    };

    console.log(data.categories);

    const [selectedCountry, setSelectedCountry] = useState(data.country_code ?? '');

    useEffect(() => {
        setData('country_code', selectedCountry);
    }, [selectedCountry]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={provider ? `Update ${tChoice('providers.title', 1)} ${provider.name}` : `Create ${tChoice('providers.title', 1)}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <form onSubmit={submit} className="space-y-4">
                    <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                        <h2>{t('common.information')}</h2>
                        <div className="flex w-full flex-col gap-4 lg:flex-row">
                            <div className="w-full">
                                <Label htmlFor="name">{t('providers.company_name')}</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    onChange={(e) => setData('name', e.target.value)}
                                    value={data.name}
                                    required
                                    minLength={4}
                                    maxLength={100}
                                    placeholder="Company SA"
                                />
                                <InputError className="mt-2" message={errors?.name ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label htmlFor="category">{t('common.category')}</Label>

                                <select
                                    name="category"
                                    value={''}
                                    required={data.categories.length < 1}
                                    // onChange={(e) => setData('categoryId', e.target.value)}
                                    onChange={(e) => handleCategoryProvider(parseInt(e.target.value))}
                                    id="category"
                                    className={cn(
                                        'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                    )}
                                >
                                    {providerCategories && providerCategories.length > 0 && (
                                        <>
                                            <option value="" disabled className="bg-background text-foreground">
                                                {t('actions.select-type', { type: t('common.category') })}
                                            </option>
                                            {providerCategories?.map((category) => (
                                                <option value={category.id} key={category.id} className="bg-background text-foreground">
                                                    {category.label}
                                                </option>
                                            ))}
                                        </>
                                    )}
                                </select>
                                <div className="flex flex-wrap gap-2">
                                    {data.categories.map((category, index) => (
                                        <Pill size={'sm'} key={index} className="flex items-center gap-2">
                                            {category.label}
                                            <X size={16} onClick={handleRemoveCategoryProvider(category.id)} />
                                        </Pill>
                                    ))}
                                </div>
                                <InputError className="mt-2" message={errors?.categories ?? ''} />
                            </div>
                        </div>
                        <div className="flex w-full flex-col gap-4 lg:flex-row">
                            <div className="w-full">
                                <Label htmlFor="email">{t('common.email')}</Label>
                                <Input
                                    id="email"
                                    type="email"
                                    minLength={10}
                                    maxLength={255}
                                    onChange={(e) => setData('email', e.target.value)}
                                    value={data.email}
                                    required
                                    placeholder={t('common.email_placeholder')}
                                />
                                <InputError className="mt-2" message={errors?.email ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label htmlFor="phone_number">{t('common.phone')}</Label>
                                <Input
                                    id="phone_number"
                                    type="text"
                                    onChange={(e) => setData('phone_number', e.target.value)}
                                    value={data.phone_number}
                                    required
                                    maxLength={16}
                                    placeholder={t('common.phone_placeholder')}
                                />
                                <InputError className="mt-2" message={errors?.phone_number ?? ''} />
                            </div>
                        </div>
                        <div className="flex w-full flex-col gap-4 lg:flex-row">
                            <div className="w-full">
                                <Label>{t('providers.vat_number')}</Label>
                                <Input
                                    type="text"
                                    onChange={(e) => setData('vat_number', e.target.value)}
                                    value={data.vat_number}
                                    placeholder={t('providers.vat_number_placeholder')}
                                />
                                <InputError className="mt-2" message={errors?.vat_number ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label>{t('providers.website')}</Label>
                                <Input
                                    type="text"
                                    onChange={(e) => setData('website', e.target.value)}
                                    value={data.website}
                                    placeholder={t('providers.website_placeholder')}
                                />
                                <InputError className="mt-2" message={errors?.website ?? ''} />
                            </div>
                        </div>

                        <h5>{t('common.address')}</h5>
                        <div className="flex w-full flex-col gap-4 lg:flex-row">
                            <div className="w-full">
                                <Label htmlFor="street">{t('common.street')}</Label>
                                <Input
                                    id="street"
                                    type="text"
                                    onChange={(e) => setData('street', e.target.value)}
                                    value={data.street}
                                    required
                                    placeholder={t('common.street_placeholder')}
                                />
                                <InputError className="mt-2" message={errors?.street ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label htmlFor="house_number">{t('common.house_number')}</Label>
                                <Input
                                    id="house_number"
                                    type="text"
                                    onChange={(e) => setData('house_number', e.target.value)}
                                    value={data.house_number}
                                    placeholder="10"
                                />
                                <InputError className="mt-2" message={errors?.house_number ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label htmlFor="postal_code">{t('common.postal_code')}</Label>
                                <Input
                                    id="postal_code"
                                    type="text"
                                    onChange={(e) => setData('postal_code', e.target.value)}
                                    value={data.postal_code}
                                    required
                                    placeholder={t('common.postal_code_placeholder')}
                                />
                                <InputError className="mt-2" message={errors?.postal_code ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label htmlFor="city">{t('common.city')}</Label>
                                <Input
                                    id="city"
                                    type="text"
                                    onChange={(e) => setData('city', e.target.value)}
                                    value={data.city}
                                    required
                                    placeholder={t('common.city_placeholder')}
                                />
                                <InputError className="mt-2" message={errors?.city ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label htmlFor="country">{t('common.country')}</Label>
                                <Select value={selectedCountry} onValueChange={setSelectedCountry} required>
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

                        {!provider && (
                            <>
                                <Label htmlFor="logo">{t('common.logo')}</Label>
                                <Input
                                    type="file"
                                    name="logo"
                                    id="logo"
                                    onChange={(e) => setData('pictures', e.target.files ? [e.target.files[0]] : null)}
                                    accept="image/png, image/jpeg, image/jpg"
                                />
                                <p className="text-xs">{t('common.pictures_restriction_description')}</p>
                                {errors?.pictures &&
                                    errors?.pictures.map((error) => {
                                        <InputError className="mt-2" message={error ?? ''} />;
                                    })}
                            </>
                        )}
                    </div>
                    {!provider && (
                        <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                            <h2>{tChoice('contacts.title', 2)}</h2>
                            <p className="">
                                {t('actions.add-type', { type: tChoice('contacts.title', 1) })}
                                <PlusCircleIcon className="inline-block" onClick={() => setCountContactPersons((prev) => prev + 1)} />
                            </p>
                            <div className="space-y-4">
                                {countContactPersons > 0 &&
                                    [...Array(countContactPersons)].map((_, index) => (
                                        <details key={index} className="flex flex-col rounded-md border-2 border-slate-400 p-4" open>
                                            <summary>
                                                <div className="flex w-fit gap-4">
                                                    <p>
                                                        {tChoice('contacts.title', 1)} {index + 1}{' '}
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
                                                            {t('common.first_name')}
                                                        </Label>
                                                        <Input
                                                            id={data.users[index]?.first_name}
                                                            type="text"
                                                            value={data.users[index]?.first_name ?? ''}
                                                            placeholder={t('common.first_name_placeholder')}
                                                            minLength={3}
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
                                                            {t('common.last_name')}
                                                        </Label>
                                                        <Input
                                                            id={data.users[index]?.last_name}
                                                            type="text"
                                                            value={data.users[index]?.last_name ?? ''}
                                                            placeholder={t('common.last_name_placeholder')}
                                                            minLength={3}
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
                                                            {t('common.email')}
                                                        </Label>
                                                        <Input
                                                            id={data.users[index]?.email}
                                                            type="email"
                                                            value={data.users[index]?.email ?? ''}
                                                            placeholder={t('common.email_placeholder')}
                                                            minLength={20}
                                                            maxLength={100}
                                                            required
                                                            onChange={(e) => handleChangeContactPerson(index, 'email', e.target.value)}
                                                        />
                                                        <InputError className="mt-2" message={errors?.users ? errors?.users[index]?.email : ''} />
                                                    </div>
                                                    <div className="w-full">
                                                        <Label className="font-medium" htmlFor={data.users[index]?.phone_number}>
                                                            {t('common.phone')}
                                                        </Label>
                                                        <Input
                                                            id={data.users[index]?.phone_number}
                                                            type="text"
                                                            value={data.users[index]?.phone_number ?? ''}
                                                            placeholder={t('common.phone_placeholder')}
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
                                                    {t('contacts.job_position')}
                                                </Label>
                                                <Input
                                                    id={data.users[index]?.job_position}
                                                    type="text"
                                                    value={data.users[index]?.job_position ?? ''}
                                                    placeholder={t('contacts.job_position_placeholder')}
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
                            {provider ? t('actions.update') : t('actions.submit')}
                        </Button>
                        <a href={provider ? route('tenant.providers.show', provider.id) : route('tenant.providers.index')}>
                            <Button type="button" tabIndex={6} variant={'secondary'} disabled={isSubmitting}>
                                {t('actions.cancel')}
                            </Button>
                        </a>
                    </div>
                </form>
            </div>
            <Modale
                title={
                    provider
                        ? t('actions.type-being-updated', { type: tChoice('providers.title', 1) })
                        : t('actions.type-being-submitted', { type: tChoice('providers.title', 1) })
                }
                isOpen={isSubmitting}
                isProcessing={isSubmitting}
            />
        </AppLayout>
    );
}
