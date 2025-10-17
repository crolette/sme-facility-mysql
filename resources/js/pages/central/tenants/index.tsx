import Modale from '@/components/Modale';
import ModaleForm from '@/components/ModaleForm';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/central/app-layout';
import { Tenant, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { Loader } from 'lucide-react';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Index tenants',
        href: '/tenants',
    },
];

interface TypeFormData {
    tenant: string | null;
    [key: string]: any;
}

export default function IndexTenants({ items }: { items: Tenant[] }) {
    const { showToast } = useToast();
    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
    const [tenantToDelete, setTenantToDelete] = useState<Tenant | null>(null);
    const [isProcessing, setIsProcessing] = useState<boolean>(false);
    const [tenants, setTenants] = useState(items);

    const fetchTenants = async () => {
        try {
            const response = await axios.get(route('api.central.tenants.index'));
            if (response.data.type === 'success') {
                setTenants(response.data.data);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.type);
        }
    };

    const { data, setData } = useForm<TypeFormData>({
        tenant: null,
    });

    const deleteTenant = async () => {
        if (!data.tenant) return;
        setIsProcessing(true);
        try {
            const response = await axios.delete(route('central.tenants.delete', data.tenant));
            if (response.data.type === 'success') {
                setTenantToDelete(null);
                setShowDeleteModale(false);
                showToast(response.data.message, response.data.type);
                fetchTenants();
                setIsProcessing(false);
            }
        } catch (error) {
            setTenantToDelete(null);
            showToast(error.response.data.message, error.response.data.type);
            setIsProcessing(false);
            setShowDeleteModale(false);
        }
    };

    // FIXME remove when on cloud server as it is only used on mutual server as DB creation is not automatic
    const sendTenantNotif = async (id: string) => {
        if (!id) return;

        try {
            const response = await axios.post(route('send-notif-tenant-admin', id), { tenant: id });
            if (response.data.type === 'success') {
                showToast(response.data.message, response.data.type);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.type);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route('central.tenants.create')}>
                    <Button>Create</Button>
                </a>
                <ul className="space-y-4">
                    {tenants.length > 0 &&
                        tenants.map((tenant) => (
                            <li key={tenant.id} className="flex items-center justify-between gap-4">
                                <p>{tenant.company_name}</p>
                                <div className="flex gap-2">
                                    <Button
                                        onClick={() => {
                                            setData('tenant', tenant.id);
                                            setTenantToDelete(tenant);
                                            setShowDeleteModale(true);
                                        }}
                                        variant={'destructive'}
                                    >
                                        Delete
                                    </Button>
                                    <a href={route('central.tenants.edit', tenant.id)}>
                                        <Button>Edit</Button>
                                    </a>
                                    <a href={route('central.tenants.show', tenant.id)}>
                                        <Button variant={'outline'}>See</Button>
                                    </a>
                                    <a href={tenant.domain_address} target="__blank">
                                        <Button variant={'outline'}>Access domain</Button>
                                    </a>

                                    <Button variant={'outline'} onClick={() => sendTenantNotif(tenant.id)}>
                                        Send notification to admin
                                    </Button>
                                </div>
                            </li>
                        ))}
                </ul>
            </div>

            <Modale
                title={'Delete tenant'}
                message={`Are you sure you want to delete this tenant : ${tenantToDelete?.company_name} ? `}
                isOpen={showDeleteModale}
                onConfirm={deleteTenant}
                onCancel={() => {
                    setTenantToDelete(null);
                    setShowDeleteModale(false);
                    setIsProcessing(false);
                }}
            />
            {isProcessing && (
                <ModaleForm>
                    <div className="flex flex-col items-center gap-4">
                        <Loader size={48} className="animate-pulse" />
                        <p className="mx-auto animate-pulse text-3xl font-bold">Processing...</p>
                        <p className="mx-auto">Tenant is being created...</p>
                    </div>
                </ModaleForm>
            )}
        </AppLayout>
    );
}
