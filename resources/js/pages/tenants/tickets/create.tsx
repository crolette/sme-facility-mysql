import { Intervention, Ticket } from '@/types';
import { Head } from '@inertiajs/react';

export default function CreateTicket({ ticket, interventions }: { ticket?: Ticket; interventions: Intervention[] }) {
    console.log(ticket);
    console.log(interventions);

    return (
        <>
            <Head title="Tickets" />
            <form action=""></form>
        </>
    );
}
