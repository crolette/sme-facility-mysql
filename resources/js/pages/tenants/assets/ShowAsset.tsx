import { ContractsList } from '@/components/tenant/contractsList';
import { DocumentManager } from '@/components/tenant/documentManager';
import { InterventionManager } from '@/components/tenant/interventionManager';
import { PictureManager } from '@/components/tenant/pictureManager';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import { TicketManager } from '@/components/tenant/ticketManager';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import Field from '@/components/ui/field';
import { usePermissions } from '@/hooks/usePermissions';
import AppLayout from '@/layouts/app-layout';
import { Asset, Contract, type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/core';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ArchiveRestore, CircleCheckBig, Pencil, QrCode, Shredder, Trash2 } from 'lucide-react';
import { useState } from 'react';

export default function ShowAsset({ item }: { item: Asset }) {
    const { t, tChoice } = useLaravelReactI18n();
    const { hasPermission } = usePermissions();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${tChoice('assets.title', 2)}`,
            href: `/assets`,
        },
        {
            title: `${item.reference_code} - ${item.maintainable.name}`,
            href: ``,
        },
    ];

    const { showToast } = useToast();

    const [asset, setAsset] = useState(item);

    const fetchAsset = async () => {
        const response = await axios.get(route('api.assets.show', asset.reference_code));
        if (response.data.status === 'success') setAsset(response.data.data);
    };

    const updateContracts = (newContracts: Contract[]) => {
        setAsset((prev) => ({ ...prev, contracts: newContracts }));
        setExistingContracts(newContracts);
    };

    const deleteAsset = async (asset: Asset) => {
        try {
            const response = await axios.delete(route(`api.assets.destroy`, asset.reference_code));
            router.visit(route(`tenant.assets.index`), {
                preserveScroll: false,
            });
            showToast(response.data.message, response.data.status);
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const restoreAsset = async (asset: Asset) => {
        try {
            const response = await axios.delete(route(`api.tenant.assets.restore`, asset.reference_code));

            showToast(response.data.message, response.data.status);
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const deleteDefinitelyAsset = async (asset: Asset) => {
        try {
            const response = await axios.delete(route(`api.tenant.assets.force`, asset.reference_code));

            showToast(response.data.message, response.data.status);
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const generateQR = async () => {
        try {
            const response = await axios.post(route('api.assets.qr.regen', asset.reference_code));
            if (response.data.status === 'success') {
                fetchAsset();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const markMaintenanceDone = async () => {
        try {
            const response = await axios.patch(route('api.maintenance.done', asset.maintainable.id));
            if (response.data.status === 'success') {
                fetchAsset();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const [activeTab, setActiveTab] = useState('information');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={item.name + ' - ' + item.code} />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex flex-wrap items-center gap-4">
                    {asset.deleted_at ? (
                        <>
                            {hasPermission('restore assets') && (
                                <Button onClick={() => restoreAsset(asset)} variant={'green'}>
                                    <ArchiveRestore />
                                    {t('actions.restore')}
                                </Button>
                            )}
                            {hasPermission('force delete assets') && (
                                <Button onClick={() => deleteDefinitelyAsset(asset)} variant={'destructive'}>
                                    <Shredder />
                                    {t('actions.delete_definitely')}
                                </Button>
                            )}
                        </>
                    ) : (
                        <>
                            {hasPermission('update assets') && (
                                <>
                                    <a href={route(`tenant.assets.edit`, asset.reference_code)}>
                                        <Button>
                                            <Pencil />
                                            {t('actions.edit')}
                                        </Button>
                                    </a>
                                    {asset.maintainable.need_maintenance && (
                                        <Button onClick={() => markMaintenanceDone()} variant={'green'}>
                                            <CircleCheckBig />
                                            {t('maintenances.mark_done')}
                                        </Button>
                                    )}
                                </>
                            )}
                            {hasPermission('delete assets') && (
                                <Button onClick={() => deleteAsset(asset)} variant={'destructive'}>
                                    <Trash2 />
                                    {t('actions.delete')}
                                </Button>
                            )}
                        </>
                    )}
                    <Button onClick={generateQR} variant={'secondary'}>
                        <QrCode />
                        {t('actions.generate_qr')}
                    </Button>
                </div>

                <div className="grid max-w-full gap-4 lg:grid-cols-[1fr_6fr]">
                    <SidebarMenuAssetLocation
                        activeTab={activeTab}
                        setActiveTab={setActiveTab}
                        menu="asset"
                        infos={{
                            name: asset.name,
                            code: asset.code,
                            reference: asset.reference_code,
                            levelPath: asset.level_path,
                            levelName: asset.is_mobile ? asset.location.full_name : asset.location.name,
                        }}
                    />
                    <div className="overflow-hidden">
                        {activeTab === 'information' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>{t('common.information')}</h2>
                                <div className="grid grid-cols-[1fr_160px] gap-4">
                                    <div className="space-y-2">
                                        <Field label={t('common.name')} text={asset.name} />
                                        <Field label={t('common.category')} text={asset.category} />
                                        <Field label={t('common.description')} text={asset.description} />
                                        <div className="flex flex-wrap gap-4">
                                            {asset.brand && <Field label={t('assets.brand')} text={asset.brand} />}
                                            {asset.model && <Field label={t('assets.model')} text={asset.model} />}
                                            {asset.serial_number && <Field label={t('assets.serial_number')} text={asset.serial_number} />}
                                        </div>
                                        {asset.surface && <Field label={t('common.surface')} text={asset.surface + ' m²'} />}
                                    </div>
                                    <div className="mx-auto h-fit shrink-1 bg-white">
                                        {asset.qr_code && (
                                            <a href={route('api.file.download', { path: asset.qr_code })} download className="w-fit cursor-pointer">
                                                <img
                                                    key={asset.qr_code}
                                                    src={route('api.image.show', { path: asset.qr_code })}
                                                    alt=""
                                                    className="h-32 w-auto"
                                                />
                                            </a>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}

                        {activeTab === 'maintenance' && (
                            <>
                                <div className="border-sidebar-border bg-sidebar rounded-md border p-4">
                                    <h2>{tChoice('maintenances.title', 1)}</h2>
                                    <div className="space-y-2">
                                        <Field
                                            label={t('maintenances.maintenance_manager')}
                                            text={
                                                asset.maintainable.manager ? (
                                                    <a href={route('tenant.users.show', asset.maintainable.manager.id)}>
                                                        {' '}
                                                        {asset.maintainable.manager.full_name}
                                                    </a>
                                                ) : (
                                                    t('maintenances.no_manager')
                                                )
                                            }
                                        />
                                        {asset.maintainable.need_maintenance && (
                                            <>
                                                <Field
                                                    label={t('maintenances.frequency')}
                                                    text={t(`maintenances.frequency.${asset.maintainable.maintenance_frequency}`)}
                                                />
                                                <Field
                                                    label={t('maintenances.next_maintenance_date')}
                                                    date
                                                    text={asset.maintainable.next_maintenance_date ?? t('maintenances.none')}
                                                />
                                                <Field
                                                    label={t('maintenances.last_maintenance_date')}
                                                    date
                                                    text={asset.maintainable.last_maintenance_date}
                                                />
                                            </>
                                        )}
                                    </div>
                                </div>

                                {asset.depreciable && (
                                    <div className="border-sidebar-border bg-sidebar mt-4 rounded-md border p-4">
                                        <h2>{t('assets.depreciation')}</h2>
                                        <div className="space-y-2">
                                            <Field
                                                label={t('assets.depreciation_duration')}
                                                text={asset.depreciation_duration + ' ' + tChoice('common.years', asset.depreciation_duration)}
                                            />
                                            <Field label={t('assets.depreciation_start_date')} date text={asset.depreciation_start_date} />
                                            <Field label={t('assets.depreciation_end_date')} date text={asset.depreciation_end_date} />
                                            <Field
                                                label={t('assets.residual_value')}
                                                text={asset.residual_value ? asset.residual_value + ' €' : 'NC'}
                                            />
                                        </div>
                                    </div>
                                )}
                            </>
                        )}

                        {activeTab === 'warranty' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4">
                                <h2>{t('assets.purchase_warranty')}</h2>
                                <div className="space-y-2">
                                    <Field label={t('assets.purchase_date')} date text={asset.maintainable.purchase_date} />
                                    <Field
                                        label={t('assets.purchase_cost')}
                                        text={asset.maintainable.purchase_cost ? asset.maintainable.purchase_cost + ' €' : 'NC'}
                                    />
                                    {asset.maintainable.under_warranty && (
                                        <Field label={t('assets.warranty_end_date')} date text={asset.maintainable.end_warranty_date} />
                                    )}
                                </div>
                            </div>
                        )}

                        {activeTab === 'contracts' && (
                            <ContractsList
                                // items={asset.contracts}
                                contractableReference={asset.reference_code}
                                getUrl="api.assets.contracts"
                                routeName="assets"
                                parameter="asset"
                                removable
                                // onContractsChange={updateContracts}
                            />
                        )}

                        {activeTab === 'providers' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4">
                                <h2>{tChoice('providers.title', 2)}</h2>
                                <div className="space-y-2">
                                    <ul>
                                        {asset.maintainable.providers?.map((provider, index) => (
                                            <li key={index}>
                                                <Field
                                                    label={tChoice('providers.title', 1)}
                                                    text={<a href={route('tenant.providers.show', provider.id)}>{provider.name}</a>}
                                                />
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </div>
                        )}

                        {activeTab === 'interventions' && (
                            <InterventionManager
                                itemCodeId={asset.reference_code}
                                getInterventionsUrl="api.assets.interventions"
                                type="asset"
                                closed={asset.deleted_at == null ? false : true}
                            />
                        )}
                        {activeTab === 'tickets' && (
                            <TicketManager
                                itemCode={asset.reference_code}
                                getTicketsUrl={`api.assets.tickets`}
                                locationType="assets"
                                canAdd={asset.deleted_at == null ? true : false}
                            />
                        )}
                        {activeTab === 'documents' && (
                            <>
                                <DocumentManager
                                    itemCodeId={asset.reference_code}
                                    getDocumentsUrl={`api.assets.documents`}
                                    removableRoute={`api.assets.documents.detach`}
                                    editRoute={`api.documents.update`}
                                    uploadRoute={`api.assets.documents.post`}
                                    deleteRoute={`api.documents.delete`}
                                    showRoute={'api.documents.show'}
                                    canAdd={asset.deleted_at == null ? true : false}
                                />
                            </>
                        )}

                        {activeTab === 'pictures' && (
                            <PictureManager
                                itemCodeId={asset.reference_code}
                                getPicturesUrl={`api.assets.pictures`}
                                uploadRoute={`api.assets.pictures.post`}
                                deleteRoute={`api.pictures.delete`}
                                showRoute={'api.pictures.show'}
                                canAdd={asset.deleted_at == null ? true : false}
                            />
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
