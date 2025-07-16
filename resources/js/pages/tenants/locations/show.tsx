import { DocumentManager } from '@/components/tenant/documentManager';

import { PictureManager } from '@/components/tenant/pictureManager';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { TenantSite, Ticket, type BreadcrumbItem } from '@/types';
import { Head, usePage } from '@inertiajs/react';
import axios from 'axios';
import { FormEventHandler, useState } from 'react';

type FormDataTicket = {
    ticket_id: number | null;
    location_type: string;
    location_id: number;
    description: string;
    reported_by: number;
    reporter_email: string;
    being_notified: boolean;
    pictures: File[];
};

export default function ShowLocation({ location, routeName }: { location: TenantSite; routeName: string }) {
    const auth = usePage().props.auth.user;
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${location.reference_code} - ${location.maintainable.name}`,
            href: ``,
        },
    ];

    const closeTicket = async (id: number) => {
        try {
            const response = await axios.patch(route('api.tickets.close', id));
            if (response.data.status === 'success') {
                fetchTickets();
            }
        } catch (error) {
            console.error('Erreur lors de la suppression', error);
        }
    };

    const fetchTickets = async () => {
        try {
            const response = await axios.get(`/api/v1/${routeName}/${location.id}/tickets`);
            console.log(response.data);
            setTickets(response.data.data);
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
        }
    };

    const [addTicketModal, setAddTicketModal] = useState<boolean>(false);
    const [submitTypeTicket, setSubmitTypeTicket] = useState<'edit' | 'new'>('edit');
    const updateTicketData = {
        ticket_id: null,
        location_type: routeName,
        location_id: location.id,
        being_notified: false,
        description: '',
        reported_by: auth.id,
        reporter_email: auth.email,
        pictures: [],
    };

    const submitEditTicket: FormEventHandler = async (e) => {
        e.preventDefault();
        console.log('submitEditTicket');
        if (newTicketData.ticket_id === null) return;

        try {
            const response = await axios.patch(route('api.tickets.update', newTicketData.ticket_id), newTicketData);
            console.log(response.data.status, response.data.message);
            fetchTickets();
            closeModalTicket();
            // }
        } catch (error) {
            console.log(error);
        }
    };

    const submitNewTicket: FormEventHandler = async (e) => {
        e.preventDefault();
        console.log('submitNewTicket');
        try {
            console.log('post');
            const response = await axios.post(route('api.tickets.store'), newTicketData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            console.log(response.data.status, response.data.message);
            if (response.data.status === 'success') {
                fetchTickets();
                closeModalTicket();
            }
        } catch (error) {
            console.log(error);
        }
    };

    const [newTicketData, setNewTicketData] = useState<FormDataTicket>(updateTicketData);

    const closeModalTicket = () => {
        setAddTicketModal(false);
        setNewTicketData(updateTicketData);
        setSubmitTypeTicket('edit');
    };

    const [tickets, setTickets] = useState<Ticket[]>(location.tickets);

    const editTicket = async (id: number) => {
        setSubmitTypeTicket('edit');
        try {
            console.log('post');
            const response = await axios.get(route('api.tickets.get', id), {});
            console.log(response.data.data);
            setNewTicketData((prev) => ({
                ...prev,
                ticket_id: response.data.data.id,
                description: response.data.data.description,
                being_notified: response.data?.data.being_notified,
            }));

            setAddTicketModal(true);
            console.log(newTicketData);

            // }
        } catch (error) {
            console.log(error);
        }
    };

    const addTicket = () => {
        return (
            <div className="bg-background/50 absolute inset-0 z-50">
                <div className="bg-background/20 flex h-dvh items-center justify-center">
                    <div className="bg-background flex items-center justify-center p-10">
                        <form onSubmit={submitTypeTicket === 'edit' ? submitEditTicket : submitNewTicket} className="flex flex-col gap-4">
                            <Input type="text" name="email" value={newTicketData.reporter_email} required disabled placeholder="Reporter email" />
                            <Textarea
                                name="description"
                                id="description"
                                required
                                minLength={10}
                                maxLength={250}
                                placeholder="Ticket description"
                                onChange={(e) =>
                                    setNewTicketData((prev) => ({
                                        ...prev,
                                        description: e.target.value,
                                    }))
                                }
                                value={newTicketData.description}
                            />
                            <Input
                                type="file"
                                multiple
                                accept="image/png, image/jpeg, image/jpg"
                                onChange={(e) => {
                                    // const pictures = { pictures: };
                                    setNewTicketData((prev) => ({
                                        ...prev,
                                        pictures: e.target.files,
                                    }));
                                }}
                            />
                            <div className="flex items-center gap-4">
                                <Label htmlFor="notified">Do you want to be notified of changes ? </Label>
                                <Checkbox
                                    id="notified"
                                    checked={newTicketData.being_notified}
                                    onClick={() => {
                                        setNewTicketData((prev) => ({
                                            ...prev,
                                            being_notified: !newTicketData.being_notified,
                                        }));
                                    }}
                                />
                            </div>
                            <Button>Add new ticket</Button>
                            <Button onClick={closeModalTicket} type="button" variant={'secondary'}>
                                Cancel
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />
            <div>
                <a href={route(`tenant.${routeName}.edit`, location.id)}>
                    <Button>Edit</Button>
                </a>
                <Button
                    onClick={() => {
                        setSubmitTypeTicket('new');
                        setAddTicketModal(!addTicketModal);
                    }}
                >
                    Add new ticket
                </Button>
            </div>
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {location.reference_code} - {location.code} - {location.location_type.label}
                <p>{location.maintainable?.name}</p>
                <p>{location.maintainable?.description}</p>
                <details open>
                    <summary className="">
                        <h3 className="inline">Tickets ({tickets?.length ?? 0})</h3>
                    </summary>
                    {tickets?.length > 0 && (
                        <Table>
                            <TableHead>
                                <TableHeadRow>
                                    <TableHeadData>Code</TableHeadData>
                                    <TableHeadData>Status</TableHeadData>
                                    <TableHeadData>Reporter</TableHeadData>
                                    <TableHeadData>Description</TableHeadData>
                                    <TableHeadData>Created at</TableHeadData>
                                    <TableHeadData>Updated at</TableHeadData>
                                    <TableHeadData></TableHeadData>
                                </TableHeadRow>
                            </TableHead>
                            <TableBody>
                                {tickets.map((ticket, index) => {
                                    return (
                                        <TableBodyRow key={index}>
                                            <TableBodyData>
                                                <a href={route('tenant.tickets.show', ticket.id)}>{ticket.code}</a>
                                            </TableBodyData>
                                            <TableBodyData>{ticket.status}</TableBodyData>
                                            <TableBodyData>{ticket.code}</TableBodyData>
                                            <TableBodyData>{ticket.description}</TableBodyData>
                                            <TableBodyData>{ticket.created_at}</TableBodyData>
                                            <TableBodyData>{ticket.updated_at}</TableBodyData>

                                            <TableBodyData>
                                                {ticket.status !== 'closed' && (
                                                    <>
                                                        <Button variant={'destructive'} onClick={() => closeTicket(ticket.id)}>
                                                            Close
                                                        </Button>

                                                        <Button onClick={() => editTicket(ticket.id)}>Edit</Button>
                                                    </>
                                                )}
                                            </TableBodyData>
                                        </TableBodyRow>
                                    );
                                })}
                            </TableBody>
                        </Table>
                    )}
                </details>
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

            {addTicketModal && addTicket()}
        </AppLayout>
    );
}
