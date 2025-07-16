import { Button } from '@/components/ui/button';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Asset, BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function IndexAssets({ assets }: { assets: Asset[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index assets`,
            href: `/assets`,
        },
    ];

    const [activeAssetsTab, setActiveAssetsTab] = useState(true);
    const [trashedAssetsTab, setTrashedAssetsTab] = useState(false);

    const { post, delete: destroy } = useForm();

    const deleteDefinitelyAsset = (asset: Asset) => {
        destroy(route(`api.tenant.assets.force`, asset.id));
    };
    const restoreAsset = (asset: Asset) => {
        post(route('api.tenant.assets.restore', asset.id), {
            onSuccess: () => {
                setTrashedAssetsTab(!trashedAssetsTab);
                setActiveAssetsTab(!activeAssetsTab);
                setSearch('');
            },
        });
    };

    const deleteAsset = (asset: Asset) => {
        destroy(route(`tenant.assets.destroy`, asset.code));
    };

    const [search, setSearch] = useState('');
    const [trashedAssets, setTrashedAssets] = useState<Asset[]>();
    const [debouncedSearch, setDebouncedSearch] = useState(search);
    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedSearch(search);
        }, 500);

        return () => {
            clearTimeout(handler);
        };
    }, [search]);

    useEffect(() => {
        const fetchData = async () => {
            try {
                const response = await fetch(`/api/v1/assets/trashed?q=${debouncedSearch}`);
                setTrashedAssets(await response.json());
            } catch (error) {
                console.error('Erreur lors de la recherche :', error);
            }
        };

        // if (debouncedSearch ) {
        fetchData();
        // }
    }, [debouncedSearch, trashedAssetsTab]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Assets" />

            <div>
                <ul className="flex border-b-2 pl-4">
                    <li
                        className={cn('cursor-pointer border-x-2 border-t-2 px-4 py-1', activeAssetsTab ? 'bg-secondary' : 'bg-transparent')}
                        onClick={() => {
                            setActiveAssetsTab(!activeAssetsTab);
                            setTrashedAssetsTab(!trashedAssetsTab);
                        }}
                    >
                        active
                    </li>
                    <li
                        className={cn('cursor-pointer border-x-2 border-t-2 px-4 py-1', trashedAssetsTab ? 'bg-secondary' : 'bg-transparent')}
                        onClick={() => {
                            setActiveAssetsTab(!activeAssetsTab);
                            setTrashedAssetsTab(!trashedAssetsTab);
                        }}
                    >
                        trashed
                    </li>
                </ul>
            </div>
            {activeAssetsTab && (
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    <a href={route(`tenant.assets.create`)}>
                        <Button>Create</Button>
                    </a>
                    <Table>
                        <TableHead>
                            <TableHeadRow>
                                <TableHeadData>Reference code</TableHeadData>
                                <TableHeadData>Code</TableHeadData>
                                <TableHeadData>Category</TableHeadData>
                                <TableHeadData>Name</TableHeadData>
                                <TableHeadData>Description</TableHeadData>
                                <TableHeadData></TableHeadData>
                            </TableHeadRow>
                        </TableHead>
                        <TableBody>
                            {assets &&
                                assets.map((asset, index) => {
                                    return (
                                        <TableBodyRow key={index}>
                                            <TableBodyData>
                                                <a href={route(`tenant.assets.show`, asset.code)}> {asset.reference_code} </a>
                                            </TableBodyData>
                                            <TableBodyData>{asset.code}</TableBodyData>
                                            <TableBodyData>{asset.category}</TableBodyData>
                                            <TableBodyData>{asset.maintainable.name}</TableBodyData>
                                            <TableBodyData>{asset.maintainable.description}</TableBodyData>

                                            <TableBodyData>
                                                <Button onClick={() => deleteAsset(asset)} variant={'destructive'}>
                                                    Delete
                                                </Button>
                                                <a href={route(`tenant.assets.edit`, asset.code)}>
                                                    <Button>Edit</Button>
                                                </a>
                                                <a href={route(`tenant.assets.show`, asset.code)}>
                                                    <Button variant={'outline'}>See</Button>
                                                </a>
                                            </TableBodyData>
                                        </TableBodyRow>
                                    );
                                })}
                        </TableBody>
                    </Table>
                </div>
            )}
            {trashedAssetsTab && (
                <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                    TRASH
                    <Table>
                        <TableHead>
                            <TableHeadRow>
                                <TableHeadData>Reference code</TableHeadData>
                                <TableHeadData>Code</TableHeadData>
                                <TableHeadData>Category</TableHeadData>
                                <TableHeadData>Name</TableHeadData>
                                <TableHeadData>Description</TableHeadData>
                                <TableHeadData></TableHeadData>
                            </TableHeadRow>
                        </TableHead>
                        <TableBody>
                            {trashedAssets &&
                                trashedAssets.map((asset, index) => {
                                    return (
                                        <TableBodyRow key={index}>
                                            <TableBodyData>
                                                <a href={route(`tenant.assets.deleted`, asset.id)}> {asset.reference_code} </a>
                                            </TableBodyData>

                                            <TableBodyData>{asset.code}</TableBodyData>
                                            <TableBodyData>{asset.category}</TableBodyData>
                                            <TableBodyData>{asset.maintainable.name}</TableBodyData>
                                            <TableBodyData>{asset.maintainable.description}</TableBodyData>

                                            <TableBodyData>
                                                <Button onClick={() => deleteDefinitelyAsset(asset)} variant={'destructive'}>
                                                    Delete definitely
                                                </Button>
                                                <Button onClick={() => restoreAsset(asset)} variant={'green'}>
                                                    Restore
                                                </Button>
                                                <a href={route('tenant.assets.deleted', asset.id)}>
                                                    <Button>Show</Button>
                                                </a>
                                            </TableBodyData>
                                        </TableBodyRow>
                                    );
                                })}
                        </TableBody>
                    </Table>
                </div>
            )}
        </AppLayout>
    );
}
