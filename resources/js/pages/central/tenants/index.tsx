import Modale from '@/components/Modale';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/central/app-layout';
import { Tenant, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { Loader } from 'lucide-react';
import { useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Index tenants',
        href: '/tenants',
    },
];

interface TypeFormData {
    tenant: string | null;
    [key: string]: any
}

export default function IndexTenants({ tenants }: { tenants: Tenant[] }) {
    const { showToast } = useToast();
        const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
    const [tenantToDelete, setTenantToDelete] = useState<Tenant | null>(null);
    const [isProcessing, setIsProcessing] = useState<boolean>(false);

    const {
        data,
        setData,
    } = useForm<TypeFormData>({
        tenant: null,
    });

    const deleteTenant = async () => {
        if (!data.tenant)
            return;
        setIsProcessing(true);
            try {
                const response = await axios.delete(route('central.tenants.delete', data.tenant));
                if (response.data.type === 'success') {
                    setTenantToDelete(null);
                    setShowDeleteModale(false);
                    showToast(response.data.message, response.data.type)
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
        
        if (!id)
            return;

        try {
            const response = await axios.post(route('send-notif-tenant-admin', id), {tenant: id});
            if (response.data.type === 'success') {
                showToast(response.data.message, response.data.type);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.type);
        }
    }
    
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route('central.tenants.create')}>
                    <Button>Create</Button>
                </a>
                <ul className='space-y-4'>
                    {tenants.length > 0 &&
                        tenants.map((tenant) => (
                            <li key={tenant.id} className="flex items-center justify-between gap-4">
                                <p>
                                    {tenant.company_name} - {tenant.email} - {tenant.domain.domain}
                                </p>
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
                <div className="bg-background/50 fixed inset-0 z-50">
                    <div className="bg-background/20 flex h-dvh items-center justify-center">
                        <div className="bg-background flex items-center justify-center p-4 text-center md:w-1/3">
                            <div className="flex flex-col items-center gap-4">
                                <Loader size={48} className="animate-pulse" />
                                <p className="mx-auto animate-pulse text-3xl font-bold">Processing...</p>
                                <p className="mx-auto">Tenant is being created...</p>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
