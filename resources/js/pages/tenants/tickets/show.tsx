import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Ticket } from '@/types';
import { Head } from '@inertiajs/react';

export default function ShowTicket({ ticket }: { ticket: Ticket }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Show tickets`,
            href: `/tickets/${ticket}`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Ticket" />
            <a href={route('tenant.tickets.create')}>
                <Button>Create ticket</Button>
            </a>
        </AppLayout>
    );
}
