import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Pill } from '@/components/ui/pill';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { BreadcrumbItem, Ticket } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useState } from 'react';

export default function IndexTickets() {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index tickets`,
            href: `/tickets`,
        },
    ];
    const [fetchTicketStatus, setFetchTicketStatus] = useState<null | 'open' | 'ongoing' | 'closed'>('open');
    const [fetchingData, setFetchingData] = useState<boolean>(true);
    const { showToast } = useToast();

    const fetchTickets = async () => {
        try {
            const response = await axios.get(route('api.tickets.index', { status: fetchTicketStatus }));
            setTickets(response.data.data);
            setFetchingData(false);
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const [tickets, setTickets] = useState<Ticket[]>([]);

    useEffect(() => {
        setFetchingData(true);
        fetchTickets();
    }, [fetchTicketStatus]);

    const changeStatusTicket = async (id: number, status: string) => {
        try {
            const response = await axios.patch(route('api.tickets.status', id), { status: status });
            if (response.data.status === 'success') {
                fetchTickets();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tickets" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="border-accent flex gap-10 border-b-2">
                    <ul className="flex pl-4">
                        <li
                            className={cn(
                                'cursor-pointer rounded-t-lg border-x-2 border-t-2 px-6 py-1',
                                fetchTicketStatus === null ? 'bg-primary text-background' : 'bg-secondary',
                            )}
                            onClick={() => {
                                setFetchTicketStatus(null);
                            }}
                        >
                            all
                        </li>

                        <li
                            className={cn(
                                'cursor-pointer rounded-t-lg border-x-2 border-t-2 px-6 py-1',
                                fetchTicketStatus === 'open' ? 'bg-primary text-background' : 'bg-secondary',
                            )}
                            onClick={() => {
                                setFetchTicketStatus('open');
                            }}
                        >
                            open
                        </li>
                        <li
                            className={cn(
                                'cursor-pointer rounded-t-lg border-x-2 border-t-2 px-6 py-1',
                                fetchTicketStatus === 'ongoing' ? 'bg-primary text-background' : 'bg-secondary',
                            )}
                            onClick={() => {
                                setFetchTicketStatus('ongoing');
                            }}
                        >
                            ongoing
                        </li>
                        <li
                            className={cn(
                                'cursor-pointer rounded-t-lg border-x-2 border-t-2 px-6 py-1',
                                fetchTicketStatus === 'closed' ? 'bg-primary text-background' : 'bg-secondary',
                            )}
                            onClick={() => {
                                setFetchTicketStatus('closed');
                            }}
                        >
                            closed
                        </li>
                    </ul>
                </div>

                <div className="">
                    <h3 className="inline">Tickets {!fetchingData && `(${tickets?.length ?? 0})`}</h3>
                    {fetchingData && <p>Loading tickets...</p>}
                    {!fetchingData && tickets && tickets?.length > 0 && (
                        <Table>
                            <TableHead>
                                <TableHeadRow>
                                    <TableHeadData>Code</TableHeadData>
                                    <TableHeadData>Related to</TableHeadData>
                                    <TableHeadData>Status</TableHeadData>
                                    <TableHeadData>Reporter</TableHeadData>
                                    <TableHeadData>Description</TableHeadData>
                                    <TableHeadData>Created at</TableHeadData>
                                    <TableHeadData>Updated at</TableHeadData>
                                    <TableHeadData></TableHeadData>
                                </TableHeadRow>
                            </TableHead>
                            <TableBody>
                                {tickets?.map((ticket, index) => (
                                    <TableBodyRow key={index}>
                                        <TableBodyData>
                                            <a href={route('tenant.tickets.show', ticket.id)}>{ticket.code}</a>
                                        </TableBodyData>
                                        <TableBodyData>
                                            <a href={ticket.ticketable.location_route}>{ticket.asset_code}</a>
                                        </TableBodyData>
                                        <TableBodyData>
                                            <Pill variant={ticket.status}>{ticket.status}</Pill>
                                        </TableBodyData>
                                        <TableBodyData>{ticket.reporter?.full_name ?? ticket.reporter_email}</TableBodyData>
                                        <TableBodyData>{ticket.description}</TableBodyData>
                                        <TableBodyData>{ticket.created_at}</TableBodyData>
                                        <TableBodyData>{ticket.updated_at}</TableBodyData>

                                        <TableBodyData className='space-x-2'>
                                            {ticket.status == 'open' && (
                                                <Button variant={'green'} onClick={() => changeStatusTicket(ticket.id, 'ongoing')}>
                                                    Ongoing
                                                </Button>
                                            )}
                                            {ticket.status !== 'closed' && (
                                                <>
                                                    <Button variant={'destructive'} onClick={() => changeStatusTicket(ticket.id, 'closed')}>
                                                        Close
                                                    </Button>
                                                    {/* <a href={route('tenant.tickets.show', ticket.id)}>
                                                    <Button type="button">Show</Button>
                                                </a> */}
                                                </>
                                            )}
                                            {ticket.status === 'closed' && (
                                                <Button variant={'green'} onClick={() => changeStatusTicket(ticket.id, 'open')}>
                                                    Re-open
                                                </Button>
                                            )}
                                        </TableBodyData>
                                    </TableBodyRow>
                                ))}
                            </TableBody>
                        </Table>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
