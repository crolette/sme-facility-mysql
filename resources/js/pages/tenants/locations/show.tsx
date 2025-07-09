import AppLayout from '@/layouts/app-layout';
import { TenantSite, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function ShowLocation({ location }: { location: TenantSite }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${location.reference_code} - ${location.maintainable.name}`,
            href: ``,
        },
    ];

    console.log(location);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {location.reference_code} - {location.code} - {location.location_type.label}
                {location.site && <a href={route('tenant.sites.show', location.site?.id)}>S1</a>}
                <p>{location.maintainable?.name}</p>
                <p>{location.maintainable?.description}</p>
            </div>
        </AppLayout>
    );
}
