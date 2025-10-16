import Modale from '@/components/Modale';
import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pill } from '@/components/ui/pill';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Contract, ContractsPaginated } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { Loader, Pencil, PlusCircle, Trash2, X } from 'lucide-react';
import { useEffect, useState } from 'react';

export interface SearchParams {
    q: string | null;
    sortBy: string | null;
    orderBy: string | null;
    type: string | null;
    status: string | null;
    provider: string | null;
    renewalType: string | null;
}

export default function IndexContracts({
    items,
    filters,
    statuses,
    renewalTypes,
}: {
    items: ContractsPaginated;
    filters: SearchParams;
    statuses: string[];
    renewalTypes: string[];
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

    const removeContract = async (contract_id: number) => {
        if (!contractableReference) return;

        try {
            const response = await axios.delete(route(`api.${routeName}.contracts.delete`, contractableReference), {
                data: { contract_id: contract_id },
            });
            if (response.data.status === 'success') {
                router.visit(route('tenant.contracts.index'));
            }
        } catch {
            // console.log(error);
        }
    };

    const [isLoading, setIsLoading] = useState<boolean>(false);

    const [query, setQuery] = useState<SearchParams>({
        q: filters.q ?? null,
        sortBy: filters.sortBy ?? null,
        orderBy: filters.orderBy ?? null,
        status: filters.status ?? null,
        type: filters.type ?? null,
        provider: filters.provider ?? null,
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

    const [providerSearch, setProviderSearch] = useState(query.provider);
    const [debouncedProviderSearch, setDebouncedProviderSearch] = useState<string>('');

    useEffect(() => {
        if (!providerSearch) return;

        const handler = setTimeout(() => {
            setDebouncedProviderSearch(providerSearch);
        }, 500);

        return () => {
            clearTimeout(handler);
        };
    }, [providerSearch]);

    useEffect(() => {
        if (query.provider !== debouncedProviderSearch && debouncedProviderSearch?.length > 2) {
            router.visit(route('tenant.contracts.index', { ...query, provider: debouncedProviderSearch }), {
                onStart: () => {
                    setIsLoading(true);
                },
                onFinish: () => {
                    setIsLoading(false);
                },
            });
        }
    }, [debouncedProviderSearch]);

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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Contracts" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex w-full justify-between">
                    <details className="border-border relative w-full cursor-pointer rounded-md border-2 p-1" open={isLoading ? false : undefined}>
                        <summary>Search</summary>

                        <div className="bg-border border-border text-background dark:text-foreground absolute top-full flex flex-col items-center gap-4 rounded-b-md border-2 p-2 sm:flex-row">
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="role">Renewal type</Label>
                                <div className="space-x-1">
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
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="canLogin">Status</Label>
                                <div className="space-x-1">
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
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="category">Provider Search</Label>
                                <div className="relative text-black dark:text-white">
                                    <Input type="text" value={providerSearch ?? ''} onChange={(e) => setProviderSearch(e.target.value)} />
                                    <X
                                        onClick={() => setQuery((prev) => ({ ...prev, provider: null }))}
                                        className={'absolute top-1/2 right-0 -translate-1/2'}
                                    />
                                </div>
                            </div>
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

                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>Name</TableHeadData>
                            <TableHeadData>Type</TableHeadData>
                            <TableHeadData>Status</TableHeadData>
                            <TableHeadData>Internal #</TableHeadData>
                            <TableHeadData>Provider #</TableHeadData>
                            <TableHeadData>Renewal</TableHeadData>
                            <TableHeadData>Provider</TableHeadData>
                            <TableHeadData>End date</TableHeadData>
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
                                        <TableBodyData>
                                            <Pill variant={contract.status}>{contract.status}</Pill>
                                        </TableBodyData>
                                        <TableBodyData>{contract.internal_reference}</TableBodyData>
                                        <TableBodyData>{contract.provider_reference}</TableBodyData>
                                        <TableBodyData>{contract.renewal_type}</TableBodyData>
                                        <TableBodyData>
                                            <a href={route(`tenant.providers.show`, contract.provider?.id)}> {contract.provider?.name} </a>
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
