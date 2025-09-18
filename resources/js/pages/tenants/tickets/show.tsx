import { InterventionManager } from '@/components/tenant/interventionManager';
import { PictureManager } from '@/components/tenant/pictureManager';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Ticket } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';

export default function ShowTicket({ item }: { item: Ticket }) {
    const [ticket, setTicket] = useState(item);
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Show tickets`,
            href: `/tickets/${ticket}`,
        },
    ];

    const fetchTicket = async () => {
        try {
            const response = await axios.get(route(`api.tickets.get`, ticket.id));
            setTicket(response.data.data);
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

    const [activeTab, setActiveTab] = useState('information');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Ticket" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex gap-2">
                    {ticket.status !== 'closed' && (
                        <Button variant={'destructive'} onClick={() => changeStatusTicket(ticket.id, 'closed')}>
                            Close
                        </Button>
                    )}
                    {!ticket.ticketable.deleted_at && ticket.status === 'closed' && (
                        <Button variant={'green'} onClick={() => changeStatusTicket(ticket.id, 'open')}>
                            Re-open
                        </Button>
                    )}
                </div>
                <div className="grid max-w-full gap-4 lg:grid-cols-[1fr_4fr]">
                    <SidebarMenuAssetLocation
                        activeTab={activeTab}
                        setActiveTab={setActiveTab}
                        menu="ticket"
                        infos={{
                            name: ticket.code,
                            code: ticket.closed_at ?? ticket.created_at,
                            status: ticket.status,
                            levelPath: ticket.ticketable_route,
                            levelName: ticket.ticketable.name + ' - ' + ticket.ticketable.reference_code,
                        }}
                    />
                    <div className="overflow-hidden">
                        {activeTab === 'information' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>Ticket information</h2>
                                <div>
                                    <p>Code: {ticket.code}</p>
                                    <p>Creation date: {ticket.created_at}</p>
                                    <p>Status: {ticket.status}</p>
                                    <p>Description : {ticket.description}</p>
                                    <p>Reporter : {ticket.reporter ? ticket.reporter.full_name : ticket.reporter_email}</p>
                                    <p>Closer: {ticket.closer?.full_name}</p>
                                    <p>Asset/Location</p>
                                    <p>Code: {ticket.ticketable.code}</p>
                                    <p>Name: {ticket.ticketable.maintainable.name}</p>
                                    <p>Location: {ticket.ticketable.reference_code}</p>
                                </div>
                            </div>
                        )}

                        {/* <DocumentManager
                            itemCodeId={ticket.id}
                            getDocumentsUrl={`api.tickets.documents`}
                            editRoute={`api.documents.update`}
                            uploadRoute={`api.tickets.documents.post`}
                            deleteRoute={`api.documents.delete`}
                            showRoute={'api.documents.show'}
                        /> */}

                        {activeTab === 'pictures' && (
                            <PictureManager
                                itemCodeId={ticket.id}
                                getPicturesUrl={`api.tickets.pictures`}
                                uploadRoute={`api.tickets.pictures.post`}
                                deleteRoute={`api.pictures.delete`}
                                showRoute={'api.pictures.show'}
                                canAdd={ticket.status === 'closed' ? false : true}
                            />
                        )}
                        {activeTab === 'interventions' && (
                            <InterventionManager
                                itemCodeId={ticket.id}
                                getInterventionsUrl="api.tickets.interventions"
                                type="ticket"
                                closed={ticket.status === 'closed' ? true : false}
                            />
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
