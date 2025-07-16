
import { DocumentManager } from '@/components/tenant/documentManager';

import { PictureManager } from '@/components/tenant/pictureManager';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { Asset, Ticket, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { FormEventHandler, useState } from 'react';

export default function ShowAsset({ asset }: { asset: Asset }) {
    const auth = usePage().props.auth.user;
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${asset.reference_code} - ${asset.maintainable.name}`,
            href: ``,
        },
    ];

    const { post, delete: destroy } = useForm();

    const deleteAsset = (asset: Asset) => {
        destroy(route(`tenant.assets.destroy`, asset.code));
    };


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
            const response = await axios.get(`/api/v1/assets/${asset.code}/tickets`);
            setTickets(await response.data);
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
        }
    };

    const [addTicketModal, setAddTicketModal] = useState<boolean>(false);
    const [submitTypeTicket, setSubmitTypeTicket] = useState<'edit' | 'new'>('edit');
    const updateTicketData = {
        ticket_id: 0,
        location_type: 'assets',
        location_id: asset.id,
        being_notified: false,
        description: '',
        reported_by: auth.id,
        reporter_email: auth.email,
        pictures: [],
    };

    const submitEditTicket: FormEventHandler = async (e) => {
        e.preventDefault();
        try {
            const response = await axios.patch(route('api.tickets.update', newTicketData.ticket_id), newTicketData);
            fetchTickets();
            closeModalTicket();
            // }
        } catch (error) {
            console.log(error);
        }
    };

    const submitNewTicket: FormEventHandler = async (e) => {
        e.preventDefault();
        try {
            const response = await axios.post(route('api.tickets.store'), newTicketData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
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

    const [tickets, setTickets] = useState<Ticket[]>(asset.tickets);

    const editTicket = async (id: number) => {
        setSubmitTypeTicket('edit');
        try {
            const response = await axios.get(route('api.tickets.get', id), {});
            setNewTicketData((prev) => ({
                ...prev,
                ticket_id: response.data.data.id,
                description: response.data.data.description,
                being_notified: response.data?.data.being_notified,
            }));

            setAddTicketModal(true);

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

    const restoreAsset = (asset: Asset) => {
        post(route('api.tenant.assets.restore', asset.id));
    };

    const deleteDefinitelyAsset = (asset: Asset) => {
        destroy(route(`api.tenant.assets.force`, asset.id));
    };

    console.log(asset);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Asset ${asset.maintainable.name}`} />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div>
                    {asset.deleted_at ? (
                        <>
                            <Button onClick={() => restoreAsset(asset)} variant={'green'}>
                                Restore
                            </Button>
                            <Button onClick={() => deleteDefinitelyAsset(asset)} variant={'destructive'}>
                                Delete definitely
                            </Button>
                        </>
                    ) : (
                        <>
                            <a href={route(`tenant.assets.edit`, asset.code)}>
                                <Button>Edit</Button>
                            </a>
                            <Button onClick={() => deleteAsset(asset)} variant={'destructive'}>
                                Delete
                            </Button>
                            <Button
                                onClick={() => {
                                    setSubmitTypeTicket('new');
                                    setAddTicketModal(!addTicketModal);
                                }}
                            >
                                Add new ticket
                            </Button>
                        </>
                    )}

                    {/* <a href={route(`tenant.tickets.create`)}> */}

                    {/* </a> */}
                </div>
                <p>Code : {asset.code}</p>
                <p>Reference code : {asset.reference_code}</p>
                <p>Location : {asset.location.maintainable.description}</p>
                <p>Category : {asset.category}</p>
                <p>Name : {asset.maintainable?.name}</p>
                <p>Description : {asset.maintainable?.description}</p>
                <p>Purchase date : {asset.maintainable?.purchase_date}</p>
                <p>Purchase cost : {asset.maintainable?.purchase_cost}</p>
                <p>End warranty date : {asset.maintainable?.end_warranty_date}</p>
                <p>Brand : {asset.brand}</p>
                <p>Model : {asset.model}</p>
                <p>Serial number : {asset.serial_number}</p>

                <details>
                    <summary className="">
                        <h3 className="inline">Tickets ({tickets?.length ?? 0})</h3>
                    </summary>
                    {tickets.length > 0 && (
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
                                            <TableBodyData>{ticket.updated_at !== ticket.created_at ? ticket.updated_at : '-'}</TableBodyData>
                                            <TableBodyData>PICTURES</TableBodyData>

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

                <details>
                    <summary className="">
                        <h3 className="inline">Documents ({documents?.length ?? 0})</h3>
                        <Button onClick={() => addNewFile()}>Add new file</Button>
                    </summary>
                    {tickets.length > 0 && (
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
                                            <TableBodyData>{ticket.updated_at !== ticket.created_at ? ticket.updated_at : '-'}</TableBodyData>
                                            <TableBodyData>PICTURES</TableBodyData>

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
                    itemCodeId={asset.code}
                    getDocumentsUrl={`api.assets.documents`}
                    editRoute={`api.documents.update`}
                    uploadRoute={`api.assets.documents.post`}
                    deleteRoute={`api.documents.delete`}
                    showRoute={'api.documents.show'}
                />
                <PictureManager
                    itemCodeId={asset.code}
                    getPicturesUrl={`api.assets.pictures`}
                    uploadRoute={`api.assets.pictures.post`}
                    deleteRoute={`api.pictures.delete`}
                    showRoute={'api.pictures.show'}
                />
            </div>

            {/* {addPictures && addNewPicturesModal()} */}
            {addTicketModal && addTicket()}
        </AppLayout>
    );
}
