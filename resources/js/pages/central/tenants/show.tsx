import AppLayout from '@/layouts/central/app-layout';
import { Tenant, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function ShowTenant({ tenant }: { tenant: Tenant }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Tenant ${tenant.company_name}`,
            href: `/tenants/${tenant.id}`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <ul>
                    <li key={tenant.id}>
                        {tenant.company_name} - {tenant.email} - {tenant.phone_number} - {tenant.company_code}
                        <ul>
                            <li key={tenant.domain.id}>Domain name: {tenant.domain.domain}</li>
                            <li key="99">Company address : {tenant.full_company_address}</li>
                            <li key="88">Invoice address : {tenant.full_invoice_address ?? 'Same as company address'}</li>
                        </ul>
                        {/* <Button onClick={() => setData('tenant', tenant.id)} variant={'destructive'}>
                                    Delete
                                </Button> */}
                    </li>
                </ul>
            </div>
        </AppLayout>
    );
}
