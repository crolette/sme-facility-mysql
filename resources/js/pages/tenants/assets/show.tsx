import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Asset, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { BiSolidFilePdf } from 'react-icons/bi';

export default function ShowAsset({ asset }: { asset: Asset }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${asset.reference_code} - ${asset.maintainable.name}`,
            href: ``,
        },
    ];

    console.log(asset);

    const { delete: destroy } = useForm();

    const deleteAsset = (asset: Asset) => {
        destroy(route(`tenant.assets.destroy`, asset.code));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Tenants" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div>
                    <a href={route(`tenant.assets.edit`, asset.code)}>
                        <Button>Edit</Button>
                    </a>
                    <Button onClick={() => deleteAsset(asset)} variant={'destructive'}>
                        Delete
                    </Button>
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
                <h3>Documents</h3>
                {asset.documents.length > 0 && (
                    <ul className="flex flex-col gap-4">
                        {asset.documents.map((document, index) => {
                            const isImage = document.mime_type.startsWith('image/');
                            const isPdf = document.mime_type === 'application/pdf';
                            return (
                                <li key={index} className="bg-foreground/10 grid grid-cols-2 gap-2 p-6">
                                    <div>
                                        {isImage && (
                                            <img
                                                src={route('documents.show', document)}
                                                alt="preview"
                                                className="mx-auto h-40 w-40 rounded object-cover"
                                            />
                                        )}
                                        {isPdf && <BiSolidFilePdf size={'160px'} />}
                                    </div>
                                    <div>
                                        <p>{document.category}</p>
                                        <p>{document.name}</p>
                                        <p>{document.filename}</p>

                                        <p>{document.created_at}</p>
                                        <p>{document.description}</p>
                                        <p>{document.sizeMo} Mo</p>
                                    </div>
                                </li>
                            );
                        })}
                    </ul>
                )}
            </div>
        </AppLayout>
    );
}
