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
            </div>
        </AppLayout>
    );
}
