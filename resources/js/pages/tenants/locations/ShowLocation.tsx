import SearchableInput from '@/components/SearchableInput';
import { AssetManager } from '@/components/tenant/assetManager';
import { ContractsList } from '@/components/tenant/contractsList';
import { DocumentManager } from '@/components/tenant/documentManager';
import { InterventionManager } from '@/components/tenant/interventionManager';

import { PictureManager } from '@/components/tenant/pictureManager';
import RealocateRoomManager from '@/components/tenant/relocateRoomManager';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import { TicketManager } from '@/components/tenant/ticketManager';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Contract, TenantBuilding, TenantFloor, TenantRoom, TenantSite, type BreadcrumbItem } from '@/types';
import { router } from '@inertiajs/core';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { CircleCheckBig, Move, Pencil, PlusCircle, QrCode } from 'lucide-react';
import { useState } from 'react';

export default function ShowLocation({ item, routeName }: { item: TenantSite | TenantBuilding | TenantFloor | TenantRoom; routeName: string }) {
    const { showToast } = useToast();
    const [location, setLocation] = useState(item);
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${routeName}`,
            href: `/${routeName}`,
        },
        {
            title: `${location.reference_code} - ${location.maintainable.name}`,
            href: ``,
        },
    ];

    const [showModaleRelocateRoom, setShowModaleRelocateRoom] = useState<boolean>(false);

    const fetchLocation = async () => {
        const response = await axios.get(route(`api.${routeName}.show`, location?.reference_code));
        setLocation(response.data.data);
    };

    const generateQR = async () => {
        try {
            const response = await axios.post(route(`api.${routeName}.qr.regen`, location.reference_code));
            if (response.data.status === 'success') {
                fetchLocation();
                showToast(response.data.message, response.data.status);
            }

        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const markMaintenanceDone = async () => {
        try {
            const response = await axios.post(route('api.maintenance.done', location.maintainable.id));
            if (response.data.status === 'success') {
                fetchLocation();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

 

        const [existingContracts, setExistingContracts] = useState(location.contracts ?? []);

        const updateContracts = (newContracts: Contract[]) => {
            setLocation((prev) => ({ ...prev, contracts: newContracts }));
            // setExistingContracts(newContracts);
        };

        const fetchContracts = async () => {
            try {
                const response = await axios.get(route(`api.${routeName}.contracts`, location.reference_code));
                console.log(response.data);
                if (response.data.status === 'success') {
                    updateContracts(response.data.data);
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
             const response = await axios.post(route(`api.${routeName}.contracts.post`, location.reference_code), contracts);
             if (response.data.status === 'success') {
                 setAddExistingContractModale(false);
                 fetchContracts();
                  showToast(response.data.message, response.data.status);
             }
         } catch (error) {
                showToast(error.response.data.message, error.response.data.status);
         }
     };

     const [activeTab, setActiveTab] = useState('information');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex flex-wrap gap-2">
                    <a href={route(`tenant.${routeName}.edit`, location.reference_code)}>
                        <Button>
                            <Pencil />
                            Edit
                        </Button>
                    </a>

                    {location.maintainable.need_maintenance && (
                        <Button onClick={() => markMaintenanceDone()} variant={'green'}>
                            <CircleCheckBig />
                            Mark maintenance as done
                        </Button>
                    )}
                    <Button onClick={generateQR} variant={'secondary'}>
                        <QrCode /> Generate new QR
                    </Button>
                    {routeName === 'rooms' && (
                        <Button variant={'secondary'} onClick={() => setShowModaleRelocateRoom(!showModaleRelocateRoom)}>
                            <Move />
                            Redefine room
                        </Button>
                    )}
                </div>

                {routeName === 'rooms' && showModaleRelocateRoom && (
                    <RealocateRoomManager room={location} itemCode={location.reference_code} onClose={() => setShowModaleRelocateRoom(false)} />
                )}
                <div className="grid max-w-full gap-4 lg:grid-cols-[1fr_6fr]">
                    <SidebarMenuAssetLocation
                        activeTab={activeTab}
                        setActiveTab={setActiveTab}
                        menu='location'
                        infos={{
                            name: location.name,
                            code: location.code,
                            reference: location.reference_code,
                            levelPath: location.level_path ?? '',
                            levelName: location.level?.name ?? '',
                        }}
                    />
                    <div className="overflow-hidden">
                        {activeTab === 'information' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>Code</h2>
                                <div className="grid grid-cols-[1fr_160px] gap-4">
                                    <div>
                                        <div>
                                            <p>Code : {location.code}</p>
                                            <p>Reference code : {location.reference_code}</p>
                                            <p>Category : {location.category}</p>
                                            <p>Name : {location.name}</p>
                                            <p>Address : {location.address}</p>
                                            <p>Description : {location.description}</p>
                                            {location.location_type.slug === 'outdoor' ? (
                                                <>
                                                    <p>
                                                        Outdoor: {location.surface_outdoor} ({location.outdoor_material})
                                                    </p>
                                                </>
                                            ) : (
                                                <>
                                                    <p>
                                                        Floor: {location.surface_floor} ({location.floor_material})
                                                    </p>
                                                    <p>
                                                        Walls: {location.surface_walls} ({location.wall_material})
                                                    </p>
                                                </>
                                            )}
                                        </div>
                                    </div>
                                    <div className="shrink-1">
                                        {location.qr_code && (
                                            <a
                                                href={route('api.file.download', { path: location.qr_code })}
                                                download
                                                className="w-fit cursor-pointer"
                                            >
                                                <img
                                                    key={location.qr_code}
                                                    src={route('api.image.show', { path: location.qr_code })}
                                                    alt=""
                                                    className="h-40 w-40 object-cover"
                                                />
                                            </a>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}

                        {activeTab === 'maintenance' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>Maintenance</h2>
                                <div>
                                    <p>
                                        Maintenance manager:
                                        {location.maintainable.manager ? (
                                            <a href={route('tenant.users.show', location.maintainable.manager.id)}>
                                                {' '}
                                                {location.maintainable.manager.full_name}
                                            </a>
                                        ) : (
                                            'No manager'
                                        )}
                                    </p>
                                    {location.maintainable.need_maintenance && (
                                        <>
                                            <p>Maintenance frequency : {location.maintainable.maintenance_frequency}</p>
                                            <p>Next maintenance date : {location.maintainable.next_maintenance_date}</p>
                                            <p>Last maintenance date : {location.maintainable.last_maintenance_date}</p>
                                        </>
                                    )}
                                </div>
                            </div>
                        )}

                        {activeTab === 'providers' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>Providers</h2>
                                {location.maintainable.providers && location.maintainable.providers.length > 0 && (
                                    <ul>
                                        {location.maintainable.providers.map((provider, index) => (
                                            <li key={index}>
                                                <a href={route('tenant.providers.show', provider.id)}>{provider.name}</a>
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </div>
                        )}

                        {activeTab === 'contracts' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4">
                                <div className="flex items-center justify-between gap-2">
                                    <h2>Contracts</h2>
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
                                </div>
                                <ContractsList
                                    items={location.contracts}
                                    contractableReference={location.reference_code}
                                    getUrl={`api.${routeName}.contracts`}
                                    routeName={routeName}
                                    removable
                                    onContractsChange={updateContracts}
                                />
                            </div>
                        )}

                        {activeTab === 'assets' && <AssetManager itemCode={location.reference_code} type={routeName} />}
                        {activeTab === 'tickets' && (
                            <TicketManager itemCode={location.reference_code} getTicketsUrl={`api.${routeName}.tickets`} locationType={routeName} />
                        )}
                        {activeTab === 'documents' && (
                            <DocumentManager
                                itemCodeId={location.reference_code}
                                getDocumentsUrl={`api.${routeName}.documents`}
                                editRoute={`api.documents.update`}
                                removableRoute={`api.${routeName}.documents.detach`}
                                uploadRoute={`api.${routeName}.documents.post`}
                                deleteRoute={`api.documents.delete`}
                                showRoute={'api.documents.show'}
                            />
                        )}
                        {activeTab === 'pictures' && (
                            <PictureManager
                                itemCodeId={location.reference_code}
                                getPicturesUrl={`api.${routeName}.pictures`}
                                uploadRoute={`api.${routeName}.pictures.post`}
                                deleteRoute={`api.pictures.delete`}
                                showRoute={'api.pictures.show'}
                            />
                        )}
                        {activeTab === 'interventions' && (
                            <InterventionManager
                                itemCodeId={location.reference_code}
                                getInterventionsUrl={`api.${routeName}.interventions`}
                                type={routeName}
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
                                    console.log(contracts);
                                    // const prev = existingContracts;
                                    // prev.push(contracts);
                                    setExistingContracts(contracts);
                                }}
                                placeholder="Search contracts..."
                            />
                            <Button
                                variant="secondary"
                                onClick={() => {
                                    setAddExistingContractModale(false);
                                    setExistingContracts(location.contracts);
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
