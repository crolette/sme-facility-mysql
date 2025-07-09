import AppLayout from '@/layouts/central/app-layout';
import { LocationType, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function ShowType({ type }: { type: LocationType }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${type.label}`,
            href: ``,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <ul>
                    <li key={type.id}>
                        {type.prefix} - {type.slug} - {type.level}
                        <ul>
                            {type.translations.map((translation) => (
                                <li key={translation.id}>
                                    {translation.locale} - {translation.label}
                                </li>
                            ))}
                        </ul>
                        {/* <Button onClick={() => setData('tenant', tenant.id)} variant={'destructive'}>
                                    Delete
                                </Button> */}
                    </li>
                </ul>
            </div>
        </AppLayout>
    );
}
