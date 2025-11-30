import Modale from '@/components/Modale';
import { Pagination } from '@/components/pagination';
import { useGridTableLayoutContext } from '@/components/tenant/gridTableLayoutContext';
import { Button } from '@/components/ui/button';
import DisplayGridTableIndex from '@/components/ui/displayGridTableIndex';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pill } from '@/components/ui/pill';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { usePermissions } from '@/hooks/usePermissions';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { BreadcrumbItem, CentralType, Contract, ContractsPaginated } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ArrowDownNarrowWide, ArrowDownWideNarrow, Loader, Pencil, PlusCircle, Trash2, X } from 'lucide-react';
import { useEffect, useState } from 'react';

export interface SearchParams {
    q: string | null;
    sortBy: string | null;
    orderBy: string | null;
    type: string | null;
    status: string | null;
    provider_category_id: number | null;
    renewalType: string | null;
}

export default function IndexContracts({
    items,
    contractTypes,
    filters,
    statuses,
    renewalTypes,
    providerCategories,
}: {
    items: ContractsPaginated;
    filters: SearchParams;
    contractTypes: string[];
    statuses: string[];
    renewalTypes: string[];
    providerCategories: CentralType[];
}) {
    const { hasPermission } = usePermissions();
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${tChoice('contracts.title', 2)}`,
            href: `/contracts`,
        },
    ];

    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
    const [contractToDelete, setContractToDelete] = useState<Contract | null>(null);

    const deleteContract = async () => {
        if (!contractToDelete) return;

        try {
            const response = await axios.delete(route('api.contracts.destroy', contractToDelete.id));
            if (response.data.status === 'success') {
                router.visit(route('tenant.contracts.index'));
                setShowDeleteModale(false);
            }
        } catch (error) {
            console.log(error);
        }
    };

    const [isLoading, setIsLoading] = useState<boolean>(false);

    const [query, setQuery] = useState<SearchParams>({
        q: filters.q ?? null,
        sortBy: filters.sortBy ?? null,
        orderBy: filters.orderBy ?? null,
        status: filters.status ?? null,
        type: filters.type ?? null,
        provider_category_id: filters.provider_category_id ?? null,
        renewalType: filters.renewalType ?? null,
    });

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
            router.visit(route('tenant.contracts.index', { ...query, q: debouncedSearch }), {
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
        router.visit(route('tenant.contracts.index'), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    const setRenewalTypeSearch = (renewalType: string | null) => {
        if (renewalType === query.renewalType) {
            renewalType = null;
        }
        router.visit(route('tenant.contracts.index', { ...query, renewalType: renewalType }), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    const setStatusSearch = (status: string | null) => {
        if (status === query.status) {
            status = null;
        }
        router.visit(route('tenant.contracts.index', { ...query, status: status }), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    const setTypeSearch = (type: string | null) => {
        if (type === query.type) {
            type = null;
        }
        router.visit(route('tenant.contracts.index', { ...query, type: type }), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    const [prevQuery, setPrevQuery] = useState(query);

    useEffect(() => {
        if (query !== prevQuery)
            router.visit(route('tenant.contracts.index', { ...query }), {
                onStart: () => {
                    setIsLoading(true);
                },
                onFinish: () => {
                    setIsLoading(false);
                },
            });
    }, [query]);

    const { layout } = useGridTableLayoutContext();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={tChoice('contracts.title', 2)} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="border-accent flex flex-col gap-2 border-b-2 pb-2 sm:flex-row sm:gap-10">
                    <details className="border-border relative w-full cursor-pointer rounded-md border-2 p-1" open={isLoading ? false : undefined}>
                        <summary>{t('common.search_filter')}</summary>

                        <div className="bg-border border-border text-background dark:text-foreground absolute top-full z-10 flex flex-col items-center gap-4 rounded-b-md border-2 p-2 lg:flex-row">
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="role">{t('contracts.renewal_type')}</Label>
                                <div className="space-x-1 text-center">
                                    {renewalTypes.map((renewalType) => (
                                        <Pill
                                            key={renewalType}
                                            variant={query.renewalType === renewalType ? 'active' : ''}
                                            onClick={() => setRenewalTypeSearch(renewalType)}
                                            className="cursor-pointer"
                                        >
                                            {t(`contracts.renewal_type.${renewalType}`)}
                                        </Pill>
                                    ))}
                                </div>
                            </div>
                            <div className="border-foreground hidden h-10 w-0.5 border lg:block"></div>
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="role">{t('common.type')}</Label>
                                <div className="space-x-1 text-center">
                                    {contractTypes.map((contractType) => (
                                        <Pill
                                            key={contractType}
                                            variant={query.type === contractType ? 'active' : ''}
                                            onClick={() => setTypeSearch(contractType)}
                                            className="cursor-pointer"
                                        >
                                            {t(`contracts.type.${contractType}`)}
                                        </Pill>
                                    ))}
                                </div>
                            </div>
                            <div className="border-foreground hidden h-10 w-0.5 border lg:block"></div>
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="provider_category">{t(`common.category`)}</Label>
                                <div className="space-x-1 text-center">
                                    <select
                                        name="provider_category"
                                        id="provider_category"
                                        value={query.provider_category_id ?? ''}
                                        onChange={(e) => setQuery((prev) => ({ ...prev, provider_category_id: e.target.value }))}
                                    >
                                        <option value="">{t('actions.select-type', { type: t('common.type') })}</option>
                                        {providerCategories.map((category) => (
                                            <option value={category.id}>{category.label}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                            <div className="border-foreground hidden h-10 w-0.5 border lg:block"></div>
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="status">{t('common.status')}</Label>
                                <div className="space-x-1 text-center">
                                    {statuses.map((status) => (
                                        <Pill
                                            key={status}
                                            variant={query.status === status ? 'active' : ''}
                                            onClick={() => setStatusSearch(status)}
                                            className="cursor-pointer"
                                        >
                                            {t(`contracts.status.${status}`)}
                                        </Pill>
                                    ))}
                                </div>
                            </div>
                            <div className="border-foreground hidden h-10 w-0.5 border lg:block"></div>
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
                            <div className="border-foreground hidden h-10 w-0.5 border lg:block"></div>
                            <Button onClick={clearSearch} size={'sm'}>
                                {t('actions.search-clear')}
                            </Button>
                        </div>
                    </details>

                    {hasPermission('create contracts') && (
                        <a href={route('tenant.contracts.create')}>
                            <Button>
                                <PlusCircle />
                                {t('actions.add-type', { type: tChoice('contracts.title', 1) })}
                            </Button>
                        </a>
                    )}
                </div>

                <div className="flex w-full flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h1>{tChoice(`contracts.title`, 2)}</h1>
                    <DisplayGridTableIndex />
                </div>

                {layout === 'grid' ? (
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-3 xl:grid-cols-5">
                        {items.data.map((contract, index) => (
                            <div key={index} className="border-accent bg-sidebar flex flex-col gap-2 overflow-hidden rounded-md border-2 p-4">
                                <a href={route(`tenant.contracts.show`, contract.id)}> {contract.name} </a>
                                <p className="text-xs">{contract.type}</p>
                                <p className="text-xs">{contract.provider?.category}</p>
                                {contract.provider && <a href={route(`tenant.providers.show`, contract.provider?.id)}> {contract.provider?.name} </a>}
                                <Pill variant={contract.status}>{t(`contracts.status.${contract.status}`)}</Pill>
                                <p>{contract.internal_reference}</p>
                                <p className="text-xs">
                                    {t('contracts.end_date')} : {contract.end_date}
                                </p>
                            </div>
                        ))}
                    </div>
                ) : (
                    <Table>
                        <TableHead>
                            <TableHeadRow>
                                <TableHeadData>{t('common.name')}</TableHeadData>
                                <TableHeadData>{t('common.type')}</TableHeadData>
                                <TableHeadData>{t('common.category')}</TableHeadData>
                                <TableHeadData>{t('common.status')}</TableHeadData>
                                <TableHeadData>{t('contracts.internal_ref')}</TableHeadData>
                                <TableHeadData>{t('contracts.provider_ref')}</TableHeadData>
                                <TableHeadData>{t('contracts.renewal_type')}</TableHeadData>
                                <TableHeadData>{tChoice('providers.title', 1)}</TableHeadData>
                                <TableHeadData>
                                    <div className="flex items-center gap-2">
                                        <ArrowDownNarrowWide
                                            size={16}
                                            className={cn(
                                                'cursor-pointer',
                                                query.sortBy === 'end_date' && query.orderBy === 'asc' ? 'text-amber-300' : '',
                                                !query.sortBy && !query.orderBy ? 'text-amber-300' : '',
                                            )}
                                            onClick={() => setQuery((prev) => ({ ...prev, sortBy: 'end_date', orderBy: 'asc' }))}
                                        />
                                        {t('contracts.end_date')}
                                        <ArrowDownWideNarrow
                                            size={16}
                                            className={cn(
                                                'cursor-pointer',
                                                query.sortBy === 'end_date' && query.orderBy === 'desc' ? 'text-amber-300' : '',
                                            )}
                                            onClick={() => setQuery((prev) => ({ ...prev, sortBy: 'end_date', orderBy: 'desc' }))}
                                        />
                                    </div>
                                </TableHeadData>
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
                            ) : items.data.length > 0 ? (
                                items.data.map((contract) => {
                                    return (
                                        <TableBodyRow key={contract.id}>
                                            <TableBodyData>
                                                <a href={route(`tenant.contracts.show`, contract.id)}> {contract.name} </a>
                                            </TableBodyData>
                                            <TableBodyData>{t(`contracts.type.${contract.type}`)}</TableBodyData>
                                            <TableBodyData>{contract.provider?.category}</TableBodyData>
                                            <TableBodyData>
                                                <Pill variant={contract.status}>{t(`contracts.status.${contract.status}`)}</Pill>
                                            </TableBodyData>
                                            <TableBodyData>{contract.internal_reference}</TableBodyData>
                                            <TableBodyData>{contract.provider_reference}</TableBodyData>
                                            <TableBodyData>{t(`contracts.renewal_type.${contract.renewal_type}`)}</TableBodyData>
                                            <TableBodyData>
                                                {contract.provider && (
                                                    <a href={route(`tenant.providers.show`, contract.provider?.id)}> {contract.provider?.name} </a>
                                                )}
                                            </TableBodyData>
                                            <TableBodyData>{contract.end_date}</TableBodyData>

                                            <TableBodyData className="flex space-x-2">
                                                {hasPermission('update contracts') && (
                                                    <a href={route(`tenant.contracts.edit`, contract.id)}>
                                                        <Button>
                                                            <Pencil />
                                                        </Button>
                                                    </a>
                                                )}
                                                {hasPermission('delete contracts') && (
                                                    <Button
                                                        onClick={() => {
                                                            setContractToDelete(contract);
                                                            setShowDeleteModale(true);
                                                        }}
                                                        variant={'destructive'}
                                                    >
                                                        <Trash2 />
                                                    </Button>
                                                )}
                                            </TableBodyData>
                                        </TableBodyRow>
                                    );
                                })
                            ) : (
                                <TableBodyRow key={0}>
                                    <TableBodyData>{t('common.no_results')}</TableBodyData>
                                </TableBodyRow>
                            )}
                        </TableBody>
                    </Table>
                )}
                <Pagination items={items} />
            </div>
            <Modale
                title={t('actions.delete-type', { type: tChoice('contracts.title', 1) })}
                message={t(`contracts.delete_description`, { name: contractToDelete?.name ?? '' })}
                isOpen={showDeleteModale}
                onConfirm={deleteContract}
                onCancel={() => {
                    setShowDeleteModale(false);
                    setContractToDelete(null);
                }}
            />
        </AppLayout>
    );
}
