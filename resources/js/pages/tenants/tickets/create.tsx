import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function CreateTicket({ statuses }: { statuses: string[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create ticket`,
            href: `/tickets/create`,
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
