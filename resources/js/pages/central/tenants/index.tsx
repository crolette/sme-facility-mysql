import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/central/app-layout';
import { Tenant, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { useEffect } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Index tenants',
        href: '/tenants',
    },
];

export default function IndexTenants({ tenants }: { tenants: Tenant[] }) {
    const {
        data,
        setData,
        delete: destroy,
    } = useForm({
        tenant: '',
    });

    const submit = () => {
        destroy(route('central.tenants.delete', data.tenant));
    };

    useEffect(() => {
        if (data.tenant !== '') {
            submit();
        }
    }, [data.tenant]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route('central.tenants.create')}>
                    <Button>Create</Button>
                </a>
                <ul>
                    {tenants.length > 0 &&
                        tenants.map((tenant) => (
                            <li key={tenant.id}>
                                {tenant.company_name} - {tenant.email} - {tenant.domain.domain}
                                <Button onClick={() => setData('tenant', tenant.id)} variant={'destructive'}>
                                    Delete
                                </Button>
                                <a href={route('central.tenants.edit', tenant.id)}>
                                    <Button>Edit</Button>
                                </a>
                                <a href={route('central.tenants.show', tenant.id)}>
                                    <Button variant={'outline'}>See</Button>
                                </a>
                            </li>
                        ))}
                </ul>
            </div>
        </AppLayout>
    );
}
