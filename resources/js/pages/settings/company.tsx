import { type BreadcrumbItem, type SharedData, Company } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import HeadingSmall from '@/components/heading-small';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { useState } from 'react';
import ImageUploadModale from '@/components/ImageUploadModale';
import { Button } from '@/components/ui/button';
import { Upload } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];


export default function CompanySettings({ company }: { company: Company }) {
    // const { auth } = usePage<SharedData>().props;
 const [isModalOpen, setIsModalOpen] = useState(false);
    console.log(company);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Company" />

            <SettingsLayout>
                <div className="space-y-6">
                    <Button onClick={() => setIsModalOpen(true)} variant={'secondary'}>
                        <Upload size={20} />
                        Uploader un logo
                    </Button>
                    <HeadingSmall title="Company information" />
                    <p>Name: {company.name}</p>
                    <p>VAT number: {company.vat_number}</p>
                    <p>Address: {company.address}</p>
                    <p>Logo:</p>
                    <img src={route('api.image.show', { path: company.logo })} alt="" className="h-40 object-cover" />
                    <ImageUploadModale
                        isOpen={isModalOpen}
                        onClose={() => setIsModalOpen(false)}
                        uploadUrl={route('api.company.logo.store')}
                        // onUploadSuccess={handleUploadSuccess}
                    />
                    {/* <form onSubmit={submit} className="space-y-6">
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

                            <Input
                                id="email"
                                type="email"
                                className="mt-1 block w-full"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                required
                                autoComplete="username"
                                placeholder="Email address"
                            />

                            <InputError className="mt-2" message={errors.email} />
                        </div>

                        {mustVerifyEmail && auth.user.email_verified_at === null && (
                            <div>
                                <p className="text-muted-foreground -mt-4 text-sm">
                                    Your email address is unverified.{' '}
                                    <Link
                                        href={route('verification.send')}
                                        method="post"
                                        as="button"
                                        className="text-foreground underline decoration-neutral-300 underline-offset-4 transition-colors duration-300 ease-out hover:decoration-current! dark:decoration-neutral-500"
                                    >
                                        Click here to resend the verification email.
                                    </Link>
                                </p>

                                {status === 'verification-link-sent' && (
                                    <div className="mt-2 text-sm font-medium text-green-600">
                                        A new verification link has been sent to your email address.
                                    </div>
                                )}
                            </div>
                        )}

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
                    </form> */}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
