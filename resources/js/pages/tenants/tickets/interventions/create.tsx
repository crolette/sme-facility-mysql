import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Ticket } from '@/types';
import { Head } from '@inertiajs/react';

export default function CreateIntervention({ ticket }: { ticket: Ticket }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create ticket`,
            href: `/tickets/create`,
        },
    ];

    return (
        <>
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Intervention Ticket" />
                <form action=""></form>
            </AppLayout>
        </>
    );
}
