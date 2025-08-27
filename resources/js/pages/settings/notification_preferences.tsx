import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notification preferences',
        href: '/settings/notification-preferences',
    },
];

export default function NotificationPreferences({ preferences }: { preferences: [] }) {
    console.log(preferences);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notification preferences" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Notification preferences" description="Change your notification preferences" />
                    {/* {preferences.map((key, value) => (
                        <p>{key}</p>
                    ))} */}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
