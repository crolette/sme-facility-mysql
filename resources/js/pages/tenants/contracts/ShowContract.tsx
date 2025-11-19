import Modale from '@/components/Modale';
import { DocumentManager } from '@/components/tenant/documentManager';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import Field from '@/components/ui/field';
import AppLayout from '@/layouts/app-layout';
import { Asset, BreadcrumbItem, Contract, TenantBuilding, TenantFloor, TenantRoom, TenantSite } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useState } from 'react';

export default function ShowContract({ item, objects }: { item: Contract; objects: [] }) {
    const { t, tChoice } = useLaravelReactI18n();
    const { showToast } = useToast();
    const [contract, setContract] = useState<Contract>(item);
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${tChoice('contracts.title', 2)}`,
            href: `/contracts`,
        },
        {
            title: `${contract.name} (${contract.provider ? contract.provider?.name : ''})`,
            href: `/contract/${contract.id}`,
        },
    ];

    const deleteContract = async () => {
        try {
            const response = await axios.delete(route('api.contracts.destroy', contract.id));
            if (response.data.status === 'success') {
                router.visit(route('tenant.contracts.index'));
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
    const [activeTab, setActiveTab] = useState('information');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={tChoice('contracts.title', 2) + ' ' + contract.name} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex gap-2">
                    <a href={route(`tenant.contracts.edit`, contract.id)}>
                        <Button>{t('actions.edit')}</Button>
                    </a>
                    <Button onClick={() => setShowDeleteModale(!showDeleteModale)} variant={'destructive'}>
                        {t('actions.delete')}
                    </Button>
                </div>
                <div className="grid max-w-full gap-4 lg:grid-cols-[1fr_6fr]">
                    <SidebarMenuAssetLocation
                        activeTab={activeTab}
                        setActiveTab={setActiveTab}
                        menu="contract"
                        infos={{
                            name: contract.name,
                            code: contract.internal_reference,
                            status: contract.status,
                            reference: contract.type ?? contract.provider?.category ?? 'NC',
                            levelPath: contract.provider ? route('tenant.providers.show', contract.provider_id) : '',
                            levelName: contract.provider?.name ?? 'NC',
                        }}
                    />
                    <div className="overflow-hidden">
                        {activeTab === 'information' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>{t('common.information')}</h2>
                                <div className="space-y-2">
                                    <Field label={t('common.name')} text={contract.name} />
                                    <div className="flex flex-wrap gap-4">
                                        <Field label={t('contracts.internal_ref')} text={contract.internal_reference ?? 'NA'} />
                                        {contract.provider_reference && (
                                            <Field label={t('contracts.provider_ref')} text={contract.provider_reference} />
                                        )}
                                    </div>
                                    <Field label={t('contracts.renewal_type')} text={contract.renewal_type} />
                                    <div className="flex gap-4">
                                        <Field label={t('contracts.start_date')} date text={contract.start_date} />
                                        <Field label={t('contracts.duration_contract')} text={contract.contract_duration} />

                                        <Field label={t('contracts.end_date')} date text={contract.end_date} />
                                    </div>
                                    <div className="flex gap-4">
                                        <Field label={t('contracts.notice_period')} text={contract.notice_period} />
                                        <Field label={t('contracts.notice_date')} date text={contract.notice_date} />
                                    </div>
                                    <Field label={t('common.notes')} text={contract.notes} />
                                </div>
                            </div>
                        )}
                        {activeTab === 'documents' && (
                            <DocumentManager
                                itemCodeId={contract.id}
                                getDocumentsUrl={`api.contracts.documents`}
                                editRoute={`api.documents.update`}
                                removableRoute={`api.contracts.documents.detach`}
                                uploadRoute={`api.contracts.documents.post`}
                                deleteRoute={`api.documents.delete`}
                                showRoute={'api.documents.show'}
                                canAdd={true}
                            />
                        )}

                        {activeTab === 'assets' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h3>{tChoice('assets.title', 2)}</h3>
                                <ul>
                                    {objects.map((object: Partial<Asset | TenantBuilding | TenantSite | TenantFloor | TenantRoom>) => (
                                        <li key={object.id}>
                                            <p>
                                                <a
                                                    href={
                                                        object.pivot.contractable_type.includes('Asset')
                                                            ? route('tenant.assets.show', object.reference_code)
                                                            : route(`tenant.${object.location_type.level}s.show`, object.reference_code)
                                                    }
                                                >
                                                    {object.name} - {object.category}- {object.code}
                                                </a>
                                            </p>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}
                    </div>
                </div>
            </div>
            <Modale
                title={t('actions.delete-type', { type: tChoice('contracts.title', 1) })}
                message={t('contracts.delete_description', { name: contract.name })}
                isOpen={showDeleteModale}
                onConfirm={deleteContract}
                onCancel={() => {
                    setShowDeleteModale(false);
                }}
            />
        </AppLayout>
    );
}
