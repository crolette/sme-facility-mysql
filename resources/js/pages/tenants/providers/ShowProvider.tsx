import ImageUploadModale from '@/components/ImageUploadModale';
import Modale from '@/components/Modale';
import { AssetManager } from '@/components/tenant/assetManager';
import { ContractsList } from '@/components/tenant/contractsList';
import { InterventionManager } from '@/components/tenant/interventionManager';
import { LocationList } from '@/components/tenant/LocationList';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import { UsersList } from '@/components/tenant/usersList';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import Field from '@/components/ui/field';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Provider } from '@/types';
import { router } from '@inertiajs/core';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Pencil, Trash, Trash2, Upload } from 'lucide-react';
import { useState } from 'react';

export default function ShowProvider({ item }: { item: Provider }) {
    const { t, tChoice } = useLaravelReactI18n();
    const { showToast } = useToast();
    const [provider, setProvider] = useState(item);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${tChoice('providers.title', 2)}`,
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

    const handleUploadSuccess = (result) => {
        fetchProvider();
    };

    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
    const [activeTab, setActiveTab] = useState('information');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={provider.name} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex gap-2">
                    <a href={route(`tenant.providers.edit`, provider.id)}>
                        <Button>
                            <Pencil />
                            {t('actions.edit')}
                        </Button>
                    </a>
                    <Button onClick={() => setShowDeleteModale(!showDeleteModale)} variant={'destructive'}>
                        <Trash2 />
                        {t('actions.delete')}
                    </Button>
                    <Button onClick={() => setIsModalOpen(true)} variant={'secondary'}>
                        <Upload size={20} />
                        {t('actions.upload-type', { type: t('common.logo') })}
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
                                <h2>{t('common.information')}</h2>
                                <div className="grid gap-4 sm:grid-cols-[1fr_160px]">
                                    <div className="space-y-2">
                                        <Field label={t('common.name')} text={provider.name} />
                                        <Field label={t('common.address')} text={provider.address} />
                                        <Field label={t('common.phone')} text={provider.phone_number} />
                                        <Field label={t('providers.vat_number')} text={provider.vat_number} />
                                        <Field label={t('common.email')} text={<a href={`mailto:${provider.email}`}>{provider.email}</a>} />
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
                            <InterventionManager itemCodeId={item.id} getInterventionsUrl={`api.providers.interventions`} type={'providers'} />
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
                                <h2>{tChoice('contacts.title', 2)}</h2>

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
                title={t('actions.delete-type', { type: tChoice('providers.title', 1) })}
                message={t(`providers.delete_description`, { name: provider.name })}
                isOpen={showDeleteModale}
                onConfirm={deleteProvider}
                onCancel={() => {
                    setShowDeleteModale(false);
                }}
            />
        </AppLayout>
    );
}
