import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, TenantBuilding, TenantFloor, TenantSite } from '@/types';
import { Head, useForm } from '@inertiajs/react';

export default function IndexSites({ locations, routeName }: { locations: TenantSite[] | TenantBuilding[] | TenantFloor[]; routeName: string }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${routeName}`,
            href: `/${routeName}`,
        },
    ];

    const { delete: destroy } = useForm();

    const deleteLocation = (locationId: number) => {
        destroy(route(`tenant.${routeName}.destroy`, locationId));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sites" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route(`tenant.${routeName}.create`)}>
                    <Button>Create</Button>
                </a>
                <ul>
                    {locations.length > 0 &&
                        locations.map((location) => (
                            <li key={location.id}>
                                <p>
                                    {location.reference_code} - {location.code} - {location.location_type.label}
                                </p>{' '}
                                <p>{location.maintainable.name}</p>
                                <p>{location.maintainable.description}</p>
                                <Button onClick={() => deleteLocation(location.id)} variant={'destructive'}>
                                    Delete
                                </Button>
                                <a href={route(`tenant.${routeName}.edit`, location.id)}>
                                    <Button>Edit</Button>
                                </a>
                                <a href={route(`tenant.${routeName}.show`, location.id)}>
                                    <Button variant={'outline'}>See</Button>
                                </a>
                            </li>
                        ))}
                </ul>
            </div>
        </AppLayout>
    );
}
