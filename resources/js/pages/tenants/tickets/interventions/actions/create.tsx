import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Intervention } from '@/types';
import { Head } from '@inertiajs/react';

export default function CreateInterventionAction({ intervention }: { intervention: Intervention }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create action`,
            href: `/tickets/create`,
        },
    ];

    return (
        <>
            <AppLayout breadcrumbs={breadcrumbs}>
                <Head title="Intervention Action" />
                <form action=""></form>
            </AppLayout>
        </>
    );
}
