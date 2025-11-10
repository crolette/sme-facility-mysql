import ImageUploadModale from '@/components/ImageUploadModale';
import Modale from '@/components/Modale';
import { AssetManager } from '@/components/tenant/assetManager';
import { ContractsList } from '@/components/tenant/contractsList';
import { LocationList } from '@/components/tenant/LocationList';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import { UsersList } from '@/components/tenant/usersList';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import Field from '@/components/ui/field';
import { Pill } from '@/components/ui/pill';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Intervention, Provider } from '@/types';
import { router } from '@inertiajs/core';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { Pencil, Trash, Trash2, Upload } from 'lucide-react';
import { useEffect, useState } from 'react';

export default function ShowProvider({ item }: { item: Provider }) {
    const { showToast } = useToast();
    const [provider, setProvider] = useState(item);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index providers`,
            href: `/providers`,
        },
        {
            title: `${provider.name}`,
            href: `/providers/${provider.id}`,
        },
    ];

    const deleteProvider = async () => {
        try {
            const response = await axios.delete(route('api.providers.destroy', provider.id));
            if (response.data.status === 'success') {
                router.get(route('tenant.providers.index'));
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const fetchProvider = async () => {
        try {
            const response = await axios.get(route('api.providers.show', provider.id));
            if (response.data.status === 'success') {
                setProvider(response.data.data);
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const deleteLogo = async () => {
        try {
            const response = await axios.delete(route('api.providers.logo.destroy', provider.id));
            if (response.data.status === 'success') {
                setProvider(response.data.data);
                fetchProvider();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const [isModalOpen, setIsModalOpen] = useState(false);
    // const [uploadedImages, setUploadedImages] = useState([]);

    const handleUploadSuccess = (result) => {
        fetchProvider();
    };

    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
    const [activeTab, setActiveTab] = useState('information');

    const [assets, setAssets] = useState();

    const fetchAssets = async () => {
        try {
            const response = await axios.get(route('api.providers.assets', provider.id));
            if (response.data.status === 'success') {
                setAssets(response.data.data);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    useEffect(() => {
        if (activeTab === 'assets') fetchAssets();
    }, [activeTab]);

    console.log(assets);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={provider.name} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex gap-2">
                    <a href={route(`tenant.providers.edit`, provider.id)}>
                        <Button>
                            <Pencil />
                            Edit
                        </Button>
                    </a>
                    <Button onClick={() => setShowDeleteModale(!showDeleteModale)} variant={'destructive'}>
                        <Trash2 />
                        Delete
                    </Button>
                    <Button onClick={() => setIsModalOpen(true)} variant={'secondary'}>
                        <Upload size={20} />
                        Uploader un logo
                    </Button>
                </div>
                <div className="grid max-w-full gap-4 lg:grid-cols-[1fr_4fr]">
                    <SidebarMenuAssetLocation
                        activeTab={activeTab}
                        setActiveTab={setActiveTab}
                        menu="provider"
                        infos={{
                            name: provider.name,
                            code: provider.category,
                            levelPath: provider.website,
                            levelName: provider.website,
                        }}
                    />
                    <div className="overflow-hidden">
                        {activeTab === 'information' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>Provider information</h2>
                                <div className="grid gap-4 sm:grid-cols-[1fr_160px]">
                                    <div className="space-y-2">
                                        <Field label={'Name'} text={provider.name} />
                                        <Field label={'Address'} text={provider.address} />
                                        <Field label={'Phone number'} text={provider.phone_number} />
                                        <Field label={'VAT Number'} text={provider.vat_number} />
                                        <Field label={'Email'} text={<a href={`mailto:${provider.email}`}>{provider.email}</a>} />
                                    </div>
                                    <div className="shrink-1">
                                        {provider.logo && (
                                            <div className="relative w-fit">
                                                <img
                                                    src={route('api.image.show', { path: provider.logo })}
                                                    alt=""
                                                    className="h-40 w-40 rounded-full object-cover"
                                                />
                                                <Button type="button" onClick={deleteLogo} variant={'destructive'} className="absolute top-2 right-2">
                                                    <Trash></Trash>
                                                </Button>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}

                        {activeTab === 'interventions' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h3>Interventions</h3>

                                {item.assigned_interventions ? (
                                    <div>
                                        {item.assigned_interventions.map((intervention: Intervention) => (
                                            <div>
                                                <Table key={intervention.id} className="table-fixed">
                                                    <TableHead>
                                                        <TableHeadRow>
                                                            <TableHeadData className="">Description</TableHeadData>
                                                            <TableHeadData>Type</TableHeadData>
                                                            <TableHeadData>Priority</TableHeadData>
                                                            <TableHeadData>Status</TableHeadData>
                                                            <TableHeadData>Assigned to</TableHeadData>
                                                            <TableHeadData>Planned at</TableHeadData>
                                                            <TableHeadData>Repair delay</TableHeadData>
                                                            <TableHeadData>Total costs</TableHeadData>
                                                        </TableHeadRow>
                                                    </TableHead>

                                                    <TableBody>
                                                        <TableBodyRow className="">
                                                            <TableBodyData className="flex max-w-72">
                                                                <a
                                                                    className="overflow-hidden overflow-ellipsis whitespace-nowrap"
                                                                    href={route('tenant.interventions.show', intervention.id)}
                                                                >
                                                                    {intervention.description}
                                                                </a>
                                                                <p className="tooltip tooltip-top">{intervention.description}</p>
                                                            </TableBodyData>
                                                            <TableBodyData>{intervention.type}</TableBodyData>
                                                            <TableBodyData>
                                                                <Pill variant={intervention.priority}>{intervention.priority}</Pill>
                                                            </TableBodyData>
                                                            <TableBodyData>{intervention.status}</TableBodyData>
                                                            <TableBodyData>
                                                                {intervention.assignable ? (
                                                                    intervention.assignable.full_name ? (
                                                                        <a href={route('tenant.users.show', intervention.assignable.id)}>
                                                                            {intervention.assignable.full_name}
                                                                        </a>
                                                                    ) : (
                                                                        <a href={route('tenant.providers.show', intervention.assignable.id)}>
                                                                            {intervention.assignable.name}
                                                                        </a>
                                                                    )
                                                                ) : (
                                                                    'not assigned'
                                                                )}
                                                            </TableBodyData>
                                                            <TableBodyData>{intervention.planned_at ?? 'Not planned'}</TableBodyData>
                                                            <TableBodyData>{intervention.repair_delay ?? 'No repair delay'}</TableBodyData>
                                                            <TableBodyData>
                                                                {intervention.total_costs ? `${intervention.total_costs} €` : '-'}
                                                            </TableBodyData>
                                                        </TableBodyRow>
                                                    </TableBody>
                                                </Table>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p>No interventions</p>
                                )}
                            </div>
                        )}

                        {activeTab === 'contracts' && (
                            <ContractsList
                                getUrl="api.providers.contracts"
                                routeName="providers"
                                parameter="provider"
                                contractableReference={provider.id}
                            />
                        )}

                        {activeTab === 'users' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>Users</h2>

                                <UsersList items={provider.users} />
                            </div>
                        )}
                        {activeTab === 'assets' && <AssetManager itemCode={provider.id} type={'providers'} />}
                        {activeTab === 'locations' && <LocationList itemCode={provider.id} type={'providers'} getUrl={'api.providers.locations'} />}
                    </div>
                    <ImageUploadModale
                        isOpen={isModalOpen}
                        onClose={() => setIsModalOpen(false)}
                        uploadUrl={route('api.providers.logo.store', provider.id)}
                        onUploadSuccess={handleUploadSuccess}
                    />
                </div>
            </div>
            <Modale
                title={'Delete provider'}
                message={`Are you sure you want to delete this provider ${provider.name} ?`}
                isOpen={showDeleteModale}
                onConfirm={deleteProvider}
                onCancel={() => {
                    setShowDeleteModale(false);
                }}
            />
        </AppLayout>
    );
}
