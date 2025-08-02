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
import { useState } from 'react';

export default function ShowLocation({
    location,
    routeName,
}: {
    location: TenantSite | TenantBuilding | TenantFloor | TenantRoom;
    routeName: string;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${location.reference_code} - ${location.maintainable.name}`,
            href: ``,
        },
    ];

    const [showModaleRelocateRoom, setShowModaleRelocateRoom] = useState<boolean>(false);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />
            <div>
                <a href={route(`tenant.${routeName}.edit`, location.reference_code)}>
                    <Button>Edit</Button>
                </a>
                <Button onClick={() => setShowModaleRelocateRoom(!showModaleRelocateRoom)}>Redefine room</Button>
            </div>
            {routeName === 'rooms' && showModaleRelocateRoom && (
                <RealocateRoomManager room={location} itemCode={location.reference_code} onClose={() => setShowModaleRelocateRoom(false)} />
            )}
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {location.reference_code} - {location.code} - {location.location_type.label}
                <p>{location.name}</p>
                <p>{location.description}</p>
                <p>Surface floor: {location.surface_floor}</p>
                <p>Surface walls: {location.surface_walls}</p>
                {/* <p>{location.qr_code_path}</p> */}
                {location.qr_code && (
                    <a href={route(`api.qr.download`, { path: location.qr_code })} className="w-fit cursor-pointer">
                        <img src={route('api.qr.show', { path: location.qr_code })} alt="" className="h-32 w-32" />
                    </a>
                )}
                <p>
                    Maintenance manager:
                    {location.maintainable.manager ? (
                        <a href={route('tenant.users.show', location.maintainable.manager.id)}>{location.maintainable.manager.full_name}</a>
                    ) : (
                        'No manager'
                    )}
                </p>
                <p>Providers</p>
                {location.maintainable.providers && (
                    <ul>
                        {location.maintainable.providers.map((provider, index) => (
                            <li key={index}>
                                <a href={route('tenant.providers.show', provider.id)}>{provider.name}</a>
                            </li>
                        ))}
                    </ul>
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
                <InterventionManager itemCodeId={location.reference_code} getInterventionsUrl={`api.${routeName}.interventions`} type={routeName} />
            </div>
        </AppLayout>
    );
}
