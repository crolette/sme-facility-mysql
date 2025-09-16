import Modale from '@/components/Modale';
import { DocumentManager } from '@/components/tenant/documentManager';
import { DocumentsList } from '@/components/tenant/documentsList';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Asset, BreadcrumbItem, Contract, TenantBuilding, TenantFloor, TenantRoom, TenantSite } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';

export default function ShowContract({ item, objects }: { item: Contract; objects: [] }) {
    const [contract, setContract] = useState<Contract>(item);
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Show contract`,
            href: `/contract`,
        },
    ];

    const deleteContract = async () => {
        try {
            const response = await axios.delete(route('api.contracts.destroy', contract.id));
            if (response.data.status === 'success') {
                router.visit(route('tenant.contracts.index'));
            }
        } catch (error) {
            console.log(error);
        }
    };

     const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
     const [activeTab, setActiveTab] = useState('information');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Contract" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex gap-2">
                    <a href={route(`tenant.contracts.edit`, contract.id)}>
                        <Button>Edit</Button>
                    </a>
                    <Button onClick={() => setShowDeleteModale(!showDeleteModale)} variant={'destructive'}>
                        Delete
                    </Button>
                </div>
                <div className="grid max-w-full gap-4 lg:grid-cols-[1fr_4fr]">
                    <SidebarMenuAssetLocation
                        activeTab={activeTab}
                        setActiveTab={setActiveTab}
                        menu="contract"
                        infos={{
                            name: contract.name,
                            code: contract.internal_reference,
                            reference: contract.type ?? contract.provider?.category,
                            levelPath: route('tenant.providers.show', contract.provider_id),
                            levelName: contract.provider.name,
                        }}
                    />
                    <div className="overflow-hidden">
                        {activeTab === 'information' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>Contract information</h2>
                                <div>
                                    <p>Name: {contract.name}</p>
                                    <p>Type: {contract.type}</p>
                                    <p>Internal reference: {contract.internal_reference}</p>
                                    <p>Status: {contract.status}</p>
                                    <p>Renewal Type: {contract.renewal_type}</p>
                                    <p>Start date: {contract.start_date}</p>
                                    <p>Contract duration: {contract.contract_duration}</p>
                                    <p>End date : {contract.end_date}</p>
                                    <p>Notice period: {contract.notice_period}</p>
                                    <p>Notice date: {contract.notice_date}</p>
                                    <p>Notes: {contract.notes}</p>
                                    <p>
                                        Provider: <a href={route('tenant.providers.show', contract.provider_id)}>{contract.provider.name}</a>
                                    </p>
                                    <p>Provider reference: {contract.provider_reference}</p>
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
                                <h3>Assets</h3>
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
                            title={'Delete contract'}
                            message={`Are you sure you want to delete this contract ${contract.name} ?`}
                            isOpen={showDeleteModale}
                            onConfirm={deleteContract}
                            onCancel={() => {
                                setShowDeleteModale(false);
                            }}
                        />
        </AppLayout>
    );
}
