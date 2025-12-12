import Modale from '@/components/Modale';
import ModaleForm from '@/components/ModaleForm';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/central/app-layout';
import { cn } from '@/lib/utils';
import { Tenant, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { CheckCircle, Globe, Loader } from 'lucide-react';
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
                                <div className="flex flex-col gap-2">
                                    <div className="flex gap-2">
                                        <a href={route('central.tenants.show', tenant.id)}>
                                            <p>{tenant.company_name}</p>
                                        </a>
                                        <CheckCircle className={tenant.verified_vat_status === 'verified' ? 'text-success' : 'text-destructive'} />

                                        {/* Stripe logo */}
                                        <svg
                                            width="800px"
                                            height="800px"
                                            viewBox="0 0 24 24"
                                            xmlns="http://www.w3.org/2000/svg"
                                            className={cn('h-6 w-6', tenant.stripe_id ? 'text-success' : 'text-destructive')}
                                        >
                                            <path
                                                fill="currentColor"
                                                fillRule="evenodd"
                                                d="M1,1 L23,1 L23,23 L1,23 L1,1 Z M11.1196337,9.18908425 C11.1196337,8.58622711 11.6142857,8.35435897 12.4335531,8.35435897 C13.6083516,8.35435897 15.0923077,8.70989011 16.2671062,9.343663 L16.2671062,5.71106227 C14.9841026,5.20095238 13.7165568,5 12.4335531,5 C9.2956044,5 7.20879121,6.6385348 7.20879121,9.37457875 C7.20879121,13.6409524 13.0827839,12.9608059 13.0827839,14.800293 C13.0827839,15.5113553 12.4644689,15.7432234 11.5988278,15.7432234 C10.3158242,15.7432234 8.67728938,15.2176557 7.37882784,14.5065934 L7.37882784,18.1855678 C8.81641026,18.8038828 10.2694505,19.0666667 11.5988278,19.0666667 C14.8140659,19.0666667 17.0245421,17.4745055 17.0245421,14.7075458 C17.0090842,10.1010989 11.1196337,10.9203663 11.1196337,9.18908425 L11.1196337,9.18908425 Z"
                                            />
                                        </svg>
                                    </div>
                                    <p>
                                        Subscription:{' '}
                                        {tenant.active_subscription
                                            ? `${tenant?.subscription_name ?? ''} (${tenant.active_subscription?.stripe_status})`
                                            : 'No subscription'}
                                    </p>
                                    <ul className="flex gap-2">
                                        <li>
                                            Sites: {tenant.current_sites_count} / <span className="font-bold">{tenant.max_sites}</span>
                                        </li>
                                        <span className="">|</span>
                                        <li>
                                            Users: {tenant.current_users_count} / <span className="font-bold">{tenant.max_users}</span>
                                        </li>
                                        <span className="">|</span>
                                        <li>
                                            Storage GB: {tenant.disk_size_gb} / <span className="font-bold">{tenant.max_storage_gb}</span>
                                        </li>
                                        <span className="">|</span>
                                        <li className="flex items-center gap-2">
                                            Statistics: <CheckCircle className={tenant.has_statistics ? 'text-success' : 'text-destructive'} />
                                        </li>
                                    </ul>
                                </div>
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

                                    <a href={tenant.domain_address} target="__blank">
                                        <Button variant={'outline'}>
                                            <Globe />
                                        </Button>
                                    </a>

                                    {/* Check if tenant has logged in, if yes, do not show this button */}
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
