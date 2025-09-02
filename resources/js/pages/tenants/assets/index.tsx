import Modale from '@/components/Modale';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Asset, BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { Loader2 } from 'lucide-react';
import { useEffect, useState } from 'react';

export default function IndexAssets({ items }: { items: Asset[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index assets`,
            href: `/assets`,
        },
    ];

    const [assets, setAssets] = useState<Asset[]>(items);
    const [isLoading, setIsLoading] = useState<boolean>(false);

    // useEffect(() => {
    //     fetchAssets();
    // }, []);

    const [activeAssetsTab, setActiveAssetsTab] = useState(true);
    const [trashedAssetsTab, setTrashedAssetsTab] = useState(false);

    const [assetToDeleteDefinitely, setAssetToDeleteDefinitely] = useState('');
    const [showDeleteDefinitelyModale, setShowDeleteDefinitelyModale] = useState<boolean>(false);

    const deleteDefinitelyAsset = async () => {
        try {
            const response = await axios.delete(route(`api.assets.force`, assetToDeleteDefinitely));
            if (response.data.status === 'success') {
                setTrashedAssetsTab(true);
                setActiveAssetsTab(false);
                fetchTrashedAssets();
                setShowDeleteDefinitelyModale(false);
            }
        } catch (error) {
            console.log(error);
        }
    };

    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);

    const restoreAsset = async (asset: Asset) => {
        try {
            const response = await axios.post(route(`api.assets.restore`, asset.reference_code));
            if (response.data.status === 'success') {
                setTrashedAssetsTab(!trashedAssetsTab);
                setActiveAssetsTab(!activeAssetsTab);
                setSearch('');
                fetchAssets();
            }
        } catch (error) {
            console.log(error);
        }
    };

    const fetchAssets = async () => {
        try {
            const response = await axios.get(route('api.assets.index'));
            setAssets(response.data.data);
            setIsLoading(false);
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
        }
    };

    const fetchTrashedAssets = async () => {
        setIsLoading(true);
        try {
            const response = await axios.get(route('api.assets.trashed'));
            setTrashedAssets(response.data.data);
            setIsLoading(false);
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
        }
    };

    const [assetToDelete, setAssetToDelete] = useState('');

    const deleteAsset = async () => {
        try {
            const response = await axios.delete(route(`api.assets.destroy`, assetToDelete));
            if (response.data.status === 'success') {
                setSearch('');
                fetchAssets();
                setShowDeleteModale(!showDeleteModale);
            }
        } catch (error) {
            console.log(error);
        }
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
                const response = await axios.get(route('api.assets.trashed', { q: debouncedSearch }));
                setTrashedAssets(await response.data.data);
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

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="b flex justify-between border-b-2">
                    <ul className="flex pl-4">
                        <li
                            className={cn(
                                'cursor-pointer rounded-t-lg border-x-2 border-t-2 px-4 py-1',
                                activeAssetsTab ? 'bg-primary text-background' : 'bg-secondary',
                            )}
                            onClick={() => {
                                setActiveAssetsTab(!activeAssetsTab);
                                setTrashedAssetsTab(!trashedAssetsTab);
                            }}
                        >
                            active
                        </li>
                        <li
                            className={cn(
                                'cursor-pointer rounded-t-lg border-x-2 border-t-2 px-4 py-1',
                                trashedAssetsTab ? 'bg-primary text-background' : 'bg-secondary',
                            )}
                            onClick={() => {
                                setActiveAssetsTab(!activeAssetsTab);
                                setTrashedAssetsTab(!trashedAssetsTab);
                            }}
                        >
                            trashed
                        </li>
                    </ul>
                    <a href={route(`tenant.assets.create`)} className="w-fit">
                        <Button>Add asset</Button>
                    </a>
                </div>
                {isLoading && (
                    <div className="my-4 flex gap-4">
                        <Loader2 size={24} className="animate-spin" />
                        <p className="animate-pulse">Loading...</p>
                    </div>
                )}
                {!isLoading && activeAssetsTab && (
                    <>
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
                                                    <a href={route(`tenant.assets.show`, asset.reference_code)}> {asset.reference_code} </a>
                                                </TableBodyData>
                                                <TableBodyData>{asset.code}</TableBodyData>
                                                <TableBodyData>{asset.category}</TableBodyData>
                                                <TableBodyData>{asset.maintainable.name}</TableBodyData>
                                                <TableBodyData>{asset.maintainable.description}</TableBodyData>

                                                <TableBodyData>
                                                    <a href={route(`tenant.assets.show`, asset.reference_code)}>
                                                        <Button variant={'outline'}>See</Button>
                                                    </a>

                                                    <a href={route(`tenant.assets.edit`, asset.reference_code)}>
                                                        <Button>Edit</Button>
                                                    </a>
                                                    <Button
                                                        onClick={() => {
                                                            setAssetToDelete(asset.reference_code);
                                                            setShowDeleteModale(true);
                                                        }}
                                                        variant={'destructive'}
                                                    >
                                                        Delete
                                                    </Button>
                                                </TableBodyData>
                                            </TableBodyRow>
                                        );
                                    })}
                            </TableBody>
                        </Table>
                    </>
                )}

                {!isLoading && trashedAssetsTab && (
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
                                                <a href={route('tenant.assets.deleted', asset.id)}>
                                                    <Button>Show</Button>
                                                </a>
                                                <Button onClick={() => restoreAsset(asset)} variant={'green'}>
                                                    Restore
                                                </Button>
                                                <Button
                                                    onClick={() => {
                                                        setAssetToDeleteDefinitely(asset.reference_code);
                                                        setShowDeleteDefinitelyModale(true);
                                                    }}
                                                    variant={'destructive'}
                                                >
                                                    Delete definitely
                                                </Button>
                                            </TableBodyData>
                                        </TableBodyRow>
                                    );
                                })}
                        </TableBody>
                    </Table>
                )}
            </div>
            <Modale
                title={'Delete asset'}
                message={'Are you sure you want to delete this asset ?'}
                isOpen={showDeleteModale}
                onConfirm={deleteAsset}
                onCancel={() => {
                    setAssetToDelete('');
                    setShowDeleteModale(false);
                }}
            />
            <Modale
                title={'Delete definitely'}
                message={
                    'Are you sure to delete this asset DEFINITELY ? You will not be able to restore it afterwards ! All pictures, documents, ... will be deleted too.'
                }
                isOpen={showDeleteDefinitelyModale}
                onConfirm={deleteDefinitelyAsset}
                onCancel={() => {
                    setAssetToDeleteDefinitely('');
                    setShowDeleteDefinitelyModale(false);
                }}
            />
        </AppLayout>
    );
}
