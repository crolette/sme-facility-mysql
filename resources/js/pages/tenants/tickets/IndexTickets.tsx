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
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ArrowDownNarrowWide, ArrowDownWideNarrow, Loader, X } from 'lucide-react';
import { useEffect, useState } from 'react';

export interface SearchParams {
    status: string | null;
    q: string | null;
    sortBy: string | null;
    orderBy: string | null;
}

export default function IndexTickets({ items, filters, statuses }: { items: PaginatedData; filters: SearchParams; statuses: TicketStatus }) {
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${tChoice('tickets', 2)}`,
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
            <Head title={tChoice('tickets', 2)} />
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
                            {t('common.all')}
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
                            {t('tickets.status.open')}
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
                            {t('tickets.status.ongoing')}
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
                            {t('tickets.status.closed')}
                        </li>
                    </ul>
                </div>

                <div className="flex w-full justify-between gap-2">
                    <details className="border-border relative w-full rounded-md border-2 p-1" open={isLoading ? false : undefined}>
                        <summary>{t('actions.search')}</summary>

                        <div className="bg-border border-border text-background dark:text-foreground absolute top-full flex flex-col items-center gap-4 rounded-b-md border-2 p-2 sm:flex-row">
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="search">{t('actions.search')}</Label>
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
                </div>

                <div className="">
                    <h3 className="inline">
                        {tChoice('tickets', 2)} {!isLoading && `(${items.total ?? 0})`}
                    </h3>
                    <Table>
                        <TableHead>
                            <TableHeadRow>
                                <TableHeadData>{t('common.code')}</TableHeadData>
                                <TableHeadData>{t('tickets.related_to')}</TableHeadData>
                                <TableHeadData>{t('common.status')}</TableHeadData>
                                <TableHeadData>{t('tickets.reporter')}</TableHeadData>
                                <TableHeadData>{t('common.description')}</TableHeadData>
                                <TableHeadData>
                                    <div className="flex items-center gap-2">
                                        <ArrowDownNarrowWide
                                            size={16}
                                            className={cn(
                                                'cursor-pointer',
                                                query.sortBy === 'created_at' && query.orderBy === 'asc' ? 'text-amber-300' : '',
                                                !query.sortBy && !query.orderBy ? 'text-amber-300' : '',
                                            )}
                                            onClick={() => setQuery((prev) => ({ ...prev, sortBy: 'created_at', orderBy: 'asc' }))}
                                        />
                                        {t('common.created_at')}
                                        <ArrowDownWideNarrow
                                            size={16}
                                            className={cn(
                                                'cursor-pointer',
                                                query.sortBy === 'created_at' && query.orderBy === 'desc' ? 'text-amber-300' : '',
                                            )}
                                            onClick={() => setQuery((prev) => ({ ...prev, sortBy: 'created_at', orderBy: 'desc' }))}
                                        />
                                    </div>
                                </TableHeadData>
                                <TableHeadData>
                                    <div className="flex items-center gap-2">
                                        <ArrowDownNarrowWide
                                            size={16}
                                            className={cn(
                                                'cursor-pointer',
                                                query.sortBy === 'updated_at' && query.orderBy === 'asc' ? 'text-amber-300' : '',
                                            )}
                                            onClick={() => setQuery((prev) => ({ ...prev, sortBy: 'updated_at', orderBy: 'asc' }))}
                                        />
                                        {t('common.updated_at')}
                                        <ArrowDownWideNarrow
                                            size={16}
                                            className={cn(
                                                'cursor-pointer',
                                                query.sortBy === 'updated_at' && query.orderBy === 'desc' ? 'text-amber-300' : '',
                                            )}
                                            onClick={() => setQuery((prev) => ({ ...prev, sortBy: 'updated_at', orderBy: 'desc' }))}
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
                                            {t('actions.loading')}
                                        </p>
                                    </TableBodyData>
                                </TableBodyRow>
                            ) : (
                                items?.data?.map((ticket, index) => (
                                    <TableBodyRow key={index}>
                                        <TableBodyData>
                                            <a href={route('tenant.tickets.show', ticket.id)}>{ticket.code}</a>
                                        </TableBodyData>
                                        <TableBodyData className="text-center">
                                            <a href={ticket.ticketable.location_route}>{ticket.asset_code}</a>
                                        </TableBodyData>
                                        <TableBodyData>
                                            <Pill variant={ticket.status}>{t(`tickets.status.${ticket.status}`)}</Pill>
                                        </TableBodyData>
                                        <TableBodyData>{ticket.reporter?.full_name ?? ticket.reporter_email}</TableBodyData>
                                        <TableBodyData className="my-auto flex h-full w-40">
                                            <p className="overflow-hidden overflow-ellipsis whitespace-nowrap">{ticket.description}</p>
                                        </TableBodyData>
                                        <TableBodyData>{ticket.created_at}</TableBodyData>
                                        <TableBodyData>{ticket.updated_at}</TableBodyData>

                                        <TableBodyData className="space-x-2">
                                            {ticket.status == 'open' && (
                                                <Button variant={'green'} onClick={() => changeStatusTicket(ticket.id, 'ongoing')}>
                                                    {t('tickets.status.ongoing')}
                                                </Button>
                                            )}
                                            {ticket.status !== 'closed' && (
                                                <>
                                                    <Button variant={'destructive'} onClick={() => changeStatusTicket(ticket.id, 'closed')}>
                                                        {t('tickets.close')}
                                                    </Button>
                                                    {/* <a href={route('tenant.tickets.show', ticket.id)}>
                                                    <Button type="button">Show</Button>
                                                </a> */}
                                                </>
                                            )}
                                            {ticket.status === 'closed' && (
                                                <Button variant={'green'} onClick={() => changeStatusTicket(ticket.id, 'open')}>
                                                    {t('tickets.reopen')}
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
