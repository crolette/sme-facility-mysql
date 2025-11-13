import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

import HeadingSmall from '@/components/heading-small';
import ImageUploadModale from '@/components/ImageUploadModale';
import InputError from '@/components/input-error';
import LocaleChange from '@/components/tenant/LocaleChange';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Trash, Upload } from 'lucide-react';

type ProfileForm = {
    first_name: string;
    last_name: string;
};

export default function Profile() {
    const { t } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${t('settings.profile')}`,
            href: '/settings/profile',
        },
        {
            title: `${t('settings.settings')}`,
            href: '/settings/profile',
        },
    ];
    const { auth } = usePage<SharedData>().props;
    const [isModalOpen, setIsModalOpen] = useState(false);
    const { showToast } = useToast();

    const { data, setData, errors, processing, recentlySuccessful } = useForm<Required<ProfileForm>>({
        first_name: auth.user.first_name,
        last_name: auth.user.last_name,
    });

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.patch(route('tenant.profile.update', auth.user.id), data);
            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.success);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.success);
        }
    };

    const deleteProfilePicture = async () => {
        try {
            const response = await axios.delete(route('api.users.picture.destroy', auth.user.id));
            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
                window.location.reload();
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('settings.settings')} />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title={t('settings.profile_title')} description={t('settings.profile_description')} />
                    <div className="flex flex-col">
                        <Label htmlFor="locale">{t('common.language')}</Label>
                        <LocaleChange />
                    </div>
                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="name">{t('common.first_name')}</Label>

                            <Input
                                id="name"
                                className="mt-1 block w-full"
                                value={data.first_name}
                                onChange={(e) => setData('first_name', e.target.value)}
                                required
                                autoComplete="given-name"
                                placeholder={t('common.first_name_placeholder')}
                            />

                            <InputError className="mt-2" message={errors.first_name} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="name">{t('common.last_name')}</Label>

                            <Input
                                id="name"
                                className="mt-1 block w-full"
                                value={data.last_name}
                                onChange={(e) => setData('last_name', e.target.value)}
                                required
                                autoComplete="family-name"
                                placeholder={t('common.last_name_placeholder')}
                            />

                            <InputError className="mt-2" message={errors.last_name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email">{t('common.email')}</Label>

                            <Input
                                id="email"
                                type="email"
                                className="mt-1 block w-full"
                                disabled
                                placeholder={t('common.email_placeholder')}
                                value={auth.user.email}
                            />
                        </div>

                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>{t('actions.save')}</Button>

                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">{t('actions.saved')}</p>
                            </Transition>
                        </div>
                    </form>
                </div>

                <div>
                    <HeadingSmall title={t('settings.picture')} description={t('settings.picture_description')} />
                    {auth.user.avatar && (
                        <div className="relative w-fit">
                            <img src={route('api.image.show', { path: auth.user.avatar })} alt="" className="h-40 w-40 rounded-full object-cover" />
                            <Button type="button" onClick={deleteProfilePicture} variant={'destructive'} className="absolute top-2 right-2">
                                <Trash></Trash>
                            </Button>
                        </div>
                    )}
                    <Button onClick={() => setIsModalOpen(true)} variant={'secondary'}>
                        <Upload size={20} />
                        {t('actions.upload-type', { type: t('settings.picture') })}
                    </Button>
                    <ImageUploadModale
                        isOpen={isModalOpen}
                        onClose={() => setIsModalOpen(false)}
                        uploadUrl={route('api.users.picture.store', auth.user.id)}
                        onUploadSuccess={() => {
                            window.location.reload();
                        }}
                    />
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
