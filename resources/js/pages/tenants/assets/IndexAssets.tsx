import Modale from '@/components/Modale';
import { Pagination } from '@/components/pagination';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Asset, AssetsPaginated, BreadcrumbItem, CentralType } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { ArchiveRestore, Loader2, Pencil, PlusCircle, Shredder, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

export interface SearchParams {
    category: number | null;
    q: string | null;
    sortBy: string | null;
    orderBy: string | null;
    trashed: boolean;
}

export default function IndexAssets({ items, filters, categories }: { items: AssetsPaginated; filters: SearchParams; categories: CentralType[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index assets`,
            href: `/assets`,
        },
    ];

    const [query, setQuery] = useState<SearchParams>({
        category: filters.category,
        q: filters.q,
        sortBy: filters.sortBy,
        orderBy: filters.orderBy,
        trashed: filters.trashed === '1' ? true : false,
    });

    const [prevQuery, setPrevQuery] = useState(query);

    const { showToast } = useToast();

    const [assets, setAssets] = useState<Asset[]>(items.data);
    const [isLoading, setIsLoading] = useState<boolean>(false);

    const [activeAssetsTab, setActiveAssetsTab] = useState(query.trashed === false ? true : false);
    const [trashedAssetsTab, setTrashedAssetsTab] = useState(query.trashed === true ? true : false);

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
            console.log(response);
            setAssets(response.data.data.data);
            setIsLoading(false);
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const fetchTrashedAssets = async () => {
        setIsLoading(true);
        try {
            const response = await axios.get(route('api.assets.trashed'));
            setTrashedAssets(response.data.data.data);
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

    const [trashedAssets, setTrashedAssets] = useState<Asset[]>();
    const [search, setSearch] = useState(query.q);
    const [debouncedSearch, setDebouncedSearch] = useState<string>('');

    useEffect(() => {
        if (query.trashed !== trashedAssetsTab) {
            router.visit(route('tenant.assets.index', { trashed: trashedAssetsTab ? 1 : 0 }));
        }
    }, [trashedAssetsTab]);

    const setCategorySearch = (id: number) => {
        router.visit(route('tenant.assets.index', { ...query, category: id ? id : 0, trashed: trashedAssetsTab }));
    };

    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedSearch(search);
        }, 500);

        return () => {
            clearTimeout(handler);
        };
    }, [search]);

    useEffect(() => {
        if (query.q !== debouncedSearch && debouncedSearch?.length > 2) {
            router.visit(route('tenant.assets.index', { ...query, q: debouncedSearch }));
        }
    }, [debouncedSearch]);

    const clearSearch = () => {
        router.visit(route('tenant.assets.index', { ...query, q: null }));
    };

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
                <div className="flex flex-row items-center gap-4">
                    Search
                    <Label htmlFor="category">Category</Label>
                    <select name="category" id="category" value={query.category ?? 0} onChange={(e) => setCategorySearch(parseInt(e.target.value))}>
                        <option value={0} aria-readonly>
                            Select a category
                        </option>
                        {categories.map((category) => (
                            <option key={category.label} value={category.id}>
                                {category.label}
                            </option>
                        ))}
                    </select>
                    <div className="flex items-center gap-2">
                        <Label htmlFor="category">Search</Label>
                        <Input type="text" value={search ?? ''} onChange={(e) => setSearch(e.target.value)} />
                        <Button onClick={clearSearch} size={'xs'}>
                            Clear Search
                        </Button>
                    </div>
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
                    {!isLoading && (
                        <>
                            <TableBody>
                                {assets.length > 0 ? (
                                    assets.map((asset, index) => {
                                        return (
                                            <TableBodyRow key={index}>
                                                <TableBodyData>
                                                    {asset.deleted_at ? (
                                                        <a href={route(`tenant.assets.deleted`, asset.id)}> {asset.reference_code} </a>
                                                    ) : (
                                                        <a href={route(`tenant.assets.show`, asset.reference_code)}> {asset.reference_code} </a>
                                                    )}
                                                </TableBodyData>
                                                <TableBodyData>{asset.code}</TableBodyData>
                                                <TableBodyData>{asset.category}</TableBodyData>
                                                <TableBodyData>{asset.maintainable.name}</TableBodyData>
                                                <TableBodyData>{asset.maintainable.description}</TableBodyData>

                                                <TableBodyData className="space-x-2">
                                                    {asset.deleted_at ? (
                                                        <>
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
                                                        </>
                                                    ) : (
                                                        <>
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
                                                        </>
                                                    )}
                                                </TableBodyData>
                                            </TableBodyRow>
                                        );
                                    })
                                ) : (
                                    <TableBodyRow key={0}>
                                        <TableBodyData>No results...</TableBodyData>
                                    </TableBodyRow>
                                )}
                            </TableBody>
                        </>
                    )}
                </Table>

                {/* pagination */}
                <Pagination items={items} />
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
