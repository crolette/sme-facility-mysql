import { DocumentManager } from '@/components/tenant/documentManager';

import { PictureManager } from '@/components/tenant/pictureManager';
import { TicketManager } from '@/components/tenant/ticketManager';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { TenantSite, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function ShowLocation({ location, routeName }: { location: TenantSite; routeName: string }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${location.reference_code} - ${location.maintainable.name}`,
            href: ``,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />
            <div>
                <a href={route(`tenant.${routeName}.edit`, location.id)}>
                    <Button>Edit</Button>
                </a>
            </div>
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {location.reference_code} - {location.code} - {location.location_type.label}
                <p>{location.maintainable?.name}</p>
                <p>{location.maintainable?.description}</p>
                <TicketManager itemCodeId={location.id} getTicketsUrl={`api.${routeName}.tickets`} locationType={routeName} />
                <DocumentManager
                    itemCodeId={location.id}
                    getDocumentsUrl={`api.${routeName}.documents`}
                    editRoute={`api.documents.update`}
                    uploadRoute={`api.${routeName}.documents.post`}
                    deleteRoute={`api.documents.delete`}
                    showRoute={'api.documents.show'}
                />
                <PictureManager
                    itemCodeId={location.id}
                    getPicturesUrl={`api.${routeName}.pictures`}
                    uploadRoute={`api.${routeName}.pictures.post`}
                    deleteRoute={`api.pictures.delete`}
                    showRoute={'api.pictures.show'}
                />
            </div>
        </AppLayout>
    );
}
