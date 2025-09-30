import SearchableInput from '@/components/SearchableInput';
import { ContractsList } from '@/components/tenant/contractsList';
import { DocumentManager } from '@/components/tenant/documentManager';
import { InterventionManager } from '@/components/tenant/interventionManager';
import { PictureManager } from '@/components/tenant/pictureManager';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import { TicketManager } from '@/components/tenant/ticketManager';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Asset, Contract, type BreadcrumbItem} from '@/types';
import { router } from '@inertiajs/core';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { ArchiveRestore, CircleCheckBig, Pencil, PlusCircle, QrCode, Shredder, Trash2 } from 'lucide-react';
import { useState } from 'react';

export default function ShowAsset({ item }: { item: Asset }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index assets`,
            href: `/assets`,
        },
        {
            title: `${item.reference_code} - ${item.maintainable.name}`,
            href: ``,
        },
    ];

    const { showToast } = useToast();

    const [asset, setAsset] = useState(item);
    const [existingContracts, setExistingContracts] = useState(asset.contracts ?? []);
    

    const fetchAsset = async () => {
        const response = await axios.get(route('api.assets.show', asset.reference_code));
        if (response.data.status === 'success') setAsset(response.data.data);
    };

    const updateContracts = (newContracts: Contract[]) => {
        setAsset((prev) => ({ ...prev, contracts: newContracts }));
        setExistingContracts(newContracts);
    }

    const fetchContracts = async () => {
        try {
            const response = await axios.get(route('api.assets.contracts', asset.reference_code));
            if (response.data.status === 'success') {
                updateContracts(response.data.data)
            }
        } catch (error) {
            console.log(error);
        }
    };

    const [addExistingContractModale, setAddExistingContractModale] = useState<boolean>(false);

    const addExistingContractToAsset = async () => {
        const contracts = {
            existing_contracts: existingContracts.map((elem) => elem.id),
        };

        try {
            const response = await axios.post(route('api.assets.contracts.post', asset.reference_code), contracts);
            if (response.data.status === 'success') {
                setAddExistingContractModale(false);
                fetchContracts();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
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
            const response = await axios.post(route('api.maintenance.done', asset.maintainable.id));
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
            <Head title={`Asset ${asset.maintainable.name}`} />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex flex-wrap items-center gap-4">
                    {asset.deleted_at ? (
                        <>
                            <Button onClick={() => restoreAsset(asset)} variant={'green'}>
                                <ArchiveRestore />
                                Restore
                            </Button>
                            <Button onClick={() => deleteDefinitelyAsset(asset)} variant={'destructive'}>
                                <Shredder />
                                Delete definitely
                            </Button>
                        </>
                    ) : (
                        <>
                            <a href={route(`tenant.assets.edit`, asset.reference_code)}>
                                <Button>
                                    <Pencil />
                                    Edit
                                </Button>
                            </a>
                            <Button onClick={() => deleteAsset(asset)} variant={'destructive'}>
                                <Trash2 />
                                Delete
                            </Button>
                            {asset.maintainable.need_maintenance && (
                                <Button onClick={() => markMaintenanceDone()} variant={'green'}>
                                    <CircleCheckBig />
                                    Mark maintenance as done
                                </Button>
                            )}
                        </>
                    )}
                    <Button onClick={generateQR} variant={'secondary'}>
                        <QrCode />
                        Generate new QR
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
                                <h2>Asset information</h2>
                                <div className="grid grid-cols-[1fr_160px] gap-4">
                                    <div>
                                        <p>Category : {asset.category}</p>
                                        <p>Name : {asset.name}</p>
                                        <p>Description : {asset.description}</p>
                                        <p>Brand : {asset.brand}</p>
                                        <p>Model : {asset.model}</p>
                                        <p>Serial number : {asset.serial_number}</p>
                                        <p>Surface : {asset.surface}</p>
                                    </div>
                                    <div className="shrink-1">
                                        {asset.qr_code && (
                                            <a href={route('api.file.download', { path: asset.qr_code })} download className="w-fit cursor-pointer">
                                                <img
                                                    key={asset.qr_code}
                                                    src={route('api.image.show', { path: asset.qr_code })}
                                                    alt=""
                                                    className="aspect-square h-32 w-auto"
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
                                    <h2>Maintenance</h2>
                                    <div>
                                        <p>
                                            Maintenance manager:
                                            {asset.maintainable.manager ? (
                                                <a href={route('tenant.users.show', asset.maintainable.manager.id)}>
                                                    {' '}
                                                    {asset.maintainable.manager.full_name}
                                                </a>
                                            ) : (
                                                'No manager'
                                            )}
                                        </p>
                                        {asset.maintainable.need_maintenance && (
                                            <>
                                                <p>Maintenance frequency : {asset.maintainable.maintenance_frequency}</p>
                                                <p>Next maintenance date : {asset.maintainable.next_maintenance_date}</p>
                                                <p>Last maintenance date : {asset.maintainable.last_maintenance_date}</p>
                                            </>
                                        )}
                                    </div>
                                </div>

                                {asset.depreciable && (
                                    <div className="border-sidebar-border bg-sidebar rounded-md border p-4">
                                        <h2>Depreciation</h2>
                                        <div>
                                            <p>depreciation_duration : {asset.depreciation_duration}</p>
                                            <p>depreciation_start_date : {asset.depreciation_start_date}</p>
                                            <p>depreciation_end_d : {asset.depreciation_end_date}</p>
                                            <p>residual_value : {asset.residual_value}</p>
                                        </div>
                                    </div>
                                )}
                            </>
                        )}

                        {activeTab === 'warranty' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4">
                                <h2>Purchase/Warranty</h2>
                                <div>
                                    <p>Purchase date : {asset.maintainable.purchase_date}</p>
                                    <p>Purchase cost : {asset.maintainable.purchase_cost}</p>
                                    {asset.maintainable.under_warranty && <p>End warranty date : {asset.maintainable.end_warranty_date}</p>}
                                </div>
                            </div>
                        )}

                        {activeTab === 'contracts' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4">
                                <div className="flex items-center justify-between gap-2">
                                    <h2>Contracts</h2>
                                        {!asset.deleted_at && (
                                    <div className="space-y-2 space-x-4 sm:space-y-0">
                                        <Button onClick={() => setAddExistingContractModale(true)}>
                                            <PlusCircle />
                                            Add existing contract
                                        </Button>
                                        <Button onClick={() => router.get(route('tenant.contracts.create'))}>
                                            <PlusCircle />
                                            Add new contract
                                        </Button>
                                        
                                        </div>
                                        )}
                                </div>
                                <ContractsList
                                    items={asset.contracts}
                                    contractableReference={asset.reference_code}
                                    getUrl="api.assets.contracts"
                                    routeName="assets"
                                    removable
                                    onContractsChange={updateContracts}
                                />
                            </div>
                        )}

                        {activeTab === 'providers' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4">
                                <h2>Providers</h2>

                                <p>End contract date : {asset.contract_end_date}</p>
                                <ul>
                                    {asset.maintainable.providers?.map((provider, index) => (
                                        <li key={index}>
                                            <a href={route('tenant.providers.show', provider.id)}>{provider.name}</a>
                                        </li>
                                    ))}
                                </ul>
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

            {addExistingContractModale && (
                <div className="bg-background/50 fixed inset-0 z-50">
                    <div className="bg-background/20 flex h-dvh items-center justify-center">
                        <div className="bg-background flex flex-col items-center justify-center p-4 text-center md:max-w-1/3">
                            <p>Add Existing contract</p>
                            <SearchableInput<Contract>
                                multiple={true}
                                searchUrl={route('api.contracts.search')}
                                selectedItems={existingContracts}
                                getDisplayText={(contract) => contract.name}
                                getKey={(contract) => contract.id}
                                onSelect={(contracts) => {
                                    setExistingContracts(contracts);
                                }}
                                placeholder="Search contracts..."
                            />
                            <Button
                                variant="secondary"
                                onClick={() => {
                                    setAddExistingContractModale(false);
                                    setExistingContracts(asset.contracts);
                                }}
                            >
                                Cancel
                            </Button>
                            <Button onClick={addExistingContractToAsset}>Add contract</Button>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
