import { Button } from '@/components/ui/button';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
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
    const fetchTickets = async () => {
        try {
            const response = await axios.get(route('api.tickets.all'));
            console.log(response.data);
            setTickets(response.data.data);
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
        }
    };

    const [tickets, setTickets] = useState<Ticket[]>();

    useEffect(() => {
        fetchTickets();
    }, []);

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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tickets" />
            <a href={route('tenant.tickets.create')}>
                <Button>Create ticket</Button>
            </a>
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
        </AppLayout>
    );
}
