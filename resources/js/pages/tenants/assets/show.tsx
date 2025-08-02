import { DocumentManager } from '@/components/tenant/documentManager';
import { InterventionManager } from '@/components/tenant/interventionManager';
import { PictureManager } from '@/components/tenant/pictureManager';
import { TicketManager } from '@/components/tenant/ticketManager';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Asset, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';

export default function ShowAsset({ asset }: { asset: Asset }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${asset.reference_code} - ${asset.maintainable.name}`,
            href: ``,
        },
    ];

    console.log(asset);

    const { post, delete: destroy } = useForm();

    const deleteAsset = (asset: Asset) => {
        destroy(route(`tenant.assets.destroy`, asset.reference_code));
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
            location.reload();
        }
    };

    console.log(asset.maintainable);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Asset ${asset.maintainable.name}`} />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
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
                        </>
                    )}
                    <Button onClick={generateQR} variant={'secondary'}>
                        Generate QR
                    </Button>
                </div>
                <p>Code : {asset.code}</p>
                <p>Reference code : {asset.reference_code}</p>
                <p>
                    Location : {asset.location.name} - {asset.location.description} -{' '}
                    <a href={route(`tenant.${asset.location.location_type.level}s.show`, asset.location.reference_code)}>
                        {asset.location.reference_code}
                    </a>
                </p>
                <p>Category : {asset.category}</p>
                <p>Name : {asset.name}</p>
                <p>Surface : {asset.surface}</p>
                <p>Description : {asset.description}</p>
                <p>Purchase date : {asset.maintainable?.purchase_date}</p>
                <p>Purchase cost : {asset.maintainable?.purchase_cost}</p>
                <p>End warranty date : {asset.maintainable?.end_warranty_date}</p>
                <p>Brand : {asset.brand}</p>
                <p>Model : {asset.model}</p>
                <p>Serial number : {asset.serial_number}</p>
                <p>Maintenance manager : {asset.maintainable.manager?.full_name ?? 'No manager'}</p>
                {asset.qr_code && (
                    <a href={route('api.qr.download', { path: asset.qr_code })} download className="w-fit cursor-pointer">
                        <img src={route('api.qr.show', { path: asset.qr_code })} alt="" className="h-32 w-32" />
                    </a>
                )}
                <p>Providers</p>
                {asset.maintainable.providers && (
                    <ul>
                        {asset.maintainable.providers.map((provider, index) => (
                            <li key={index}>
                                <a href={route('tenant.providers.show', provider.id)}>{provider.name}</a>
                            </li>
                        ))}
                    </ul>
                )}

                <>
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
                    <InterventionManager
                        itemCodeId={asset.reference_code}
                        getInterventionsUrl="api.assets.interventions"
                        type="asset"
                        closed={asset.deleted_at == null ? false : true}
                    />
                </>
            </div>

            {/* {addPictures && addNewPicturesModal()} */}
        </AppLayout>
    );
}
