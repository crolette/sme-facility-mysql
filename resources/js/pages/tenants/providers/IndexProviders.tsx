import { Pagination } from '@/components/pagination';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, CentralType, Provider } from '@/types';
import { Head, router } from '@inertiajs/react';
import { Loader, Pencil, PlusCircle, X } from 'lucide-react';
import { useEffect, useState } from 'react';

export interface SearchParams {
    category: number | null;
    q: string | null;
    sortBy: string | null;
    orderBy: string | null;
}

export default function IndexProviders({ items, categories, filters }: { items: Provider[]; categories: CentralType[]; filters: SearchParams }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index providers`,
            href: `/providers`,
        },
    ];
    const [providers, setProviders] = useState<Provider[]>(items.data);
    const [isLoading, setIsLoading] = useState(false);

    const [query, setQuery] = useState<SearchParams>({
        category: filters.category,
        q: filters.q,
        sortBy: filters.sortBy,
        orderBy: filters.orderBy,
    });

    const setCategorySearch = (id: number) => {
        router.visit(route('tenant.providers.index', { ...query, category: id ? id : null }), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    const clearSearch = () => {
        router.visit(route('tenant.providers.index'), {
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
            router.visit(route('tenant.providers.index', { ...query, q: debouncedSearch }), {
                onStart: () => {
                    setIsLoading(true);
                },
                onFinish: () => {
                    setIsLoading(false);
                },
            });
        }
    }, [debouncedSearch]);

    const [prevQuery, setPrevQuery] = useState(query);

    useEffect(() => {
        if (query !== prevQuery)
            router.visit(route('tenant.providers.index', { ...query }), {
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
                <div className="flex w-full justify-between gap-2">
                    <details className="border-border relative w-full cursor-pointer rounded-md border-2 p-1" open={isLoading ? false : undefined}>
                        <summary>Search</summary>
                        <div className="bg-border border-border text-background dark:text-foreground absolute top-full flex flex-col items-center gap-4 rounded-b-md border-2 p-2 sm:flex-row">
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="category">Category</Label>
                                <select
                                    name="category"
                                    id="category"
                                    value={query.category ?? 0}
                                    onChange={(e) => setCategorySearch(parseInt(e.target.value))}
                                >
                                    <option value={0} aria-readonly>
                                        Select a category
                                    </option>
                                    {categories.map((category) => (
                                        <option key={category.label} value={category.id}>
                                            {category.label}
                                        </option>
                                    ))}
                                </select>
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
                            <Button onClick={clearSearch} size={'xs'}>
                                Clear Search
                            </Button>
                        </div>
                    </details>
                    <a href={route(`tenant.providers.create`)}>
                        <Button>
                            <PlusCircle />
                            Create provider
                        </Button>
                    </a>
                </div>

                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>Company name</TableHeadData>
                            <TableHeadData>Category</TableHeadData>
                            <TableHeadData>Phone number</TableHeadData>
                            <TableHeadData>Email</TableHeadData>
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
                        ) : providers.length > 0 ? (
                            providers.map((item, index) => {
                                return (
                                    <TableBodyRow key={index}>
                                        <TableBodyData>
                                            <a href={route('tenant.providers.show', item.id)}>{item.name}</a>
                                        </TableBodyData>
                                        <TableBodyData>{item.category ?? ''}</TableBodyData>
                                        <TableBodyData>{item.phone_number}</TableBodyData>
                                        <TableBodyData>{item.email}</TableBodyData>

                                        <TableBodyData>
                                            {/* <Button onClick={() => deleteLocation(item.reference_code)} variant={'destructive'}>
                                                                Delete
                                                            </Button> */}
                                            <a href={route(`tenant.providers.edit`, item.id)}>
                                                <Button>
                                                    <Pencil />
                                                </Button>
                                            </a>
                                            {/* <a href={route(`tenant.providers.show`, item.id)}>
                                                                <Button variant={'outline'}>See</Button>
                                                            </a> */}
                                        </TableBodyData>
                                    </TableBodyRow>
                                );
                            })
                        ) : (
                            <TableBodyRow>
                                <TableBodyData>No results..</TableBodyData>
                            </TableBodyRow>
                        )}
                    </TableBody>
                </Table>
                <Pagination items={items} />
            </div>
        </AppLayout>
    );
}
