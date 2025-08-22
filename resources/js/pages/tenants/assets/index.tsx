import { Button } from '@/components/ui/button';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Asset, BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { Loader2 } from 'lucide-react';
import { useEffect, useState } from 'react';

export default function IndexAssets() {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index assets`,
            href: `/assets`,
        },
    ];

    const [assets, setAssets] = useState<Asset[]>();
    const [isLoading, setIsLoading] = useState<boolean>(true);

    useEffect(() => {
        fetchAssets();
    }, []);

    const [activeAssetsTab, setActiveAssetsTab] = useState(true);
    const [trashedAssetsTab, setTrashedAssetsTab] = useState(false);

    const { post, delete: destroy } = useForm();

    const deleteDefinitelyAsset = async (asset: Asset) => {
        try {
            const response = await axios.delete(route(`api.assets.force`, asset.reference_code));
            if (response.data.status === 'success') {
                setTrashedAssetsTab(true);
                setActiveAssetsTab(false);
                fetchTrashedAssets();
            }
        } catch (error) {
            console.log(error);
        }
    };

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

    const deleteAsset = async (asset: Asset) => {
        try {
            const response = await axios.delete(route(`api.assets.destroy`, asset.reference_code));
            if (response.data.status === 'success') {
                setSearch('');
                fetchAssets();
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
                                                    <Button onClick={() => deleteAsset(asset)} variant={'destructive'}>
                                                        Delete
                                                    </Button>
                                                    <a href={route(`tenant.assets.edit`, asset.reference_code)}>
                                                        <Button>Edit</Button>
                                                    </a>
                                                    <a href={route(`tenant.assets.show`, asset.reference_code)}>
                                                        <Button variant={'outline'}>See</Button>
                                                    </a>
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
                )}
            </div>
        </AppLayout>
    );
}
