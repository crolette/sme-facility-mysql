import Modale from '@/components/Modale';
import { Pagination } from '@/components/pagination';
import { useGridTableLayoutContext } from '@/components/tenant/gridTableLayoutContext';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, CentralType, PaginatedData, TenantBuilding, TenantFloor, TenantRoom, TenantSite } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';

import { useLaravelReactI18n } from 'laravel-react-i18n';
import { LayoutGrid, Loader, Pencil, PlusCircle, TableIcon, Trash2, X } from 'lucide-react';

import { useEffect, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

export interface SearchParams {
    category: number | null;
    q: string | null;
    sortBy: string | null;
    orderBy: string | null;
}

export default function IndexSites({
    items,
    routeName,
    filters,
    categories,
}: {
    items: PaginatedData;
    routeName: string;
    filters: SearchParams;
    categories: CentralType[];
}) {
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${tChoice(`locations.${routeName}`, 2)}`,
            href: `/${routeName}`,
        },
    ];

    const [query, setQuery] = useState<SearchParams>({
        category: filters.category,
        q: filters.q,
        sortBy: filters.sortBy,
        orderBy: filters.orderBy,
    });

    const [prevQuery, setPrevQuery] = useState(query);
    const [isLoading, setIsLoading] = useState<boolean>(false);
    const [locations, setLocations] = useState(items.data);
    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
    const [locationToDelete, setLocationToDelete] = useState<TenantSite | TenantBuilding | TenantFloor | TenantRoom | null>(null);
    const { showToast } = useToast();

    const deleteLocation = async () => {
        try {
            const response = await axios.delete(route(`api.${routeName}.destroy`, locationToDelete?.reference_code));
            if (response.data.status === 'success') {
                setShowDeleteModale(false);
                setLocationToDelete(null);
                // fetchLocations();
                setQuery({ category: null, q: null, sortBy: null, orderBy: null });
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            setShowDeleteModale(false);
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const fetchLocations = async () => {
        try {
            const response = await axios.get(route(`api.${routeName}.index`));
            if (response.data.status === 'success') {
                setLocations(response.data.data.data);
            }
        } catch (error) {
            console.log(error);
        }
    };

    const setCategorySearch = (id: number) => {
        router.visit(route(`tenant.${routeName}.index`, { ...query, category: id ? id : null }), {
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
            router.visit(route(`tenant.${routeName}.index`, { ...query, q: debouncedSearch }), {
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
        router.visit(route(`tenant.${routeName}.index`), {
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
            router.visit(route(`tenant.${routeName}.index`, { ...query }), {
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
            <Head title={tChoice(`locations.${routeName}`, 2)} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="border-accent flex flex-col gap-2 border-b-2 pb-2 sm:flex-row sm:gap-10">
                    <details className="border-border relative w-full rounded-md border-2 p-1" open={isLoading ? false : undefined}>
                        <summary>{t('common.search_filter')}</summary>

                        <div className="bg-border border-border text-background dark:text-foreground absolute top-full z-10 flex flex-col items-center gap-4 rounded-b-md border-2 p-2">
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
                                    <Input type="text" value={search ?? ''} onChange={(e) => setSearch(e.target.value)} />
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
                        <a href={route(`tenant.${routeName}.create`)}>
                            <Button>
                                <PlusCircle />
                                {t('actions.create')}
                            </Button>
                        </a>
                        <a href={route('tenant.pdf.qr-codes', { type: routeName })} target="__blank">
                            <Button variant={'secondary'}>
                                <BiSolidFilePdf size={20} />
                                {t('actions.download-type', { type: tChoice('common.qr_codes', 2) })}
                            </Button>
                        </a>
                    </div>
                </div>

                <div className="flex gap-4">
                    <div className="bg-sidebar hover:bg-sidebar-accent cursor-pointer rounded-md p-2" onClick={() => setLayout('grid')}>
                        <LayoutGrid size={20} />
                    </div>
                    <div className="bg-sidebar hover:bg-sidebar-accent cursor-pointer rounded-md p-2" onClick={() => setLayout('table')}>
                        <TableIcon size={20} />
                    </div>
                </div>
                {layout === 'grid' ? (
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-3 xl:grid-cols-5">
                        {locations.map((item, index) => (
                            <div key={index} className="border-accent bg-sidebar flex flex-col gap-2 overflow-hidden rounded-md border-2 p-4">
                                <a href={route(`tenant.${routeName}.show`, item.reference_code)}> {item.reference_code} </a>
                                <p className="text-xs">{item.category ?? ''}</p>
                                <p className="text-xs">{item.code ?? ''}</p>
                                <p className="overflow-hidden text-xs overflow-ellipsis whitespace-nowrap">{item.description ?? ''}</p>
                            </div>
                        ))}
                    </div>
                ) : (
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
                            ) : (
                                locations &&
                                locations.map((item, index) => {
                                    return (
                                        <TableBodyRow key={index}>
                                            <TableBodyData>
                                                <a href={route(`tenant.${routeName}.show`, item.reference_code)}> {item.reference_code} </a>
                                            </TableBodyData>
                                            <TableBodyData>{item.code}</TableBodyData>
                                            <TableBodyData>{item.category}</TableBodyData>
                                            <TableBodyData>
                                                <span className="flex max-w-72">
                                                    <p className="overflow-hidden overflow-ellipsis whitespace-nowrap">{item.name}</p>
                                                </span>
                                            </TableBodyData>
                                            <TableBodyData>
                                                <span className="flex max-w-72">
                                                    <p className="overflow-hidden overflow-ellipsis whitespace-nowrap">{item.description}</p>
                                                </span>
                                            </TableBodyData>

                                            <TableBodyData className="space-x-2">
                                                <a href={route(`tenant.${routeName}.edit`, item.reference_code)}>
                                                    <Button>
                                                        <Pencil />
                                                    </Button>
                                                </a>
                                                <Button
                                                    onClick={() => {
                                                        setShowDeleteModale(true);
                                                        setLocationToDelete(item);
                                                    }}
                                                    variant={'destructive'}
                                                >
                                                    <Trash2 />
                                                </Button>
                                            </TableBodyData>
                                        </TableBodyRow>
                                    );
                                })
                            )}
                        </TableBody>
                    </Table>
                )}
                <Pagination items={items} />
            </div>
            <Modale
                title={`Delete ${routeName}`}
                message={`Are you sure you want to delete ${locationToDelete?.name}`}
                isOpen={showDeleteModale}
                onConfirm={deleteLocation}
                onCancel={() => {
                    setLocationToDelete(null);
                    setShowDeleteModale(false);
                }}
            />
        </AppLayout>
    );
}
