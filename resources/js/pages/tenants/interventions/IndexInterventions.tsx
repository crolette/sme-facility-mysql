import Modale from '@/components/Modale';
import ModaleForm from '@/components/ModaleForm';
import { Pagination } from '@/components/pagination';
import { useGridTableLayoutContext } from '@/components/tenant/gridTableLayoutContext';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import DisplayGridTableIndex from '@/components/ui/displayGridTableIndex';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pill } from '@/components/ui/pill';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import { usePermissions } from '@/hooks/usePermissions';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { BreadcrumbItem, CentralType, Intervention, InterventionStatus, PaginatedData, PriorityLevel } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { ArrowDownNarrowWide, ArrowDownWideNarrow, Loader, Pencil, Trash2, X } from 'lucide-react';

import { FormEventHandler, useEffect, useState } from 'react';

export interface SearchParams {
    q: string | null;
    sortBy: string | null;
    orderBy: string | null;
    status: string | null;
    type: string | null;
    priority: string | null;
}

type InterventionFormData = {
    intervention_id: null | number;
    intervention_type_id: null | number;
    status: null | string;
    priority: null | string;
    planned_at: null | string;
    description: null | string;
    repair_delay: null | string;
    total_costs: null | number;
    locationType: null | string;
    locationId: null | number | string;
    ticket_id: null | number;
    pictures: FileList | null;
};

export default function IndexInterventions({
    items,
    filters,
    statuses,
    types,
    priorities,
}: {
    items: PaginatedData;
    filters: SearchParams;
    statuses: InterventionStatus;
    priorities: PriorityLevel;
    types: CentralType[];
}) {
    const { t, tChoice } = useLaravelReactI18n();
    const { hasPermission } = usePermissions();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${tChoice('interventions.title', 2)}`,
            href: `/interventions`,
        },
    ];
    const [isLoading, setIsLoading] = useState<boolean>(false);

    const interventionData = {
        intervention_id: null,
        intervention_type_id: null,
        status: null,
        priority: null,
        planned_at: null,
        description: null,
        repair_delay: null,
        total_costs: null,
        locationType: null,
        locationId: null,
        ticket_id: null,
        pictures: [],
    };

    const [isProcessing, setIsProcessing] = useState<boolean>(false);
    const [interventionDataForm, setInterventionDataForm] = useState<InterventionFormData>(interventionData);
    const [addIntervention, setAddIntervention] = useState<boolean>(false);
    const [interventionTypes, setInterventionTypes] = useState<CentralType[]>([]);
    const closeModale = () => {
        setInterventionDataForm(interventionData);
        setAddIntervention(false);
        setIsProcessing(false);
    };

    const editIntervention = (id: number) => {
        const intervention = items.data.find((intervention) => {
            return intervention.id === id;
        });

        setInterventionDataForm((prev) => ({
            ...prev,
            intervention_id: id,
            intervention_type_id: intervention?.intervention_type_id ?? null,
            status: intervention?.status ?? null,
            priority: intervention?.priority ?? null,
            planned_at: intervention?.planned_at ?? null,
            description: intervention?.description ?? null,
            repair_delay: intervention?.repair_delay ?? null,
            total_costs: intervention?.total_costs ?? null,
            ticket_id: intervention?.ticket_id ?? null,
            locationType: intervention?.ticket_id ? null : intervention?.interventionable_type,
            locationId: intervention?.ticket_id ? null : (intervention?.interventionable_id ?? null),
        }));
        setAddIntervention(true);
    };

    const submitEditIntervention: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);

        try {
            const response = await axios.patch(route('api.interventions.update', interventionDataForm.intervention_id), interventionDataForm);
            if (response.data.status === 'success') {
                setAddIntervention(false);
                setIsProcessing(false);
                showToast(response.data.message, response.data.status);
                router.visit(route('tenant.interventions.index'));
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
            setIsProcessing(false);
        }
    };

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

    const setStatusSearch = (status: string | null) => {
        if (status === query.status) status = null;

        router.visit(route('tenant.interventions.index', { ...query, status: status ?? '' }), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    const setPrioritySearch = (priority: string | null) => {
        if (priority === query.priority) priority = null;

        router.visit(route('tenant.interventions.index', { ...query, priority: priority }), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    const { showToast } = useToast();

    const [prevQuery, setPrevQuery] = useState(query);

    useEffect(() => {
        if (query !== prevQuery)
            router.visit(route('tenant.interventions.index', { ...query }), {
                onStart: () => {
                    setIsLoading(true);
                },
                onFinish: () => {
                    setIsLoading(false);
                },
            });
    }, [query]);

    const [showDeleteInterventionModale, setShowDeleteInterventionModale] = useState(false);
    const [interventionToDelete, setInterventionToDelete] = useState<null | Intervention>(null);
    const deleteIntervention = async () => {
        if (!interventionToDelete) return;

        try {
            setIsLoading(true);
            const response = await axios.delete(route('api.interventions.destroy', interventionToDelete.id), {});

            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
                setShowDeleteInterventionModale(false);
                setInterventionToDelete(null);

                router.visit(route('tenant.interventions.index', { ...query }));
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
            setIsLoading(false);
        }
    };

    const { layout } = useGridTableLayoutContext();

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={tChoice('interventions.title', 2)} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="border-accent flex flex-col gap-2 border-b-2 pb-2 sm:flex-row sm:gap-10">
                    <div className="flex w-full items-center justify-between gap-4">
                        <details
                            className="border-border relative w-full cursor-pointer rounded-md border-2 p-2"
                            open={isLoading ? false : undefined}
                        >
                            <summary>{t('common.search_filter')}</summary>

                            <div className="bg-border border-border text-background dark:text-foreground absolute top-full z-10 flex flex-col items-center gap-4 rounded-b-md border-2 p-2 sm:flex-row">
                                <div className="flex flex-col items-center gap-2">
                                    <Label htmlFor="status">{t('common.type')}</Label>
                                    <select
                                        name="type"
                                        id="type"
                                        value={query.type ?? ''}
                                        onChange={(e) => setQuery((prev) => ({ ...prev, type: e.target.value }))}
                                    >
                                        <option value={''} aria-readonly>
                                            {t('actions.select-type', { type: t('common.type') })}
                                        </option>
                                        {types.map((type) => (
                                            <option key={type.id} value={type.id}>
                                                {type.label}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="flex flex-col items-center gap-2">
                                    <Label htmlFor="status">{t('interventions.status')}</Label>
                                    <select name="status" id="status" value={query.status ?? ''} onChange={(e) => setStatusSearch(e.target.value)}>
                                        <option value={''} aria-readonly>
                                            {t('actions.select-type', { type: t('interventions.status') })}
                                        </option>
                                        {statuses.map((status) => (
                                            <option key={status} value={status}>
                                                {t(`interventions.status.${status}`)}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="flex flex-col items-center gap-2">
                                    <Label htmlFor="canLogin">{t('interventions.priority')}</Label>
                                    <div className="space-x-1 text-center">
                                        {priorities.map((priority) => (
                                            <Pill
                                                key={priority}
                                                // size={'sm'}
                                                className="cursor-pointer"
                                                variant={query.priority === priority ? 'active' : 'default'}
                                                onClick={() => setPrioritySearch(priority)}
                                            >
                                                {t(`interventions.priority.${priority}`)}
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

                                <Button onClick={clearSearch} size={'sm'}>
                                    {t('actions.search-clear')}
                                </Button>
                            </div>
                        </details>
                    </div>
                </div>
                <div className="flex w-full flex-col sm:flex-row sm:items-center sm:justify-between">
                    <h1>{tChoice(`interventions.title`, 2)}</h1>
                    <DisplayGridTableIndex />
                </div>
                {layout === 'grid' ? (
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-3 xl:grid-cols-5">
                        {items.data.map((item, index) => (
                            <div
                                key={index}
                                className="border-accent bg-sidebar relative flex flex-col gap-2 overflow-hidden rounded-md border-2 p-4"
                            >
                                <div>
                                    <a href={route('tenant.interventions.show', item.id)} className="flex w-full">
                                        <p className="overflow-hidden overflow-ellipsis whitespace-nowrap">{item.description}</p>
                                    </a>
                                    <p className="tooltip tooltip-bottom">{item.description}</p>
                                </div>
                                <p className="text-xs">{item.type ?? ''}</p>
                                <a className="text-xs" href={item.interventionable?.location_route ?? ''}>
                                    {item.interventionable?.reference_code ?? 'NULL'}
                                </a>
                                <div className="flex gap-2">
                                    <Pill variant={item.priority}>{t(`interventions.priority.${item.priority}`)}</Pill>
                                    <Pill variant={item.status}>{t(`interventions.status.${item.status}`)}</Pill>
                                </div>
                                <p className="text-xs">
                                    {' '}
                                    {item.assignable ? (
                                        item.assignable.full_name ? (
                                            <a href={route('tenant.users.show', item.assignable.id)}>{item.assignable.full_name}</a>
                                        ) : (
                                            <a href={route('tenant.providers.show', item.assignable.id)}>{item.assignable.name}</a>
                                        )
                                    ) : (
                                        t('interventions.assigned_not')
                                    )}
                                </p>
                                <p className="text-xs">
                                    {t('interventions.planned_at')} : {item.planned_at ?? t('interventions.planned_at_no')}
                                </p>
                                <p className="text-xs">
                                    {t('interventions.repair_delay')} : {item.repair_delay ?? t('interventions.repair_delay_no')}
                                </p>
                                {!closed && (
                                    <div className="flex gap-2">
                                        <Button onClick={() => editIntervention(item.id)}>
                                            <Pencil />
                                        </Button>
                                        <Button
                                            type="button"
                                            variant="destructive"
                                            onClick={() => {
                                                setInterventionToDelete(item);
                                                setShowDeleteInterventionModale(true);
                                            }}
                                        >
                                            <Trash2 />
                                        </Button>
                                    </div>
                                )}
                            </div>
                        ))}
                    </div>
                ) : (
                    <Table>
                        <TableHead>
                            <TableHeadRow>
                                <TableHeadData className="w-52">{t('common.description')}</TableHeadData>
                                <TableHeadData>{t('common.type')}</TableHeadData>
                                <TableHeadData>{t('tickets.related_to')}</TableHeadData>
                                <TableHeadData>
                                    <div className="flex flex-nowrap items-center gap-2">
                                        <ArrowDownNarrowWide
                                            size={16}
                                            className={cn(
                                                'cursor-pointer',
                                                query.sortBy === 'priority' && query.orderBy === 'asc' ? 'text-amber-300' : '',
                                            )}
                                            onClick={() => setQuery((prev) => ({ ...prev, sortBy: 'priority', orderBy: 'asc' }))}
                                        />
                                        <p>{t('interventions.priority')}</p>
                                        <ArrowDownWideNarrow
                                            size={16}
                                            className={cn(
                                                'cursor-pointer',
                                                query.sortBy === 'priority' && query.orderBy === 'desc' ? 'text-amber-300' : '',
                                            )}
                                            onClick={() => setQuery((prev) => ({ ...prev, sortBy: 'priority', orderBy: 'desc' }))}
                                        />
                                    </div>
                                </TableHeadData>
                                <TableHeadData>{t('interventions.status')}</TableHeadData>
                                <TableHeadData>{t('interventions.assigned_to')}</TableHeadData>
                                <TableHeadData>
                                    <div className="flex items-center gap-2">
                                        <ArrowDownNarrowWide
                                            size={16}
                                            className={cn(
                                                'cursor-pointer',
                                                query.sortBy === 'planned_at' && query.orderBy === 'asc' ? 'text-amber-300' : '',
                                                !query.sortBy && !query.orderBy ? 'text-amber-300' : '',
                                            )}
                                            onClick={() => setQuery((prev) => ({ ...prev, sortBy: 'planned_at', orderBy: 'asc' }))}
                                        />
                                        <p>{t('interventions.planned_at')}</p>
                                        <ArrowDownWideNarrow
                                            size={16}
                                            className={cn(
                                                'cursor-pointer',
                                                query.sortBy === 'planned_at' && query.orderBy === 'desc' ? 'text-amber-300' : '',
                                            )}
                                            onClick={() => setQuery((prev) => ({ ...prev, sortBy: 'planned_at', orderBy: 'desc' }))}
                                        />
                                    </div>
                                </TableHeadData>
                                <TableHeadData>
                                    <div className="flex items-center gap-2">
                                        <ArrowDownNarrowWide
                                            size={16}
                                            className={cn(
                                                'cursor-pointer',
                                                query.sortBy === 'repair_delay' && query.orderBy === 'asc' ? 'text-amber-300' : '',
                                            )}
                                            onClick={() => setQuery((prev) => ({ ...prev, sortBy: 'repair_delay', orderBy: 'asc' }))}
                                        />
                                        <p>{t('interventions.repair_delay')}</p>
                                        <ArrowDownWideNarrow
                                            size={16}
                                            className={cn(
                                                'cursor-pointer',
                                                query.sortBy === 'repair_delay' && query.orderBy === 'desc' ? 'text-amber-300' : '',
                                            )}
                                            onClick={() => setQuery((prev) => ({ ...prev, sortBy: 'repair_delay', orderBy: 'desc' }))}
                                        />
                                    </div>
                                </TableHeadData>
                                <TableHeadData>{t('interventions.total_costs')}</TableHeadData>
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
                                items.data.map((item, index) => {
                                    return (
                                        <TableBodyRow key={index}>
                                            <TableBodyData className="">
                                                <a href={route('tenant.interventions.show', item.id)} className="flex w-40">
                                                    <p className="overflow-hidden overflow-ellipsis whitespace-nowrap">{item.description}</p>
                                                </a>
                                                <p className="tooltip tooltip-bottom">{item.description}</p>
                                            </TableBodyData>
                                            <TableBodyData>{item.type}</TableBodyData>
                                            <TableBodyData>
                                                <a href={item.interventionable?.location_route ?? ''}>
                                                    {item.interventionable?.reference_code ?? item.interventionable?.name ?? 'NA'}
                                                </a>
                                            </TableBodyData>
                                            <TableBodyData>
                                                <Pill variant={item.priority}>{t(`interventions.priority.${item.priority}`)}</Pill>
                                            </TableBodyData>
                                            <TableBodyData>
                                                <Pill variant={item.status}>{t(`interventions.status.${item.status}`)}</Pill>
                                            </TableBodyData>
                                            <TableBodyData>
                                                {item.assignable ? (
                                                    item.assignable.full_name ? (
                                                        <a href={route('tenant.users.show', item.assignable.id)}>{item.assignable.full_name}</a>
                                                    ) : (
                                                        <a href={route('tenant.providers.show', item.assignable.id)}>{item.assignable.name}</a>
                                                    )
                                                ) : (
                                                    t('interventions.assigned_not')
                                                )}
                                            </TableBodyData>
                                            <TableBodyData>{item.planned_at ?? t('interventions.planned_at_no')}</TableBodyData>
                                            <TableBodyData>{item.repair_delay ?? t('interventions.repair_delay_no')}</TableBodyData>
                                            <TableBodyData>{item.total_costs ? `${item.total_costs} €` : '-'}</TableBodyData>
                                            <TableBodyData className="flex space-x-2">
                                                {!closed && (
                                                    <>
                                                        {hasPermission('update interventions') && (
                                                            <Button onClick={() => editIntervention(item.id)}>
                                                                <Pencil />
                                                            </Button>
                                                        )}
                                                        {hasPermission('delete interventions') && (
                                                            <Button
                                                                type="button"
                                                                variant="destructive"
                                                                onClick={() => {
                                                                    setInterventionToDelete(item);
                                                                    setShowDeleteInterventionModale(true);
                                                                }}
                                                            >
                                                                <Trash2 />
                                                            </Button>
                                                        )}
                                                    </>
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
                title={t('actions.delete-type', { type: tChoice('interventions.title', 1) })}
                message={t('interventions.delete_description')}
                isOpen={showDeleteInterventionModale}
                onConfirm={deleteIntervention}
                onCancel={() => {
                    setInterventionToDelete(null);
                    setShowDeleteInterventionModale(false);
                }}
            />

            {addIntervention && (
                <ModaleForm title={'Edit intervention'}>
                    {isProcessing && (
                        <div className="flex flex-col items-center gap-4">
                            <Loader size={48} className="animate-pulse" />
                            <p className="mx-auto animate-pulse text-3xl font-bold">{t('actions.processing')}</p>
                            <p className="mx-auto">{t('actions.type-being-created', { type: tChoice('interventions.title', 1) })}</p>
                        </div>
                    )}
                    {!isProcessing && (
                        <form onSubmit={submitEditIntervention} className="flex w-full flex-col space-y-4">
                            <Label>{t('common.type')}</Label>
                            <select
                                name="intervention_type"
                                id="intervention_type"
                                required
                                value={interventionDataForm.intervention_type_id ?? ''}
                                onChange={(e) =>
                                    setInterventionDataForm((prev) => ({
                                        ...prev,
                                        intervention_type_id: parseInt(e.target.value),
                                    }))
                                }
                            >
                                <option value="">{t('actions.select-type', { type: t('common.type') })}</option>
                                {types?.map((interventionType) => (
                                    <option key={interventionType.id} value={interventionType.id}>
                                        {interventionType.label}
                                    </option>
                                ))}
                            </select>
                            <Label>{t('interventions.status')}</Label>
                            <select
                                name=""
                                id=""
                                required
                                value={interventionDataForm.status ?? ''}
                                onChange={(e) =>
                                    setInterventionDataForm((prev) => ({
                                        ...prev,
                                        status: e.target.value,
                                    }))
                                }
                            >
                                <option value="">{t('actions.select-type', { type: t('common.status') })}</option>

                                <option value="draft">{t('interventions.status.draft')}</option>
                                <option value="planned">{t('interventions.status.planned')}</option>
                                <option value="in_progress">{t('interventions.status.in_progress')}</option>
                                <option value="waiting_parts">{t('interventions.status.waiting_parts')}</option>
                                <option value="completed">{t('interventions.status.completed')}</option>
                                <option value="cancelled">{t('interventions.status.cancelled')}</option>
                            </select>
                            <Label>{t('interventions.priority')}</Label>
                            <select
                                name=""
                                id=""
                                required
                                value={interventionDataForm.priority ?? ''}
                                onChange={(e) =>
                                    setInterventionDataForm((prev) => ({
                                        ...prev,
                                        priority: e.target.value,
                                    }))
                                }
                            >
                                <option value="">{t('actions.select-type', { type: t('interventions.priority') })}</option>
                                <option value="low">{t('interventions.priority.low')}</option>
                                <option value="medium">{t('interventions.priority.medium')}</option>
                                <option value="high">{t('interventions.priority.high')}</option>
                                <option value="urgent">{t('interventions.priority.urgent')}</option>
                            </select>
                            <Label>{t('common.description')}</Label>
                            <Textarea
                                placeholder="description"
                                value={interventionDataForm.description ?? ''}
                                onChange={(e) =>
                                    setInterventionDataForm((prev) => ({
                                        ...prev,
                                        description: e.target.value,
                                    }))
                                }
                            ></Textarea>
                            <Label>{t('interventions.planned_at')}</Label>
                            <div className="flex gap-2">
                                <Input
                                    type="date"
                                    value={interventionDataForm.planned_at ?? ''}
                                    onChange={(e) =>
                                        setInterventionDataForm((prev) => ({
                                            ...prev,
                                            planned_at: e.target.value,
                                        }))
                                    }
                                />
                                <Button
                                    variant={'outline'}
                                    type="button"
                                    onClick={() =>
                                        setInterventionDataForm((prev) => ({
                                            ...prev,
                                            planned_at: null,
                                        }))
                                    }
                                >
                                    {t('actions.clear-type', { type: t('interventions.planned_at') })}
                                </Button>
                            </div>
                            <Label>{t('interventions.repair_delay')}</Label>
                            <div className="flex gap-2">
                                <Input
                                    type="date"
                                    value={interventionDataForm.repair_delay ?? ''}
                                    onChange={(e) =>
                                        setInterventionDataForm((prev) => ({
                                            ...prev,
                                            repair_delay: e.target.value,
                                        }))
                                    }
                                />
                                <Button
                                    variant={'outline'}
                                    type="button"
                                    onClick={() =>
                                        setInterventionDataForm((prev) => ({
                                            ...prev,
                                            repair_delay: null,
                                        }))
                                    }
                                >
                                    {t('actions.clear-type', { type: t('interventions.repair_delay') })}
                                </Button>
                            </div>
                            <Button type="submit">
                                <Label>{t('actions.submit')}</Label>
                            </Button>
                            <Button onClick={closeModale} type="button" variant={'secondary'}>
                                <Label>{t('actions.cancel')}</Label>
                            </Button>
                        </form>
                    )}
                </ModaleForm>
            )}
        </AppLayout>
    );
}
