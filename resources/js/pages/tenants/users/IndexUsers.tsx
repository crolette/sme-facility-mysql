import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pill } from '@/components/ui/pill';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, PaginatedData } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Loader, PlusCircle, X } from 'lucide-react';
import { useEffect, useState } from 'react';

export interface SearchParams {
    q: string | null;
    sortBy: string | null;
    orderBy: string | null;
    canLogin: string | null;
    role: string | null;
    provider: string | null;
}

export default function IndexUsers({ items, filters }: { items: PaginatedData; filters: SearchParams }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index users`,
            href: `/users`,
        },
    ];
    const [isLoading, setIsLoading] = useState<boolean>(false);

    const [query, setQuery] = useState<SearchParams>({
        q: filters.q ?? null,
        sortBy: filters.sortBy ?? null,
        orderBy: filters.orderBy ?? null,
        canLogin: filters.canLogin ?? null,
        role: filters.role ?? null,
        provider: filters.provider ?? null,
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
            router.visit(route('tenant.users.index', { ...query, q: debouncedSearch }), {
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
        router.visit(route('tenant.users.index'), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    const setRoleSearch = (role: string) => {
        router.visit(route('tenant.users.index', { ...query, role: role ?? '' }), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    const setCanLoginSearch = (canLogin: string | null) => {
        if (canLogin === 'all') {
            canLogin = null;
        }

        router.visit(route('tenant.users.index', { ...query, canLogin: canLogin }), {
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
                        <summary>Search/Filter</summary>

                        <div className="bg-border border-border text-background dark:text-foreground absolute top-full z-10 flex flex-col items-center gap-4 rounded-b-md border-2 p-2 sm:flex-row">
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="role">role</Label>
                                <select name="role" id="role" value={query.role ?? ''} onChange={(e) => setRoleSearch(e.target.value)}>
                                    <option value={''} aria-readonly>
                                        Select a role
                                    </option>
                                    <option value={'admin'}>Admin</option>
                                    <option value={'manager'}>Maintenance Manager</option>
                                </select>
                            </div>
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="canLogin">canLogin</Label>
                                <div className="space-x-1 text-center">
                                    <Pill variant={query.canLogin === 'yes' ? 'active' : ''} onClick={() => setCanLoginSearch('yes')}>
                                        Yes
                                    </Pill>
                                    <Pill variant={query.canLogin === 'no' ? 'active' : ''} onClick={() => setCanLoginSearch('no')}>
                                        No
                                    </Pill>
                                    <Pill variant={query.canLogin === null ? 'active' : ''} onClick={() => setCanLoginSearch('all')}>
                                        All
                                    </Pill>
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
                    <a href={route(`tenant.users.create`)}>
                        <Button>
                            <PlusCircle />
                            Create user
                        </Button>
                    </a>
                </div>
                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>Name</TableHeadData>
                            <TableHeadData>Job position</TableHeadData>
                            <TableHeadData>Email</TableHeadData>
                            <TableHeadData>Can login</TableHeadData>
                            <TableHeadData>Role</TableHeadData>
                            <TableHeadData>Provider</TableHeadData>
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
                                            <a href={route('tenant.users.show', item.id)}>{item.full_name}</a>
                                        </TableBodyData>
                                        <TableBodyData>{item.job_position}</TableBodyData>
                                        <TableBodyData>
                                            <a href={`mailto:${item.email}`}>{item.email}</a>
                                        </TableBodyData>
                                        <TableBodyData>{item.can_login ? 'YES' : 'NO'}</TableBodyData>
                                        <TableBodyData>{item.roles && item.roles.length > 0 ? item.roles[0].name : ''}</TableBodyData>
                                        <TableBodyData>
                                            {item.provider ? (
                                                <a href={route('tenant.providers.show', item.provider?.id)}>{item.provider?.name}</a>
                                            ) : (
                                                <p>Internal</p>
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
