import InputError from '@/components/input-error';
import SearchableInput from '@/components/SearchableInput';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Provider, User } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { FormEventHandler, useState } from 'react';

interface FormDataUser {
    first_name: string;
    last_name: string;
    email: string;
    job_position: string;
    can_login: boolean;
    avatar: File | string | null;
    provider_id: string | number | null;
    provider_name: string | null;
    phone_number: string;
    role: string;
}

export default function CreateUpdateUser({ user, roles, canAddLoginableUser }: { user?: User; roles: []; canAddLoginableUser: boolean }) {
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Indes ${tChoice('contacts.title', 2)}`,
            href: `/users`,
        },
        {
            title: user ? `Update ${tChoice('contacts.title', 1)} ${user.full_name}` : `Create ${tChoice('contacts.title', 1)}`,
            href: `/users/create`,
        },
    ];
    const { showToast } = useToast();
    const [errors, setErrors] = useState<FormDataUser>();
    const [isSubmitting, setIsSubmitting] = useState(false);

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

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsSubmitting(true);
        if (user) {
            try {
                const response = await axios.patch(route('api.users.update', user.id), data);
                if (response.data.status === 'success') {
                    router.visit(route('tenant.users.show', user.id), {
                        preserveScroll: false,
                    });
                }
            } catch (error) {
                setErrors(error.response.data.errors);
                showToast(error.response.data.message, error.response.data.status);
                setIsSubmitting(false);
            }
        } else {
            try {
                const response = await axios.post(route('api.users.store'), data, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                if (response.data.status === 'success') {
                    showToast(response.data.message, response.data.status);
                    router.visit(route('tenant.users.index'), {
                        preserveScroll: false,
                    });
                }
                reset();
            } catch (error) {
                setErrors(error.response.data.errors);
                showToast(error.response.data.message, error.response.data.status);
                setIsSubmitting(false);
            }
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={user ? `Update ${tChoice('contacts.title', 1)} ${user.full_name}` : `Create ${tChoice('contacts.title', 1)}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <form onSubmit={submit} className="space-y-4">
                    <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                        <div className="flex w-full flex-col gap-4 lg:flex-row">
                            <div className="w-full">
                                <Label htmlFor="first_name">{t('common.first_name')}</Label>
                                <Input
                                    id="first_name"
                                    type="text"
                                    onChange={(e) => setData('first_name', e.target.value)}
                                    value={data.first_name}
                                    minLength={3}
                                    maxLength={100}
                                    required
                                    placeholder={t('common.first_name_placeholder')}
                                />
                                <InputError message={errors?.first_name ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label htmlFor="last_name">{t('common.last_name')}</Label>
                                <Input
                                    id="last_name"
                                    type="text"
                                    minLength={3}
                                    maxLength={100}
                                    onChange={(e) => setData('last_name', e.target.value)}
                                    value={data.last_name}
                                    required
                                    placeholder={t('common.last_name_placeholder')}
                                />
                                <InputError message={errors?.last_name ?? ''} />
                            </div>
                        </div>
                        <div className="mt-4 flex w-full flex-col gap-4 lg:flex-row">
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
                                <InputError message={errors?.email ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label htmlFor="phone">{t('common.phone')}</Label>
                                <Input
                                    id="phone"
                                    type="text"
                                    maxLength={16}
                                    onChange={(e) => setData('phone_number', e.target.value)}
                                    value={data.phone_number}
                                    placeholder={t('common.phone_placeholder')}
                                />
                                <InputError message={errors?.phone_number ?? ''} />
                            </div>
                        </div>

                        <div className="mt-4 space-y-2">
                            <Label htmlFor="job_position">{t('contacts.job_position')}</Label>
                            <Input
                                id="job_position"
                                type="text"
                                onChange={(e) => setData('job_position', e.target.value)}
                                value={data.job_position}
                                placeholder={t('contacts.job_position_placeholder')}
                            />
                            <InputError message={errors?.job_position ?? ''} />
                        </div>

                        {!user && !canAddLoginableUser && <p>Loginable user limit reached</p>}
                        <div className="mt-4 space-y-2">
                            {(user?.can_login || canAddLoginableUser) && (
                                <>
                                    <div className="flex items-center gap-2">
                                        <Label htmlFor="can_login">{t('contacts.can_login')}</Label>
                                        <Checkbox
                                            id="can_login"
                                            onClick={() => setData('can_login', !data.can_login)}
                                            checked={data.can_login ?? false}
                                        />
                                    </div>
                                    {data.can_login && (
                                        <div>
                                            <Label htmlFor="role">{t('contacts.role')}</Label>
                                            <select
                                                name="role"
                                                id="role"
                                                value={data.role}
                                                onChange={(e) => setData('role', e.target.value)}
                                                className="block"
                                            >
                                                <option value="">{t('actions.select-type', { type: t('contacts.role') })}</option>
                                                {roles.map((role, index) => (
                                                    <option key={index} value={role}>
                                                        {role}
                                                    </option>
                                                ))}
                                            </select>
                                            <InputError message={errors?.can_login ? 'Wrong value' : ''} />
                                        </div>
                                    )}
                                </>
                            )}
                        </div>
                        {!user && (
                            <div className="mt-4">
                                <Label>{t('contacts.profile_picture')}</Label>
                                <Input
                                    type="file"
                                    name="logo"
                                    id="logo"
                                    onChange={(e) => setData('avatar', e.target.files ? e.target.files[0] : null)}
                                    accept="image/png, image/jpeg, image/jpg"
                                />
                                <p className="text-xs">{t('common.pictures_restriction_description')}</p>
                                <InputError message={errors?.avatar ? 'Wrong picture' : ''} />
                            </div>
                        )}

                        <div className="mt-4">
                            <Label>{tChoice('providers.title', 1)}</Label>
                            <SearchableInput<Provider>
                                searchUrl={route('api.providers.search')}
                                displayValue={data.provider_name}
                                getDisplayText={(provider) => provider.name}
                                getKey={(provider) => provider.id}
                                onSelect={(provider) => {
                                    setData('provider_id', provider.id);
                                    setData('provider_name', provider.name);
                                }}
                                onDelete={() => {
                                    setData('provider_id', null);
                                    setData('provider_name', null);
                                }}
                                placeholder={t('actions.search-type', { type: tChoice('providers.title', 1) })}
                                className="mb-4"
                            />
                        </div>
                    </div>
                    <div className="space-x-2">
                        <Button disabled={isSubmitting}>{t('actions.submit')}</Button>
                        <a href={user ? route('tenant.users.show', user.id) : route('tenant.users.index')}>
                            <Button type="button" variant={'secondary'} disabled={isSubmitting}>
                                {t('actions.cancel')}
                            </Button>
                        </a>
                    </div>
                </form>
            </div>
        </AppLayout>
    );
}
