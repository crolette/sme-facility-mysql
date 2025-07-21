import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Ticket } from '@/types';
import { Head } from '@inertiajs/react';

export default function CreateTicket({ ticket, statuses }: { ticket?: Ticket; statuses: string[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Edit ticket`,
            href: `/tickets/edit`,
        },
    ];

    return (
        <>
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Tickets" />
                <form action=""></form>
            </AppLayout>
        </>
    );
}
