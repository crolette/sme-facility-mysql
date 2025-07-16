import { DocumentManager } from '@/components/tenant/documentManager';
import { PictureManager } from '@/components/tenant/pictureManager';
import { TicketManager } from '@/components/tenant/ticketManager';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Asset, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';

export default function ShowAsset({ asset }: { asset: Asset }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${asset.reference_code} - ${asset.maintainable.name}`,
            href: ``,
        },
    ];

    const { post, delete: destroy } = useForm();

    const deleteAsset = (asset: Asset) => {
        destroy(route(`tenant.assets.destroy`, asset.code));
    };

    const restoreAsset = (asset: Asset) => {
        post(route('api.tenant.assets.restore', asset.id));
    };

    const deleteDefinitelyAsset = (asset: Asset) => {
        destroy(route(`api.tenant.assets.force`, asset.id));
    };

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
                            <a href={route(`tenant.assets.edit`, asset.code)}>
                                <Button>Edit</Button>
                            </a>
                            <Button onClick={() => deleteAsset(asset)} variant={'destructive'}>
                                Delete
                            </Button>
                        </>
                    )}

                    {/* <a href={route(`tenant.tickets.create`)}> */}

                    {/* </a> */}
                </div>
                <p>Code : {asset.code}</p>
                <p>Reference code : {asset.reference_code}</p>
                <p>Location : {asset.location.maintainable.description}</p>
                <p>Category : {asset.category}</p>
                <p>Name : {asset.maintainable?.name}</p>
                <p>Description : {asset.maintainable?.description}</p>
                <p>Purchase date : {asset.maintainable?.purchase_date}</p>
                <p>Purchase cost : {asset.maintainable?.purchase_cost}</p>
                <p>End warranty date : {asset.maintainable?.end_warranty_date}</p>
                <p>Brand : {asset.brand}</p>
                <p>Model : {asset.model}</p>
                <p>Serial number : {asset.serial_number}</p>

                <TicketManager itemCodeId={asset.code} getTicketsUrl={`api.assets.tickets`} locationType="assets" itemId={asset.id} />
                <DocumentManager
                    itemCodeId={asset.code}
                    getDocumentsUrl={`api.assets.documents`}
                    editRoute={`api.documents.update`}
                    uploadRoute={`api.assets.documents.post`}
                    deleteRoute={`api.documents.delete`}
                    showRoute={'api.documents.show'}
                />
                <PictureManager
                    itemCodeId={asset.code}
                    getPicturesUrl={`api.assets.pictures`}
                    uploadRoute={`api.assets.pictures.post`}
                    deleteRoute={`api.pictures.delete`}
                    showRoute={'api.pictures.show'}
                />
            </div>

            {/* {addPictures && addNewPicturesModal()} */}
        </AppLayout>
    );
}
