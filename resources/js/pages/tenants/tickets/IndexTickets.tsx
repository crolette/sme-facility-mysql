import { Pagination } from '@/components/pagination';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pill } from '@/components/ui/pill';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { BreadcrumbItem, PaginatedData, TicketStatus } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { ArrowDownNarrowWide, ArrowDownWideNarrow, Loader, X } from 'lucide-react';
import { useEffect, useState } from 'react';

export interface SearchParams {
    status: string | null;
    q: string | null;
    sortBy: string | null;
    orderBy: string | null;
}

export default function IndexTickets({ items, filters, statuses }: { items: PaginatedData; filters: SearchParams; statuses: TicketStatus }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index tickets`,
            href: `/tickets`,
        },
    ];

    const [isLoading, setIsLoading] = useState<boolean>(false);
    const { showToast } = useToast();

    const [query, setQuery] = useState<SearchParams>({
        status: filters.status ?? null,
        q: filters.q,
        sortBy: filters.sortBy,
        orderBy: filters.orderBy,
    });

    const [prevQuery, setPrevQuery] = useState(query);

    const changeStatusTicket = async (id: number, status: string) => {
        try {
            const response = await axios.patch(route('api.tickets.status', id), { status: status });
            if (response.data.status === 'success') {
                router.visit(route('tenant.tickets.index', { ...query, q: debouncedSearch }), {
                    onStart: () => {
                        setIsLoading(true);
                    },
                    onFinish: () => {
                        setIsLoading(false);
                    },
                });
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
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
            router.visit(route('tenant.tickets.index', { ...query, q: debouncedSearch }), {
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
        router.visit(route('tenant.tickets.index'), {
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
            router.visit(route('tenant.tickets.index', { ...query }), {
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
            <Head title="Tickets" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="border-accent flex gap-10 border-b-2">
                    <ul className="flex pl-4">
                        <li
                            className={cn(
                                'cursor-pointer rounded-t-lg border-x-2 border-t-2 px-6 py-1',
                                query.status === null ? 'bg-primary text-background' : 'bg-secondary',
                            )}
                            onClick={() => {
                                setQuery((prev) => ({ ...prev, q: null, status: null }));
                            }}
                        >
                            all
                        </li>

                        <li
                            className={cn(
                                'cursor-pointer rounded-t-lg border-x-2 border-t-2 px-6 py-1',
                                query.status === 'open' ? 'bg-primary text-background' : 'bg-secondary',
                            )}
                            onClick={() => {
                                setQuery((prev) => ({ ...prev, q: null, status: 'open' }));
                            }}
                        >
                            open
                        </li>
                        <li
                            className={cn(
                                'cursor-pointer rounded-t-lg border-x-2 border-t-2 px-6 py-1',
                                query.status === 'ongoing' ? 'bg-primary text-background' : 'bg-secondary',
                            )}
                            onClick={() => {
                                setQuery((prev) => ({ ...prev, q: null, status: 'ongoing' }));
                            }}
                        >
                            ongoing
                        </li>
                        <li
                            className={cn(
                                'cursor-pointer rounded-t-lg border-x-2 border-t-2 px-6 py-1',
                                query.status === 'closed' ? 'bg-primary text-background' : 'bg-secondary',
                            )}
                            onClick={() => {
                                setQuery((prev) => ({ ...prev, q: null, status: 'closed' }));
                            }}
                        >
                            closed
                        </li>
                    </ul>
                </div>

                <div className="flex w-full justify-between gap-2">
                    <details className="border-border relative w-full rounded-md border-2 p-1" open={isLoading ? false : undefined}>
                        <summary>Search</summary>

                        <div className="bg-border border-border text-background dark:text-foreground absolute top-full flex flex-col items-center gap-4 rounded-b-md border-2 p-2 sm:flex-row">
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="category">Search</Label>
                                <div className="relative text-black dark:text-white">
                                    <Input type="text" value={search ?? ''} onChange={(e) => setSearch(e.target.value)} className="" />
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
                </div>

                <div className="">
                    <h3 className="inline">Tickets {!isLoading && `(${items.total ?? 0})`}</h3>
                    <Table>
                        <TableHead>
                            <TableHeadRow>
                                <TableHeadData>Code</TableHeadData>
                                <TableHeadData>Related to</TableHeadData>
                                <TableHeadData>Status</TableHeadData>
                                <TableHeadData>Reporter</TableHeadData>
                                <TableHeadData>Description</TableHeadData>
                                <TableHeadData>
                                    <div className="flex items-center gap-2">
                                        <ArrowDownNarrowWide
                                            size={16}
                                            className="cursor-pointer"
                                            onClick={() => setQuery((prev) => ({ ...prev, orderBy: 'created_at', sortBy: 'asc' }))}
                                        />
                                        Created at
                                        <ArrowDownWideNarrow
                                            size={16}
                                            className="cursor-pointer"
                                            onClick={() => setQuery((prev) => ({ ...prev, orderBy: 'created_at', sortBy: 'desc' }))}
                                        />
                                    </div>
                                </TableHeadData>
                                <TableHeadData>
                                    <div className="flex items-center gap-2">
                                        <ArrowDownNarrowWide
                                            size={16}
                                            className="cursor-pointer"
                                            onClick={() => setQuery((prev) => ({ ...prev, orderBy: 'updated_at', sortBy: 'asc' }))}
                                        />
                                        Updated at
                                        <ArrowDownWideNarrow
                                            size={16}
                                            className="cursor-pointer"
                                            onClick={() => setQuery((prev) => ({ ...prev, orderBy: 'updated_at', sortBy: 'desc' }))}
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
                                            Loading...
                                        </p>
                                    </TableBodyData>
                                </TableBodyRow>
                            ) : (
                                items?.data?.map((ticket, index) => (
                                    <TableBodyRow key={index}>
                                        <TableBodyData>
                                            <a href={route('tenant.tickets.show', ticket.id)}>{ticket.code}</a>
                                        </TableBodyData>
                                        <TableBodyData>
                                            <a href={ticket.ticketable.location_route}>{ticket.asset_code}</a>
                                        </TableBodyData>
                                        <TableBodyData>
                                            <Pill variant={ticket.status}>{ticket.status}</Pill>
                                        </TableBodyData>
                                        <TableBodyData>{ticket.reporter?.full_name ?? ticket.reporter_email}</TableBodyData>
                                        <TableBodyData>{ticket.description}</TableBodyData>
                                        <TableBodyData>{ticket.created_at}</TableBodyData>
                                        <TableBodyData>{ticket.updated_at}</TableBodyData>

                                        <TableBodyData className="space-x-2">
                                            {ticket.status == 'open' && (
                                                <Button variant={'green'} onClick={() => changeStatusTicket(ticket.id, 'ongoing')}>
                                                    Ongoing
                                                </Button>
                                            )}
                                            {ticket.status !== 'closed' && (
                                                <>
                                                    <Button variant={'destructive'} onClick={() => changeStatusTicket(ticket.id, 'closed')}>
                                                        Close
                                                    </Button>
                                                    {/* <a href={route('tenant.tickets.show', ticket.id)}>
                                                    <Button type="button">Show</Button>
                                                </a> */}
                                                </>
                                            )}
                                            {ticket.status === 'closed' && (
                                                <Button variant={'green'} onClick={() => changeStatusTicket(ticket.id, 'open')}>
                                                    Re-open
                                                </Button>
                                            )}
                                        </TableBodyData>
                                    </TableBodyRow>
                                ))
                            )}
                        </TableBody>
                    </Table>
                    <Pagination items={items} />
                </div>
            </div>
        </AppLayout>
    );
}
