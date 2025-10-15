import Modale from '@/components/Modale';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Asset, BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { ArchiveRestore, Loader2, Pencil, PlusCircle, Shredder, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

export default function IndexAssets({ items }: { items: Asset[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index assets`,
            href: `/assets`,
        },
    ];
    const { showToast } = useToast();

    const [assets, setAssets] = useState<Asset[]>(items);
    const [isLoading, setIsLoading] = useState<boolean>(false);

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
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
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
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const fetchAssets = async () => {
        try {
            const response = await axios.get(route('api.assets.index'));
            setAssets(response.data.data);
            setIsLoading(false);
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const fetchTrashedAssets = async () => {
        setIsLoading(true);
        try {
            const response = await axios.get(route('api.assets.trashed'));
            setTrashedAssets(response.data.data);
            setIsLoading(false);
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const [assetToDelete, setAssetToDelete] = useState<Asset | null>(null);

    const deleteAsset = async () => {
        try {
            const response = await axios.delete(route(`api.assets.destroy`, assetToDelete?.reference_code));
            if (response.data.status === 'success') {
                setSearch('');
                fetchAssets();
                setShowDeleteModale(!showDeleteModale);
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
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
                <div className="border-accent flex gap-10 border-b-2">
                    <ul className="flex pl-4">
                        <li
                            className={cn(
                                'cursor-pointer rounded-t-lg border-x-2 border-t-2 px-6 py-1',
                                activeAssetsTab ? 'bg-primary text-background' : 'bg-secondary',
                            )}
                            onClick={() => {
                                setActiveAssetsTab(true);
                                setTrashedAssetsTab(false);
                            }}
                        >
                            active
                        </li>
                        <li
                            className={cn(
                                'cursor-pointer rounded-t-lg border-x-2 border-t-2 px-6 py-1',
                                trashedAssetsTab ? 'bg-primary text-background' : 'bg-secondary',
                            )}
                            onClick={() => {
                                setActiveAssetsTab(false);
                                setTrashedAssetsTab(true);
                            }}
                        >
                            trashed
                        </li>
                    </ul>
                    <a href={route(`tenant.assets.create`)} className="w-fit">
                        <Button>
                            <PlusCircle />
                            Create
                        </Button>
                    </a>
                    <a href={route('tenant.pdf.qr-codes', { type: 'assets' })} target="__blank">
                        <Button variant={'secondary'}>
                            <BiSolidFilePdf size={20} />
                            Download QR Codes
                        </Button>
                    </a>
                </div>
                {isLoading && (
                    <div className="my-4 flex gap-4">
                        <Loader2 size={24} className="animate-spin" />
                        <p className="animate-pulse">Loading...</p>
                    </div>
                )}
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
                    {!isLoading && activeAssetsTab && (
                        <>
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

                                                <TableBodyData className="space-x-2">
                                                    <a href={route(`tenant.assets.edit`, asset.reference_code)}>
                                                        <Button>
                                                            <Pencil />
                                                        </Button>
                                                    </a>
                                                    <Button
                                                        onClick={() => {
                                                            setAssetToDelete(asset);
                                                            setShowDeleteModale(true);
                                                        }}
                                                        variant={'destructive'}
                                                    >
                                                        <Trash2 />
                                                    </Button>
                                                </TableBodyData>
                                            </TableBodyRow>
                                        );
                                    })}
                            </TableBody>
                        </>
                    )}

                    {!isLoading && trashedAssetsTab && (
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

                                            <TableBodyData className="space-x-2">
                                                <Button onClick={() => restoreAsset(asset)} variant={'green'}>
                                                    <ArchiveRestore />
                                                    {/* Restore */}
                                                </Button>
                                                <Button
                                                    onClick={() => {
                                                        setAssetToDeleteDefinitely(asset.reference_code);
                                                        setShowDeleteDefinitelyModale(true);
                                                    }}
                                                    variant={'destructive'}
                                                >
                                                    <Shredder />
                                                    {/* Delete definitely */}
                                                </Button>
                                            </TableBodyData>
                                        </TableBodyRow>
                                    );
                                })}
                        </TableBody>
                    )}
                </Table>
            </div>
            <Modale
                title={'Delete asset'}
                message={`Are you sure you want to delete the asset ${assetToDelete?.maintainable.name} ? `}
                isOpen={showDeleteModale}
                onConfirm={deleteAsset}
                onCancel={() => {
                    setAssetToDelete(null);
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
