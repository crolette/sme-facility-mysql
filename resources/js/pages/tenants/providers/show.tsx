import ImageUploadModale from '@/components/ImageUploadModale';
import Modale from '@/components/Modale';
import { ContractsList } from '@/components/tenant/contractsList';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import { UsersList } from '@/components/tenant/usersList';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Provider } from '@/types';
import { router } from '@inertiajs/core';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { Pencil, Trash, Trash2, Upload } from 'lucide-react';
import { useState } from 'react';

export default function ProviderShow({ item }: { item: Provider }) {
    const { showToast } = useToast();
    const [provider, setProvider] = useState(item);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${provider.name}`,
            href: `/providers/${provider.id}`,
        },
    ];

    const deleteProvider = async () => {
        try {
            const response = await axios.delete(route('api.providers.destroy', provider.id));
            console.log(response);
            if (response.data.status === 'success')
            {
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
        // Ajouter l'image uploadée à la liste
        // setUploadedImages((prev) => [...prev, result]);
        console.log('Image uploadée avec succès:', result);
        fetchProvider();
    };

    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
    const [activeTab, setActiveTab] = useState('information');
    
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sites" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className='flex gap-2'>
                    <a href={route(`tenant.providers.edit`, provider.id)}>
                        <Button>
                            <Pencil />
                            Edit</Button>
                    </a>
                    <Button onClick={() => setShowDeleteModale(!showDeleteModale)} variant={'destructive'}>
                        <Trash2/>
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
                                <div className="grid grid-cols-[1fr_160px] gap-4">
                                    <div>
                                        <p>Category : {provider.category}</p>
                                        <p>Name : {provider.name}</p>
                                        <p>Address : {provider.address}</p>
                                        <p>Phone number : {provider.phone_number}</p>
                                        <p>VAT Number : {provider.vat_number}</p>
                                        <p>
                                            Email :<a href={`mailto:${provider.email}`}>{provider.email}</a>
                                        </p>
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

                        {activeTab === 'contracts' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4">
                                <h2>Contracts</h2>
                                <ContractsList items={provider.contracts ?? []} getUrl="api.providers.contracts" routeName="providers" />
                            </div>
                        )}

                        {activeTab === 'users' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>Users</h2>

                                <UsersList items={provider.users} />
                            </div>
                        )}
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
