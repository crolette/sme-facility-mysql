import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { Ticket } from '@/types';
import { usePage } from '@inertiajs/react';
import axios from 'axios';
import { Loader } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import ModaleForm from '../ModaleForm';
import { useToast } from '../ToastrContext';
import { Button } from '../ui/button';
import { Checkbox } from '../ui/checkbox';
import { Input } from '../ui/input';
import { Label } from '../ui/label';
import { Pill } from '../ui/pill';
import { Textarea } from '../ui/textarea';

interface TicketManagerProps {
    itemCode: string;
    getTicketsUrl: string;
    locationType: string;
    canAdd?: boolean;
}

type FormDataTicket = {
    ticket_id: number | null;
    location_type: string;
    location_code: string;
    description: string;
    reported_by: number;
    reporter_email: string;
    being_notified: boolean;
    pictures: File[];
};

export const TicketManager = ({ itemCode, getTicketsUrl, locationType, canAdd = true }: TicketManagerProps) => {
    const auth = usePage().props.auth.user;
    const { showToast } = useToast();

    const [tickets, setTickets] = useState<Ticket[]>();
    const [addTicketModal, setAddTicketModal] = useState<boolean>(false);
    const [submitTypeTicket, setSubmitTypeTicket] = useState<'edit' | 'new'>('edit');
    const [isProcessing, setIsProcessing] = useState(false);

    const updateTicketData = {
        ticket_id: null,
        location_type: locationType,
        location_code: itemCode,
        being_notified: false,
        description: '',
        reported_by: auth.id,
        reporter_email: auth.email,
        pictures: [],
    };

    const [newTicketData, setNewTicketData] = useState<FormDataTicket>(updateTicketData);

    useEffect(() => {
        fetchTickets();
    }, []);

    const fetchTickets = async () => {
        try {
            const response = await axios.get(route(getTicketsUrl, itemCode));
            if (response.data.status === 'success') {
                setTickets(await response.data.data);
            }
        } catch (error) {
            return;
        }
    };

    const closeTicket = async (id: number) => {
        try {
            const response = await axios.patch(route('api.tickets.status', id), { status: 'closed' });
            if (response.data.status === 'success') {
                fetchTickets();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const closeModalTicket = () => {
        setAddTicketModal(false);
        setNewTicketData(updateTicketData);
        setSubmitTypeTicket('edit');
        setIsProcessing(false);
    };

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

    const submitEditTicket: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);
        try {
            const response = await axios.patch(route('api.tickets.update', newTicketData.ticket_id), newTicketData);
            if (response.data.status === 'success') {
                fetchTickets();
                closeModalTicket();
                showToast(response.data.message, response.data.status);
            }
            // }
        } catch (error) {
            setIsProcessing(false);
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const submitNewTicket: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);
        try {
            const response = await axios.post(route('api.tickets.store'), newTicketData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            if (response.data.status === 'success') {
                fetchTickets();
                setIsProcessing(false);
                closeModalTicket();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            setIsProcessing(false);
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    return (
        <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
            <div className="flex justify-between">
                <h2 className="inline">Tickets ({tickets?.length ?? 0})</h2>
                {canAdd && (
                    <Button
                        className=""
                        onClick={() => {
                            setSubmitTypeTicket('new');
                            setAddTicketModal(!addTicketModal);
                        }}
                    >
                        Add new ticket
                    </Button>
                )}
            </div>

            {tickets && tickets?.length > 0 && (
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
                        {tickets?.map((ticket, index) => {
                            return (
                                <TableBodyRow key={index}>
                                    <TableBodyData>
                                        <a href={route('tenant.tickets.show', ticket.id)}>{ticket.code}</a>
                                    </TableBodyData>
                                    <TableBodyData>
                                        <Pill variant={ticket.status}>{ticket.status}</Pill>
                                    </TableBodyData>
                                    <TableBodyData>{ticket.reporter_email ?? ticket.reporter?.email}</TableBodyData>
                                    <TableBodyData>{ticket.description}</TableBodyData>
                                    <TableBodyData>{ticket.created_at}</TableBodyData>
                                    <TableBodyData>{ticket.updated_at !== ticket.created_at ? ticket.updated_at : '-'}</TableBodyData>

                                    <TableBodyData>
                                        {ticket.status !== 'closed' && (
                                            <>
                                                <Button variant={'destructive'} onClick={() => closeTicket(ticket.id)}>
                                                    Close
                                                </Button>

                                                {/* <Button onClick={() => editTicket(ticket.id)}>Edit</Button> */}
                                                {/* <a href={route('tenant.tickets.show', ticket.id)}>
                                                    <Button type="button">Show</Button>
                                                </a> */}
                                            </>
                                        )}
                                    </TableBodyData>
                                </TableBodyRow>
                            );
                        })}
                    </TableBody>
                </Table>
            )}

            {addTicketModal && (
                <ModaleForm title={'Add new ticket'}>
                    {isProcessing && (
                        <div className="flex flex-col items-center gap-4">
                            <Loader size={48} className="animate-pulse" />
                            <p className="mx-auto animate-pulse text-3xl font-bold">Processing...</p>
                            <p className="mx-auto">Ticket is being created...</p>
                        </div>
                    )}
                    {!isProcessing && (
                        <form onSubmit={submitTypeTicket === 'edit' ? submitEditTicket : submitNewTicket} className="flex flex-col gap-4">
                            <Label>E-mail</Label>
                            <Input type="text" name="email" value={newTicketData.reporter_email} required disabled placeholder="Reporter email" />
                            <Label>Description</Label>
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
                            {submitTypeTicket === 'new' && (
                                <>
                                    <Label>Pictures</Label>
                                    <Input
                                        type="file"
                                        multiple
                                        max={3}
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
                                        <div>
                                            <Label htmlFor="notified">Do you want to be notified of changes ? </Label>
                                            <p className="text-xs">You will receive an e-mail when the ticket is closed.</p>
                                        </div>
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
                                </>
                            )}
                            {submitTypeTicket === 'new' ? (
                                <Button disabled={isProcessing}>Add new ticket</Button>
                            ) : (
                                <Button disabled={isProcessing}>Edit ticket</Button>
                            )}

                            <Button onClick={closeModalTicket} type="button" variant={'secondary'}>
                                Cancel
                            </Button>
                        </form>
                    )}
                </ModaleForm>
            )}
        </div>
    );
};
