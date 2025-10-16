import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pill } from '@/components/ui/pill';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, InterventionStatus, PaginatedData, PriorityLevel } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Loader, Pencil, Trash2, X } from 'lucide-react';
import { useEffect, useState } from 'react';

export interface SearchParams {
    q: string | null;
    sortBy: string | null;
    orderBy: string | null;
    status: string | null;
    type: string | null;
    priority: string | null;
}

export default function IndexInterventions({
    items,
    filters,
    statuses,
    priorities,
}: {
    items: PaginatedData;
    filters: SearchParams;
    statuses: InterventionStatus;
    priorities: PriorityLevel;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index interventions`,
            href: `/interventions`,
        },
    ];
    const [isLoading, setIsLoading] = useState<boolean>(false);

    const [query, setQuery] = useState<SearchParams>({
        q: filters.q ?? null,
        sortBy: filters.sortBy ?? null,
        orderBy: filters.orderBy ?? null,
        status: filters.status ?? null,
        type: filters.type ?? null,
        priority: filters.priority ?? null,
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
            router.visit(route('tenant.interventions.index', { ...query, q: debouncedSearch }), {
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
        router.visit(route('tenant.interventions.index'), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    const setStatusSearch = (status: string) => {
        console.log(status);
        router.visit(route('tenant.interventions.index', { ...query, status: status ?? '' }), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    const setPrioritySearch = (priority: string) => {
        router.visit(route('tenant.interventions.index', { ...query, priority: priority }), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

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
            router.visit(route('tenant.users.index', { ...query, provider: debouncedProviderSearch }), {
                onStart: () => {
                    setIsLoading(true);
                },
                onFinish: () => {
                    setIsLoading(false);
                },
            });
        }
    }, [debouncedProviderSearch]);

    const [prevQuery, setPrevQuery] = useState(query);

    useEffect(() => {
        if (query !== prevQuery)
            router.visit(route('tenant.users.index', { ...query }), {
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
            <Head title="Sites" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex w-full justify-between">
                    <details className="border-border relative w-full cursor-pointer rounded-md border-2 p-1" open={isLoading ? false : undefined}>
                        <summary>Search</summary>

                        <div className="bg-border border-border text-background dark:text-foreground absolute top-full flex flex-col items-center gap-4 rounded-b-md border-2 p-2 sm:flex-row">
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="status">status</Label>
                                <select name="status" id="status" value={query.status ?? ''} onChange={(e) => setStatusSearch(e.target.value)}>
                                    <option value={''} aria-readonly>
                                        Select a status
                                    </option>
                                    {statuses.map((status) => (
                                        <option value={status}>{status}</option>
                                    ))}

                                    <option value={'manager'}>Maintenance Manager</option>
                                </select>
                            </div>
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="canLogin">canLogin</Label>
                                <div className="space-x-1">
                                    {priorities.map((priority) => (
                                        <Pill variant={query.status === priority ? 'active' : ''} onClick={() => setPrioritySearch(priority)}>
                                            {priority}
                                        </Pill>
                                    ))}

                                    {/* <Pill variant={query.canLogin === 'no' ? 'active' : ''} onClick={() => setCanLoginSearch('no')}>
                                        No
                                    </Pill>
                                    <Pill variant={query.canLogin === null ? 'active' : ''} onClick={() => setCanLoginSearch('all')}>
                                        All
                                    </Pill> */}
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

                            <Button onClick={clearSearch} size={'sm'}>
                                Clear Search
                            </Button>
                        </div>
                    </details>
                    {/* <a href={route(`tenant.users.create`)}>
                        <Button>
                            <PlusCircle />
                            Create user
                        </Button>
                    </a> */}
                </div>
                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>Type</TableHeadData>
                            <TableHeadData className="w-32">Description</TableHeadData>
                            <TableHeadData>Priority</TableHeadData>
                            <TableHeadData>Status</TableHeadData>
                            <TableHeadData>Planned at</TableHeadData>
                            <TableHeadData>Repair delay</TableHeadData>
                            <TableHeadData>Total costs</TableHeadData>
                            <TableHeadData>
                                {/* <Button onClick={() => sendIntervention(intervention.id)} variant={'secondary'}> */}
                                Send to provider
                                {/* </Button> */}
                            </TableHeadData>
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
                            items.data.map((item, index) => {
                                return (
                                    <TableBodyRow key={index}>
                                        <TableBodyData>
                                            <a href={route('tenant.interventions.show', item.id)}>{item.type}</a>
                                        </TableBodyData>
                                        <TableBodyData className="overflow-ellipsis">{item.description}</TableBodyData>
                                        <TableBodyData>
                                            <Pill variant={item.priority}>{item.priority}</Pill>
                                        </TableBodyData>
                                        <TableBodyData>{item.status}</TableBodyData>
                                        <TableBodyData>{item.planned_at ?? 'Not planned'}</TableBodyData>
                                        <TableBodyData>{item.repair_delay ?? 'No repair delay'}</TableBodyData>
                                        <TableBodyData>{item.total_costs ? `${item.total_costs} €` : '-'}</TableBodyData>
                                        <TableBodyData className="flex space-x-2">
                                            {!closed && (
                                                <>
                                                    <Button
                                                    // onClick={() => editIntervention(item.id)}
                                                    >
                                                        <Pencil />
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        variant="destructive"
                                                        // onClick={() => {
                                                        //     setInterventionToDelete(intervention);
                                                        //     setShowDeleteInterventionModale(true);
                                                        // }}
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
                </Table>
                <Pagination items={items} />
            </div>
        </AppLayout>
    );
}
