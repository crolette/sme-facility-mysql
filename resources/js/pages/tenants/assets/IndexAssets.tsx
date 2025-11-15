import Modale from '@/components/Modale';
import { Pagination } from '@/components/pagination';
import { useGridTableLayoutContext } from '@/components/tenant/gridTableLayoutContext';
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
import { ArchiveRestore, LayoutGrid, Loader, Pencil, PlusCircle, Shredder, TableIcon, Trash2, X } from 'lucide-react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

import { useEffect, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

export interface SearchParams {
    category: number | null;
    q: string | null;
    sortBy: string | null;
    orderBy: string | null;
    trashed: boolean | null;
}

export default function IndexAssets({ items, filters, categories }: { items: AssetsPaginated; filters: SearchParams; categories: CentralType[] }) {
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${tChoice('assets.title', 2)}`,
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
                setQuery({ category: null, q: null, sortBy: null, orderBy: null, trashed: null });
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
                // setTrashedAssetsTab(!trashedAssetsTab);
                // setActiveAssetsTab(!activeAssetsTab);
                // fetchAssets();
                setQuery({ category: null, q: null, sortBy: null, orderBy: null, trashed: null });
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
                // setSearch('');
                // fetchAssets();
                setShowDeleteModale(!showDeleteModale);
                setQuery({ category: null, q: null, sortBy: null, orderBy: null, trashed: null });
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const [trashedAssets, setTrashedAssets] = useState<Asset[]>();

    const [prevQuery, setPrevQuery] = useState(query);

    useEffect(() => {
        if (query.trashed !== trashedAssetsTab) {
            router.visit(route('tenant.assets.index', { trashed: trashedAssetsTab ? 1 : 0 }), {
                onStart: () => {
                    setIsLoading(true);
                },
                onFinish: () => {
                    setIsLoading(false);
                },
            });
        }
    }, [trashedAssetsTab]);

    const setCategorySearch = (id: number) => {
        router.visit(route('tenant.assets.index', { ...query, category: id ? id : null, trashed: trashedAssetsTab }), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    const [search, setSearch] = useState(query.q);
    const [debouncedSearch, setDebouncedSearch] = useState<string>('');

    useEffect(() => {
        if (!search) return;

        const handler = setTimeout(() => {
            setDebouncedSearch(search);
        }, 500);

        return () => {
            clearTimeout(handler);
        };
    }, [search]);

    useEffect(() => {
        if (query.q !== debouncedSearch && debouncedSearch?.length > 2) {
            router.visit(route('tenant.assets.index', { ...query, q: debouncedSearch }), {
                onStart: () => {
                    setIsLoading(true);
                },
                onFinish: () => {
                    setIsLoading(false);
                },
            });
        }
    }, [debouncedSearch]);

    const clearSearch = () => {
        router.visit(route('tenant.assets.index'), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    useEffect(() => {
        if (query !== prevQuery)
            router.visit(route('tenant.assets.index', { ...query }), {
                onStart: () => {
                    setIsLoading(true);
                },
                onFinish: () => {
                    setIsLoading(false);
                },
            });
    }, [query]);

    const { layout, setLayout } = useGridTableLayoutContext();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={tChoice('assets.title', 2)} />

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
                            {t('common.active')}
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
                            {t('assets.trashed')}
                        </li>
                    </ul>
                </div>
                <div>
                    <div className="border-accent flex flex-col gap-2 border-b-2 pb-2 sm:flex-row sm:gap-10">
                        <details className="border-border relative w-full rounded-md border-2 p-1" open={isLoading ? false : undefined}>
                            <summary>{t('common.search_filter')}</summary>

                            <div className="bg-border border-border text-background dark:text-foreground absolute top-full z-10 flex flex-col items-center gap-4 rounded-b-md border-2 p-2 lg:flex-row">
                                <div className="flex flex-col items-center gap-2">
                                    <Label htmlFor="category">{t('common.category')}</Label>
                                    <select
                                        name="category"
                                        id="category"
                                        value={query.category ?? 0}
                                        onChange={(e) => setCategorySearch(parseInt(e.target.value))}
                                    >
                                        <option value={0} aria-readonly>
                                            {t('actions.select-type', { type: t('common.category') })}
                                        </option>
                                        {categories.map((category) => (
                                            <option key={category.label} value={category.id}>
                                                {category.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="flex flex-col items-center gap-2">
                                    <Label htmlFor="category">{t('actions.search')}</Label>
                                    <div className="relative text-black dark:text-white">
                                        <Input type="text" value={search ?? ''} onChange={(e) => setSearch(e.target.value)} className="" />
                                        <X
                                            onClick={() => setQuery((prev) => ({ ...prev, q: null }))}
                                            className={'absolute top-1/2 right-0 -translate-1/2'}
                                        />
                                    </div>
                                </div>
                                <Button onClick={clearSearch} size={'xs'}>
                                    {t('actions.search-clear')}
                                </Button>
                            </div>
                        </details>
                        <div className="flex space-x-2">
                            <a href={route(`tenant.assets.create`)} className="w-fit">
                                <Button>
                                    <PlusCircle />
                                    {t('actions.create')}
                                </Button>
                            </a>
                            <a href={route('tenant.pdf.qr-codes', { type: 'assets' })} target="__blank">
                                <Button variant={'secondary'}>
                                    <BiSolidFilePdf size={20} />
                                    {t('actions.download-type', { type: tChoice('common.qr_codes', 2) })}
                                </Button>
                            </a>
                        </div>
                    </div>
                </div>

                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>{t('common.reference_code')}</TableHeadData>
                            <TableHeadData>{t('common.code')}</TableHeadData>
                            <TableHeadData>{t('common.category')}</TableHeadData>
                            <TableHeadData className="max-w-72">{t('common.name')}</TableHeadData>
                            <TableHeadData className="max-w-72">{t('common.description')}</TableHeadData>
                            <TableHeadData></TableHeadData>
                        </TableHeadRow>
                    </TableHead>
                    <TableBody>
                        {isLoading ? (
                            <TableBodyRow>
                                <TableBodyData>
                                    <p className="flex animate-pulse gap-2">
                                        <Loader />
                                        {t('actions.searching')}
                                    </p>
                                </TableBodyData>
                            </TableBodyRow>
                        ) : assets.length > 0 ? (
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
                                        <TableBodyData>
                                            <span className="flex max-w-72">
                                                <p className="overflow-hidden overflow-ellipsis whitespace-nowrap">{asset.maintainable.name}</p>
                                            </span>
                                        </TableBodyData>
                                        <TableBodyData>
                                            <span className="flex max-w-72">
                                                <p className="overflow-hidden overflow-ellipsis whitespace-nowrap">
                                                    {asset.maintainable.description}
                                                </p>
                                            </span>
                                        </TableBodyData>

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
                                                ) 
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
                    </Table>
                )}

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
