import { DocumentManager } from '@/components/tenant/documentManager';
import { InterventionManager } from '@/components/tenant/interventionManager';
import { PictureManager } from '@/components/tenant/pictureManager';
import { TicketManager } from '@/components/tenant/ticketManager';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Asset, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';

export default function ShowAsset({ item }: { item: Asset }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${item.reference_code} - ${item.maintainable.name}`,
            href: ``,
        },
    ];

    console.log(item);

    const { post, delete: destroy } = useForm();

    const [asset, setAsset] = useState(item);

    const fetchAsset = async () => {
        const response = await axios.get(route('api.assets.show', asset.reference_code));
        if (response.data.status === 'success') setAsset(response.data.data);
    };

    const deleteAsset = (asset: Asset) => {
        destroy(route(`api.assets.destroy`, asset.reference_code));
    };

    const restoreAsset = (asset: Asset) => {
        post(route('api.tenant.assets.restore', asset.id));
    };

    const deleteDefinitelyAsset = (asset: Asset) => {
        destroy(route(`api.tenant.assets.force`, asset.id));
    };

    const generateQR = async () => {
        const response = await axios.post(route('api.qr.regen', asset.reference_code));
        if (response.data.status === 'success') {
            // location.reload();
        }
    };

    const markMaintenanceDone = async () => {
        const response = await axios.post(route('api.maintenance.done', asset.maintainable.id));
        if (response.data.status === 'success') fetchAsset();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Asset ${asset.maintainable.name}`} />

            <div className="flex h-full flex-1 flex-col justify-between gap-4 rounded-xl p-4">
                <div>
                    {asset.deleted_at ? (
                        <>
                            <Button onClick={() => restoreAsset(asset)} variant={'green'}>
                                Restore
                            </Button>
                            <Button onClick={() => deleteDefinitelyAsset(asset)} variant={'destructive'}>
                                Delete definitely
                            </Button>
                        </>
                    ) : (
                        <>
                            <a href={route(`tenant.assets.edit`, asset.reference_code)}>
                                <Button>Edit</Button>
                            </a>
                            <Button onClick={() => deleteAsset(asset)} variant={'destructive'}>
                                Delete
                            </Button>
                            <Button onClick={() => markMaintenanceDone()} variant={'green'}>
                                Mark maintenance as done
                            </Button>
                        </>
                    )}
                    <Button onClick={generateQR} variant={'secondary'}>
                        Generate new QR
                    </Button>
                </div>

                <div className="flex items-center gap-2">
                    <div className="flex w-full shrink-0 justify-between rounded-md border border-gray-200 p-4">
                        <div>
                            <h2>Code</h2>
                            <div>
                                <p>Code : {asset.code}</p>
                                <p>Reference code : {asset.reference_code}</p>
                                {asset.is_mobile ? (
                                    <p>
                                        Location :<a href={route(`tenant.users.show`, asset.location.id)}>{asset.location.full_name}</a>
                                    </p>
                                ) : (
                                    <p>
                                        Location : {asset.location.name} - {asset.location.description} -{' '}
                                        <a href={route(`tenant.${asset.location.location_type.level}s.show`, asset.location.reference_code)}>
                                            {asset.location.reference_code}
                                        </a>
                                    </p>
                                )}
                            </div>
                        </div>
                        <div className="shrink-1">
                            {asset.qr_code && (
                                <a href={route('api.file.download', { path: asset.qr_code })} download className="w-fit cursor-pointer">
                                    <img src={route('api.image.show', { path: asset.qr_code })} alt="" className="h-32 w-32" />
                                </a>
                            )}
                        </div>
                    </div>
                </div>

                <div className="rounded-md border border-gray-200 p-4">
                    <h2>Maintenance</h2>
                    <div>
                        <p>
                            Maintenance manager:
                            {asset.maintainable.manager ? (
                                <a href={route('tenant.users.show', asset.maintainable.manager.id)}> {asset.maintainable.manager.full_name}</a>
                            ) : (
                                'No manager'
                            )}
                        </p>
                        <p>Maintenance frequency : {asset.maintainable.maintenance_frequency}</p>
                        <p>Next maintenance date : {asset.maintainable.next_maintenance_date}</p>
                        <p>Last maintenance date : {asset.maintainable.last_maintenance_date}</p>
                    </div>
                </div>

                <div className="rounded-md border border-gray-200 p-4">
                    <h2>Asset information</h2>
                    <div>
                        <p>Category : {asset.category}</p>
                        <p>Name : {asset.name}</p>
                        <p>Description : {asset.description}</p>
                        <p>Brand : {asset.brand}</p>
                        <p>Model : {asset.model}</p>
                        <p>Serial number : {asset.serial_number}</p>
                        <p>Surface : {asset.surface}</p>
                    </div>
                </div>

                <div className="rounded-md border border-gray-200 p-4">
                    <h2>Purchase/Warranty</h2>
                    <div>
                        <p>Purchase date : {asset.maintainable?.purchase_date}</p>
                        <p>Purchase cost : {asset.maintainable?.purchase_cost}</p>
                        <p>End warranty date : {asset.maintainable?.end_warranty_date}</p>
                    </div>
                </div>

                {asset.maintainable.providers && asset.maintainable.providers?.length > 0 && (
                    <div className="rounded-md border border-gray-200 p-4">
                        <h2>Providers</h2>
                        <ul>
                            {asset.maintainable.providers.map((provider, index) => (
                                <li key={index}>
                                    <a href={route('tenant.providers.show', provider.id)}>{provider.name}</a>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                <>
                    <InterventionManager
                        itemCodeId={asset.reference_code}
                        getInterventionsUrl="api.assets.interventions"
                        type="asset"
                        closed={asset.deleted_at == null ? false : true}
                    />
                    <TicketManager
                        itemCode={asset.reference_code}
                        getTicketsUrl={`api.assets.tickets`}
                        locationType="assets"
                        canAdd={asset.deleted_at == null ? true : false}
                    />
                    <DocumentManager
                        itemCodeId={asset.reference_code}
                        getDocumentsUrl={`api.assets.documents`}
                        editRoute={`api.documents.update`}
                        uploadRoute={`api.assets.documents.post`}
                        deleteRoute={`api.documents.delete`}
                        showRoute={'api.documents.show'}
                        canAdd={asset.deleted_at == null ? true : false}
                    />

                    <PictureManager
                        itemCodeId={asset.reference_code}
                        getPicturesUrl={`api.assets.pictures`}
                        uploadRoute={`api.assets.pictures.post`}
                        deleteRoute={`api.pictures.delete`}
                        showRoute={'api.pictures.show'}
                        canAdd={asset.deleted_at == null ? true : false}
                    />
                </>
            </div>
        </AppLayout>
    );
}
