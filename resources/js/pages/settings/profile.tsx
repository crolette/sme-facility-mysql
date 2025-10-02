import { type BreadcrumbItem, type SharedData } from '@/types';
import { Transition } from '@headlessui/react';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';

import DeleteUser from '@/components/delete-user';
import HeadingSmall from '@/components/heading-small';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import axios from 'axios';
import { useToast } from '@/components/ToastrContext';
import ImageUploadModale from '@/components/ImageUploadModale';
import { Trash, Upload } from 'lucide-react';
import { router } from '@inertiajs/core';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];

type ProfileForm = {
    first_name: string;
    last_name: string;
};

export default function Profile() {
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
            const response = await axios.patch(route('tenant.profile.update', auth.user.id), data)
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

    console.log(auth.user)

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Profile settings" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Profile information" description="Update your name and email address" />

                    <div></div>
                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-2">
                            <Label htmlFor="name">First Name</Label>

                            <Input
                                id="name"
                                className="mt-1 block w-full"
                                value={data.first_name}
                                onChange={(e) => setData('first_name', e.target.value)}
                                required
                                autoComplete="given-name"
                                placeholder="First name"
                            />

                            <InputError className="mt-2" message={errors.first_name} />
                        </div>
                        <div className="grid gap-2">
                            <Label htmlFor="name">Last Name</Label>

                            <Input
                                id="name"
                                className="mt-1 block w-full"
                                value={data.last_name}
                                onChange={(e) => setData('last_name', e.target.value)}
                                required
                                autoComplete="family-name"
                                placeholder="Last name"
                            />

                            <InputError className="mt-2" message={errors.last_name} />
                        </div>

                        <div className="grid gap-2">
                            <Label htmlFor="email">Email address</Label>

                            <Input id="email" type="email" className="mt-1 block w-full" disabled placeholder="Email address" />
                        </div>

                        <div className="flex items-center gap-4">
                            <Button disabled={processing}>Save</Button>

                            <Transition
                                show={recentlySuccessful}
                                enter="transition ease-in-out"
                                enterFrom="opacity-0"
                                leave="transition ease-in-out"
                                leaveTo="opacity-0"
                            >
                                <p className="text-sm text-neutral-600">Saved</p>
                            </Transition>
                        </div>
                    </form>
                </div>

                <div>
                    <HeadingSmall title="Profile picture" description="Update your profile picture" />
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
                        Update profile picture
                    </Button>
                    <ImageUploadModale
                        isOpen={isModalOpen}
                        onClose={() => setIsModalOpen(false)}
                        uploadUrl={route('api.users.picture.store', auth.user.id)}
                        onUploadSuccess={() => {
                             window.location.reload();;
                        }}
                    />
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
