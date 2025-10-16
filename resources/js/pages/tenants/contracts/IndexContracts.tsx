import { ContractsList } from '@/components/tenant/contractsList';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pill } from '@/components/ui/pill';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, ContractsPaginated } from '@/types';
import { Head, router } from '@inertiajs/react';
import { PlusCircle, X } from 'lucide-react';
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
        router.visit(route('tenant.contracts.index'));
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
        if (query !== prevQuery) router.visit(route('tenant.contracts.index', { ...query }));
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
                                <div className="relative">
                                    <Input type="text" value={search ?? ''} onChange={(e) => setSearch(e.target.value)} />
                                    <X
                                        onClick={() => setQuery((prev) => ({ ...prev, q: null }))}
                                        className={'absolute top-1/2 right-0 -translate-1/2'}
                                    />
                                </div>
                            </div>
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="category">Provider Search</Label>
                                <div className="relative">
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
                <ContractsList getUrl={'api.contracts.index'} items={items} editable isLoading={isLoading} />
            </div>
        </AppLayout>
    );
}
