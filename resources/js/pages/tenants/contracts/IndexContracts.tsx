import Modale from '@/components/Modale';
import { Pagination } from '@/components/pagination';
import { useGridTableLayoutContext } from '@/components/tenant/gridTableLayoutContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pill } from '@/components/ui/pill';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { BreadcrumbItem, CentralType, Contract, ContractsPaginated } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { ArrowDownNarrowWide, ArrowDownWideNarrow, LayoutGrid, Loader, Pencil, PlusCircle, TableIcon, Trash2, X } from 'lucide-react';
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
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index contracts`,
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

    // const removeContract = async (contract_id: number) => {
    //     if (!contractableReference) return;

    //     try {
    //         const response = await axios.delete(route(`api.${routeName}.contracts.delete`, contractableReference), {
    //             data: { contract_id: contract_id },
    //         });
    //         if (response.data.status === 'success') {
    //             router.visit(route('tenant.contracts.index'));
    //         }
    //     } catch {
    //         // console.log(error);
    //     }
    // };

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

    const { layout, setLayout } = useGridTableLayoutContext();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Contracts" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex w-full justify-between">
                    <details className="border-border relative w-full cursor-pointer rounded-md border-2 p-1" open={isLoading ? false : undefined}>
                        <summary>Search/Filter</summary>

                        <div className="bg-border border-border text-background dark:text-foreground absolute top-full z-10 flex flex-col items-center gap-4 rounded-b-md border-2 p-2 lg:flex-row">
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="role">Renewal type</Label>
                                <div className="space-x-1 text-center">
                                    {renewalTypes.map((renewalType) => (
                                        <Pill
                                            key={renewalType}
                                            variant={query.renewalType === renewalType ? 'active' : ''}
                                            onClick={() => setRenewalTypeSearch(renewalType)}
                                            className="cursor-pointer"
                                        >
                                            {renewalType}
                                        </Pill>
                                    ))}
                                </div>
                            </div>
                            <div className="border-foreground block h-10 w-0.5 border"></div>
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="role">Type</Label>
                                <div className="space-x-1 text-center">
                                    {contractTypes.map((contractType) => (
                                        <Pill
                                            key={contractType}
                                            variant={query.type === contractType ? 'active' : ''}
                                            onClick={() => setTypeSearch(contractType)}
                                            className="cursor-pointer"
                                        >
                                            {contractType}
                                        </Pill>
                                    ))}
                                </div>
                            </div>
                            <div className="border-foreground block h-10 w-0.5 border"></div>
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="provider_category">Category</Label>
                                <div className="space-x-1 text-center">
                                    <select
                                        name="provider_category"
                                        id="provider_category"
                                        value={query.provider_category_id ?? ''}
                                        onChange={(e) => setQuery((prev) => ({ ...prev, provider_category_id: e.target.value }))}
                                    >
                                        <option value="">Select a category</option>
                                        {providerCategories.map((category) => (
                                            <option value={category.id}>{category.label}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                            <div className="border-foreground block h-10 w-0.5 border"></div>
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="status">Status</Label>
                                <div className="space-x-1 text-center">
                                    {statuses.map((status) => (
                                        <Pill
                                            key={status}
                                            variant={query.status === status ? 'active' : ''}
                                            onClick={() => setStatusSearch(status)}
                                            className="cursor-pointer"
                                        >
                                            {status}
                                        </Pill>
                                    ))}
                                </div>
                            </div>
                            <div className="border-foreground block h-10 w-0.5 border"></div>
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="category">Search</Label>
                                <div className="relative text-black dark:text-white">
                                    <Input type="text" value={search ?? ''} onChange={(e) => setSearch(e.target.value)} />
                                    <X
                                        onClick={() => setQuery((prev) => ({ ...prev, q: null }))}
                                        className={'absolute top-1/2 right-0 -translate-1/2'}
                                    />
                                </div>
                            </div>
                            <div className="border-foreground block h-10 w-0.5 border"></div>
                            <Button onClick={clearSearch} size={'sm'}>
                                Clear Search
                            </Button>
                        </div>
                    </details>

                    <a href={route('tenant.contracts.create')}>
                        <Button>
                            <PlusCircle />
                            Create
                        </Button>
                    </a>
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
                        {items.data.map((contract, index) => (
                            <div key={index} className="border-accent bg-sidebar flex flex-col gap-2 overflow-hidden rounded-md border-2 p-4">
                                <a href={route(`tenant.contracts.show`, contract.id)}> {contract.name} </a>
                                <p className="text-xs">{contract.type}</p>
                                <p className="text-xs">{contract.provider?.category}</p>
                                {contract.provider && <a href={route(`tenant.providers.show`, contract.provider?.id)}> {contract.provider?.name} </a>}
                                <Pill variant={contract.status}>{contract.status}</Pill>
                                <p>{contract.internal_reference}</p>
                                <p className="text-xs">End date : {contract.end_date}</p>
                            </div>
                        ))}
                    </div>
                ) : (
                    <Table>
                        <TableHead>
                            <TableHeadRow>
                                <TableHeadData>Name</TableHeadData>
                                <TableHeadData>Type</TableHeadData>
                                <TableHeadData>Category</TableHeadData>
                                <TableHeadData>Status</TableHeadData>
                                <TableHeadData>Internal #</TableHeadData>
                                <TableHeadData>Provider #</TableHeadData>
                                <TableHeadData>Renewal</TableHeadData>
                                <TableHeadData>Provider</TableHeadData>
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
                                        End date
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
                                            Searching...
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
                                            <TableBodyData>{contract.type}</TableBodyData>
                                            <TableBodyData>{contract.provider?.category}</TableBodyData>
                                            <TableBodyData>
                                                <Pill variant={contract.status}>{contract.status}</Pill>
                                            </TableBodyData>
                                            <TableBodyData>{contract.internal_reference}</TableBodyData>
                                            <TableBodyData>{contract.provider_reference}</TableBodyData>
                                            <TableBodyData>{contract.renewal_type}</TableBodyData>
                                            <TableBodyData>
                                                {contract.provider && (
                                                    <a href={route(`tenant.providers.show`, contract.provider?.id)}> {contract.provider?.name} </a>
                                                )}
                                            </TableBodyData>
                                            <TableBodyData>{contract.end_date}</TableBodyData>

                                            <TableBodyData className="flex space-x-2">
                                                <a href={route(`tenant.contracts.edit`, contract.id)}>
                                                    <Button>
                                                        <Pencil />
                                                    </Button>
                                                </a>
                                                <Button
                                                    onClick={() => {
                                                        setContractToDelete(contract);
                                                        setShowDeleteModale(true);
                                                    }}
                                                    variant={'destructive'}
                                                >
                                                    <Trash2 />
                                                </Button>
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
                <Pagination items={items} />
                {/* <ContractsList getUrl={'api.contracts.index'} items={items} editable isLoading={isLoading} /> */}
            </div>
            <Modale
                title={'Delete contract'}
                message={`Are you sure you want to delete this contract ${contractToDelete?.name} ?`}
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
