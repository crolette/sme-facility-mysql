import { AssetManager } from '@/components/tenant/assetManager';
import { DocumentManager } from '@/components/tenant/documentManager';
import { InterventionManager } from '@/components/tenant/interventionManager';

import { PictureManager } from '@/components/tenant/pictureManager';
import RealocateRoomManager from '@/components/tenant/relocateRoomManager';
import { TicketManager } from '@/components/tenant/ticketManager';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { TenantBuilding, TenantFloor, TenantRoom, TenantSite, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';

export default function ShowLocation({ item, routeName }: { item: TenantSite | TenantBuilding | TenantFloor | TenantRoom; routeName: string }) {
    const [location, setLocation] = useState(item);
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${location.reference_code} - ${location.maintainable.name}`,
            href: ``,
        },
    ];

    const [showModaleRelocateRoom, setShowModaleRelocateRoom] = useState<boolean>(false);

    const fetchLocation = async () => {
        const response = await axios.get(route(`api.${routeName}.show`, location?.reference_code));
        setLocation(response.data.data);
    };

    const generateQR = async () => {
        const response = await axios.post(route(`api.${routeName}.qr.regen`, location.reference_code));
        if (response.data.status === 'success') {
            fetchLocation();
        }
    };

    const markMaintenanceDone = async () => {
        const response = await axios.post(route('api.maintenance.done', location.maintainable.id));
        fetchLocation();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />
            <div className="flex h-full flex-1 flex-col justify-between gap-4 rounded-xl p-4">
                <div>
                    <a href={route(`tenant.${routeName}.edit`, location.reference_code)}>
                        <Button>Edit</Button>
                    </a>
                    {routeName === 'rooms' && <Button onClick={() => setShowModaleRelocateRoom(!showModaleRelocateRoom)}>Redefine room</Button>}
                    {location.maintainable.need_maintenance && (
                        <Button onClick={() => markMaintenanceDone()} variant={'green'}>
                            Mark maintenance as done
                        </Button>
                    )}
                    <Button onClick={generateQR} variant={'secondary'}>
                        Generate new QR
                    </Button>
                </div>

                {routeName === 'rooms' && showModaleRelocateRoom && (
                    <RealocateRoomManager room={location} itemCode={location.reference_code} onClose={() => setShowModaleRelocateRoom(false)} />
                )}
                <div className="flex h-full flex-1 flex-col gap-4">
                    <div className="flex w-full shrink-0 justify-between rounded-md border p-4">
                        <div>
                            <h2>Code</h2>
                            <div>
                                <p>Code : {location.code}</p>
                                <p>Reference code : {location.reference_code}</p>
                            </div>
                        </div>
                        <div className="shrink-1">
                            {location.qr_code && (
                                <a href={route('api.file.download', { path: location.qr_code })} download className="w-fit cursor-pointer">
                                    <img
                                        key={location.qr_code}
                                        src={route('api.image.show', { path: location.qr_code })}
                                        alt=""
                                        className="h-32 w-32"
                                    />
                                </a>
                            )}
                        </div>
                    </div>
                    <div className="rounded-md border p-4">
                        <h2>Maintenance</h2>
                        <div>
                            <p>
                                Maintenance manager:
                                {location.maintainable.manager ? (
                                    <a href={route('tenant.users.show', location.maintainable.manager.id)}>
                                        {' '}
                                        {location.maintainable.manager.full_name}
                                    </a>
                                ) : (
                                    'No manager'
                                )}
                            </p>
                            <p>Maintenance frequency : {location.maintainable.maintenance_frequency}</p>
                            <p>Next maintenance date : {location.maintainable.next_maintenance_date}</p>
                            <p>Last maintenance date : {location.maintainable.last_maintenance_date}</p>
                        </div>
                    </div>
                    <div className="rounded-md border p-4">
                        <h2>Location information</h2>
                        <div>
                            <p>Category : {location.category}</p>
                            <p>Name : {location.name}</p>
                            <p>Address : {location.address}</p>
                            <p>Description : {location.description}</p>
                            {location.location_type.slug === 'outdoor' ? (
                                <>
                                    <p>
                                        Outdoor: {location.surface_outdoor} ({location.outdoor_material})
                                    </p>
                                </>
                            ) : (
                                <>
                                    <p>
                                        Floor: {location.surface_floor} ({location.floor_material})
                                    </p>
                                    <p>
                                        Walls: {location.surface_walls} ({location.wall_material})
                                    </p>
                                </>
                            )}
                        </div>
                    </div>
                    {location.maintainable.providers && location.maintainable.providers.length > 0 && (
                        <div className="rounded-md border p-4">
                            <h2>Providers</h2>
                            <ul>
                                {location.maintainable.providers.map((provider, index) => (
                                    <li key={index}>
                                        <a href={route('tenant.providers.show', provider.id)}>{provider.name}</a>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    )}
                    <AssetManager itemCode={location.reference_code} type={routeName} />
                    <TicketManager itemCode={location.reference_code} getTicketsUrl={`api.${routeName}.tickets`} locationType={routeName} />
                    <DocumentManager
                        itemCodeId={location.reference_code}
                        getDocumentsUrl={`api.${routeName}.documents`}
                        editRoute={`api.documents.update`}
                        uploadRoute={`api.${routeName}.documents.post`}
                        deleteRoute={`api.documents.delete`}
                        showRoute={'api.documents.show'}
                    />
                    <PictureManager
                        itemCodeId={location.reference_code}
                        getPicturesUrl={`api.${routeName}.pictures`}
                        uploadRoute={`api.${routeName}.pictures.post`}
                        deleteRoute={`api.pictures.delete`}
                        showRoute={'api.pictures.show'}
                    />
                    <InterventionManager
                        itemCodeId={location.reference_code}
                        getInterventionsUrl={`api.${routeName}.interventions`}
                        type={routeName}
                    />
                </div>
            </div>
        </AppLayout>
    );
}
