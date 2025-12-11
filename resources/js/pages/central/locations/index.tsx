import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/central/app-layout';
import { LocationLevel, LocationType, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Index types',
        href: '/building',
    },
];
type Tabs = 'sites' | 'buildings' | 'floors' | 'rooms';

export default function IndexTypes({ types, routeName }: { types: Record<LocationLevel, LocationType[]>; routeName: string }) {
    const { t, tChoice } = useLaravelReactI18n();
    // const { delete: destroy } = useForm();

    // const submit = (type: LocationType) => {
    //     destroy(route(`central.${routeName}.destroy`, type.slug));
    // };
    const [showTab, setShowTab] = useState<Tabs>('sites');
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route(`central.${routeName}.create`)}>
                    <Button>Create</Button>
                </a>
                <div className="my-2 space-y-2 space-x-2">
                    <Button variant={showTab == 'sites' ? 'default' : 'outline'} onClick={() => setShowTab('sites')} size={'lg'}>
                        {tChoice('locations.sites', 2)}
                    </Button>
                    <Button variant={showTab == 'buildings' ? 'default' : 'outline'} onClick={() => setShowTab('buildings')} size={'lg'}>
                        {tChoice('locations.buildings', 2)}
                    </Button>
                    <Button variant={showTab == 'floors' ? 'default' : 'outline'} onClick={() => setShowTab('floors')} size={'lg'}>
                        {tChoice('locations.floors', 2)}
                    </Button>
                    <Button variant={showTab == 'rooms' ? 'default' : 'outline'} onClick={() => setShowTab('rooms')} size={'lg'}>
                        {tChoice('locations.rooms', 2)}
                    </Button>
                </div>
                {showTab === 'sites' && (
                    <>
                        <h2>Sites</h2>
                        <ul className="space-y-2">
                            {types['site']?.length > 0 &&
                                types['site'].map((type: LocationType) => (
                                    <li key={type.id} className="odd:bg-accent flex items-center justify-between p-2">
                                        <p>
                                            {type.label} ({type.prefix})
                                        </p>
                                        <div className="space-x-4">
                                            {/* <Button onClick={() => submit(type)} variant={'destructive'}>
                                        Delete
                                    </Button> */}
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
                    </>
                )}
                {showTab === 'buildings' && (
                    <>
                        <h2>Buildings</h2>
                        <ul className="space-y-2">
                            {types['building']?.length > 0 &&
                                types['building'].map((type: LocationType) => (
                                    <li key={type.id} className="odd:bg-accent flex items-center justify-between p-2">
                                        <p>
                                            {type.label} ({type.prefix})
                                        </p>
                                        <div className="space-x-4">
                                            {/* <Button onClick={() => submit(type)} variant={'destructive'}>
                                        Delete
                                    </Button> */}
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
                    </>
                )}
                {showTab === 'floors' && (
                    <>
                        <h2>Floors</h2>
                        <ul className="space-y-2">
                            {types['floor']?.length > 0 &&
                                types['floor'].map((type: LocationType) => (
                                    <li key={type.id} className="odd:bg-accent flex items-center justify-between p-2">
                                        <p>
                                            {type.label} ({type.prefix})
                                        </p>
                                        <div className="space-x-4">
                                            {/* <Button onClick={() => submit(type)} variant={'destructive'}>
                                        Delete
                                    </Button> */}
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
                    </>
                )}
                {showTab === 'rooms' && (
                    <>
                        <h2>Rooms</h2>
                        <ul className="space-y-2">
                            {types['room']?.length > 0 &&
                                types['room'].map((type: LocationType) => (
                                    <li key={type.id} className="odd:bg-accent flex items-center justify-between p-2">
                                        <p>
                                            {type.label} ({type.prefix})
                                        </p>
                                        <div className="space-x-4">
                                            {/* <Button onClick={() => submit(type)} variant={'destructive'}>
                                        Delete
                                    </Button> */}
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
                    </>
                )}
            </div>
        </AppLayout>
    );
}
