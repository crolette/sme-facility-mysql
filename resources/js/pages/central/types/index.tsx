import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/central/app-layout';
import { LocationLevel, LocationType, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Index types',
        href: '/building',
    },
];

export default function IndexTypes({ types, routeName }: { types: Record<LocationLevel, LocationType[]>; routeName: string }) {
    const { delete: destroy } = useForm();

    const submit = (type: LocationType) => {
        destroy(route(`central.${routeName}.destroy`, type.slug));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route(`central.${routeName}.create`)}>
                    <Button>Create</Button>
                </a>
                <h2>Sites</h2>
                <ul>
                    {types['site']?.length > 0 &&
                        types['site'].map((type: LocationType) => (
                            <li key={type.id} className="grid grid-cols-2">
                                <p>
                                    {type.label} ({type.prefix})
                                </p>
                                <div className="space-x-4">
                                    <Button onClick={() => submit(type)} variant={'destructive'}>
                                        Delete
                                    </Button>
                                    <a href={route(`central.${routeName}.edit`, type.slug)}>
                                        <Button>Edit</Button>
                                    </a>
                                    <a href={route(`central.${routeName}.show`, type.slug)}>
                                        <Button variant={'outline'}>See</Button>
                                    </a>
                                </div>
                            </li>
                        ))}
                </ul>
                <h2>Buildings</h2>
                <ul>
                    {types['building']?.length > 0 &&
                        types['building'].map((type: LocationType) => (
                            <li key={type.id} className="grid grid-cols-2">
                                <p>
                                    {type.label} ({type.prefix})
                                </p>
                                <div className="space-x-4">
                                    <Button onClick={() => submit(type)} variant={'destructive'}>
                                        Delete
                                    </Button>
                                    <a href={route(`central.${routeName}.edit`, type.slug)}>
                                        <Button>Edit</Button>
                                    </a>
                                    <a href={route(`central.${routeName}.show`, type.slug)}>
                                        <Button variant={'outline'}>See</Button>
                                    </a>
                                </div>
                            </li>
                        ))}
                </ul>
                <h2>Floors</h2>
                <ul>
                    {types['floor']?.length > 0 &&
                        types['floor'].map((type: LocationType) => (
                            <li key={type.id} className="grid grid-cols-2">
                                <p>
                                    {type.label} ({type.prefix})
                                </p>
                                <div className="space-x-4">
                                    <Button onClick={() => submit(type)} variant={'destructive'}>
                                        Delete
                                    </Button>
                                    <a href={route(`central.${routeName}.edit`, type.slug)}>
                                        <Button>Edit</Button>
                                    </a>
                                    <a href={route(`central.${routeName}.show`, type.slug)}>
                                        <Button variant={'outline'}>See</Button>
                                    </a>
                                </div>
                            </li>
                        ))}
                </ul>
                <h2>Rooms</h2>
                <ul>
                    {types['room']?.length > 0 &&
                        types['room'].map((type: LocationType) => (
                            <li key={type.id} className="grid grid-cols-2">
                                <p>
                                    {type.label} ({type.prefix})
                                </p>
                                <div className="space-x-4">
                                    <Button onClick={() => submit(type)} variant={'destructive'}>
                                        Delete
                                    </Button>
                                    <a href={route(`central.${routeName}.edit`, type.slug)}>
                                        <Button>Edit</Button>
                                    </a>
                                    <a href={route(`central.${routeName}.show`, type.slug)}>
                                        <Button variant={'outline'}>See</Button>
                                    </a>
                                </div>
                            </li>
                        ))}
                </ul>
            </div>
        </AppLayout>
    );
}
