import { InterventionManager } from '@/components/tenant/interventionManager';
import { PictureManager } from '@/components/tenant/pictureManager';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Ticket } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';

export default function ShowTicket({ ticket }: { ticket: Ticket }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Show tickets`,
            href: `/tickets/${ticket}`,
        },
    ];

    const [ticketItem, setTicketItem] = useState(ticket);

    const fetchTicket = async () => {
        try {
            const response = await axios.get(route(`api.tickets.get`, ticket.id));
            setTicketItem(response.data.data);
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
        }
    };

    const changeStatusTicket = async (id: number, status: string) => {
        try {
            const response = await axios.patch(route('api.tickets.status', id), { status: status });
            if (response.data.status === 'success') {
                fetchTicket();
            }
        } catch (error) {
            console.error('Erreur lors de la suppression', error);
        }
    };

    function formatUrlAsset(assetType: string) {
        const type = ticket.ticketable_type.split(`\\`)[3].toLowerCase();

        return `tenant.${type}s.show`;
    }

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Ticket" />
            <div>
                <a href={route(formatUrlAsset(ticket.ticketable_type), ticketItem.ticketable.reference_code)}>
                    <Button type="button">Show asset</Button>
                </a>
                {ticketItem.status !== 'closed' && (
                    <Button variant={'destructive'} onClick={() => changeStatusTicket(ticketItem.id, 'closed')}>
                        Close
                    </Button>
                )}
                {!ticketItem.ticketable.deleted_at && ticketItem.status === 'closed' && (
                    <Button variant={'green'} onClick={() => changeStatusTicket(ticketItem.id, 'open')}>
                        Re-open
                    </Button>
                )}
            </div>

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div>
                    <p>Code: {ticketItem.code}</p>
                    <p>Creation date: {ticketItem.created_at}</p>
                    <p>Status: {ticketItem.status}</p>
                    <p>Description : {ticketItem.description}</p>
                    <p>Reporter : {ticketItem.reporter ? ticketItem.reporter.full_name : ticketItem.reporter_email}</p>
                    <p>Closer: {ticketItem.closer?.full_name}</p>
                    <p>Asset/Location</p>
                    <p>Code: {ticketItem.ticketable.code}</p>
                    <p>Name: {ticketItem.ticketable.maintainable.name}</p>
                    <p>Location: {ticketItem.ticketable.reference_code}</p>
                </div>
                {/* <DocumentManager
                    itemCodeId={ticket.id}
                    getDocumentsUrl={`api.tickets.documents`}
                    editRoute={`api.documents.update`}
                    uploadRoute={`api.tickets.documents.post`}
                    deleteRoute={`api.documents.delete`}
                    showRoute={'api.documents.show'}
                /> */}

                <PictureManager
                    itemCodeId={ticket.id}
                    getPicturesUrl={`api.tickets.pictures`}
                    uploadRoute={`api.tickets.pictures.post`}
                    deleteRoute={`api.pictures.delete`}
                    showRoute={'api.pictures.show'}
                    canAdd={ticketItem.status === 'closed' ? false : true}
                />

                <InterventionManager
                    itemCodeId={ticket.id}
                    getInterventionsUrl="api.tickets.interventions"
                    type="ticket"
                    closed={ticketItem.status === 'closed' ? true : false}
                />
            </div>
        </AppLayout>
    );
}
