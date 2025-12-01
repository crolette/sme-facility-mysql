import ModaleForm from '@/components/ModaleForm';
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
import Field from '@/components/ui/field';
import { usePermissions } from '@/hooks/usePermissions';
import AppLayout from '@/layouts/app-layout';
import { Contract, TenantBuilding, TenantFloor, TenantRoom, TenantSite, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { CircleCheckBig, Move, Pencil, QrCode } from 'lucide-react';
import { useState } from 'react';

export default function ShowLocation({ item, routeName }: { item: TenantSite | TenantBuilding | TenantFloor | TenantRoom; routeName: string }) {
    const { showToast } = useToast();
    const { hasPermission } = usePermissions();
    const [location, setLocation] = useState(item);
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${tChoice(`locations.${routeName}`, 2)}`,
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
            const response = await axios.patch(route('api.maintenance.done', location.maintainable.id));
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
            <Head title={item.name + ' - ' + item.reference_code} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex flex-wrap gap-2">
                    {hasPermission('update locations') && (
                        <>
                            <a href={route(`tenant.${routeName}.edit`, location.reference_code)}>
                                <Button>
                                    <Pencil />
                                    {t('actions.edit')}
                                </Button>
                            </a>
                            {location.maintainable.need_maintenance && (
                                <Button onClick={() => markMaintenanceDone()} variant={'green'}>
                                    <CircleCheckBig />
                                    {t('maintenances.mark_done')}
                                </Button>
                            )}
                        </>
                    )}
                    <Button onClick={generateQR} variant={'secondary'}>
                        <QrCode /> {t('actions.generate_qr')}
                    </Button>

                    {hasPermission('create locations') && routeName === 'rooms' && (
                        <Button variant={'secondary'} onClick={() => setShowModaleRelocateRoom(!showModaleRelocateRoom)}>
                            <Move />
                            {t('locations.rooms_relocate')}
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
                        menu="location"
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
                                <h2>{t('common.information')}</h2>
                                <div className="grid gap-4 sm:grid-cols-[1fr_160px]">
                                    <div className="space-y-2">
                                        <Field label={t('common.name')} text={location.name} />
                                        <Field label={t('common.category')} text={location.category} />
                                        {location.address && <Field label={t('common.address')} text={location.address} />}
                                        <Field label={t('common.description')} text={location.description} />

                                        {location.location_type.slug === 'outdoor' ? (
                                            <>
                                                <p>
                                                    <Field
                                                        label={t('location.outdoor')}
                                                        text={`${location.surface_outdoor} m² - ${location.outdoor_material}`}
                                                    />
                                                </p>
                                            </>
                                        ) : (
                                            <>
                                                <Field
                                                    label={t('locations.surface_type_floor')}
                                                    text={`${location.surface_floor ?? 0} m² - ${location.floor_material}`}
                                                />
                                                <Field
                                                    label={t('locations.surface_type_wall')}
                                                    text={`${location.surface_walls ?? '0'} m² - ${location.wall_material}`}
                                                />
                                                {location.height && <Field label={t('locations.height')} text={`${location.height} m`} />}
                                            </>
                                        )}
                                    </div>
                                    <div className="mx-auto h-fit shrink-1 bg-white">
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
                                                    className="h-32 w-auto"
                                                />
                                            </a>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}

                        {activeTab === 'maintenance' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>{tChoice('maintenances.title', 1)}</h2>
                                <div>
                                    <Field
                                        label={t('maintenances.maintenance_manager')}
                                        text={
                                            location.maintainable.manager ? (
                                                <a href={route('tenant.users.show', location.maintainable.manager.id)}>
                                                    {' '}
                                                    {location.maintainable.manager.full_name}
                                                </a>
                                            ) : (
                                                t('maintenances.no_manager')
                                            )
                                        }
                                    />

                                    {location.maintainable.need_maintenance && (
                                        <>
                                            <Field label={t('maintenances.frequency')} text={location.maintainable.maintenance_frequency} />
                                            <Field
                                                label={t('maintenances.next_maintenance_date')}
                                                date
                                                text={location.maintainable.next_maintenance_date ?? t('maintenances.planned_not')}
                                            />
                                            <Field
                                                label={t('maintenances.last_maintenance_date')}
                                                date
                                                text={location.maintainable.last_maintenance_date}
                                            />
                                        </>
                                    )}
                                </div>
                            </div>
                        )}

                        {activeTab === 'providers' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>{tChoice('providers.title', 2)}</h2>
                                {location.maintainable.providers && location.maintainable.providers.length > 0 && (
                                    <ul>
                                        {location.maintainable.providers.map((provider, index) => (
                                            <li key={index}>
                                                <Field
                                                    label={tChoice('providers.title', 1)}
                                                    text={<a href={route('tenant.providers.show', provider.id)}>{provider.name}</a>}
                                                />
                                            </li>
                                        ))}
                                    </ul>
                                )}
                            </div>
                        )}

                        {activeTab === 'contracts' && (
                            <ContractsList
                                contractableReference={location.reference_code}
                                getUrl={`api.${routeName}.contracts`}
                                routeName={routeName}
                                removable
                                parameter={routeName.substring(0, routeName.length - 1)}
                            />
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
                <ModaleForm title={'Add Existing contract'}>
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
                            setExistingContracts(location.contracts);
                        }}
                    >
                        Cancel
                    </Button>
                    <Button onClick={addExistingContractToAsset}>Add contract</Button>
                </ModaleForm>
            )}
        </AppLayout>
    );
}
