import AppLayout from '@/layouts/central/app-layout';
import { Tenant, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { CheckCircle } from 'lucide-react';

export default function ShowTenant({ tenant }: { tenant: Tenant }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Tenant index`,
            href: `/tenants/`,
        },
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
                        {tenant.company_name} - {tenant.email} - {tenant.phone_number} - {tenant.company_code} -
                        <a href={tenant.domain_address}>{tenant.domain_address}</a>
                        <ul>
                            <li key={tenant.domain.id}>Domain name: {tenant.domain.domain}</li>
                            <li key="1">Stripe ID : {tenant.stripe_id ?? 'Not as customer in Stripe'}</li>
                            <li key="2" className="flex gap-2">
                                VAT Number : {tenant.vat_number}{' '}
                                <CheckCircle className={tenant.verified_vat_status === 'verified' ? 'text-success' : 'text-destructive'} />
                            </li>
                            <li key="3">Trial ends at : {tenant.trial_ends_at}</li>
                            <li key="3">Company address : {tenant.full_company_address}</li>
                            <li key="4">Invoice address : {tenant.full_invoice_address ?? 'Same as company address'}</li>
                        </ul>
                    </li>
                </ul>
            </div>
        </AppLayout>
    );
}
