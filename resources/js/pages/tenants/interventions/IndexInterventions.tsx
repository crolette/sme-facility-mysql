import Modale from '@/components/Modale';
import { Pagination } from '@/components/pagination';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pill } from '@/components/ui/pill';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, CentralType, Intervention, InterventionStatus, PaginatedData, PriorityLevel } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { Loader, Pencil, Trash2, X } from 'lucide-react';
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
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index interventions`,
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
            planned_at: intervention?.planned_at ? formatDateForInput(intervention?.planned_at) : null,
            description: intervention?.description ?? null,
            repair_delay: intervention?.repair_delay ? formatDateForInput(intervention?.repair_delay) : null,
            total_costs: intervention?.total_costs ?? null,
            ticket_id: intervention?.ticket_id ?? null,
            locationType: intervention?.ticket_id ? null : intervention?.interventionable_type,
            locationId: intervention?.ticket_id ? null : (intervention?.interventionable_id ?? null),
        }));
        setAddIntervention(true);
    };

    function formatDateForInput(dateStr: string) {
        const [day, month, year] = dateStr.split('-');
        return `${year}-${month}-${day}`;
    }

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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sites" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex w-full justify-between">
                    <details className="border-border relative w-full cursor-pointer rounded-md border-2 p-2" open={isLoading ? false : undefined}>
                        <summary>Search</summary>

                        <div className="bg-border border-border text-background dark:text-foreground absolute top-full flex flex-col items-center gap-4 rounded-b-md border-2 p-2 sm:flex-row">
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="status">Type</Label>
                                <select
                                    name="type"
                                    id="type"
                                    value={query.type ?? ''}
                                    onChange={(e) => setQuery((prev) => ({ ...prev, type: e.target.value }))}
                                >
                                    <option value={''} aria-readonly>
                                        Select a type
                                    </option>
                                    {types.map((type) => (
                                        <option key={type.id} value={type.id}>
                                            {type.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="status">Status</Label>
                                <select name="status" id="status" value={query.status ?? ''} onChange={(e) => setStatusSearch(e.target.value)}>
                                    <option value={''} aria-readonly>
                                        Select a status
                                    </option>
                                    {statuses.map((status) => (
                                        <option key={status} value={status}>
                                            {status}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="canLogin">Priority</Label>
                                <div className="space-x-1">
                                    {priorities.map((priority) => (
                                        <Pill
                                            key={priority}
                                            size={'sm'}
                                            className="cursor-pointer"
                                            variant={query.priority === priority ? 'active' : 'default'}
                                            onClick={() => setPrioritySearch(priority)}
                                        >
                                            {priority}
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
                                Clear Search
                            </Button>
                        </div>
                    </details>
                </div>
                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData className="w-52">Description</TableHeadData>
                            <TableHeadData>Type</TableHeadData>
                            <TableHeadData>Asset</TableHeadData>
                            <TableHeadData>Priority</TableHeadData>
                            <TableHeadData>Status</TableHeadData>
                            <TableHeadData>Planned at</TableHeadData>
                            <TableHeadData>Repair delay</TableHeadData>
                            <TableHeadData>Total costs</TableHeadData>
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
                            items.data.map((item, index) => {
                                return (
                                    <TableBodyRow key={index}>
                                        <TableBodyData className="">
                                            <a href={route('tenant.interventions.show', item.id)}>
                                                <p className="">{item.description}</p>
                                            </a>
                                        </TableBodyData>
                                        <TableBodyData>{item.type}</TableBodyData>
                                        <TableBodyData>
                                            <a href={item.interventionable?.location_route ?? ''}>
                                                {item.interventionable?.reference_code ?? 'NULL'}
                                            </a>
                                        </TableBodyData>
                                        <TableBodyData>
                                            <Pill variant={item.priority}>{item.priority}</Pill>
                                        </TableBodyData>
                                        <TableBodyData>
                                            <Pill variant={item.status}>{item.status}</Pill>
                                        </TableBodyData>
                                        <TableBodyData>{item.planned_at ?? 'Not planned'}</TableBodyData>
                                        <TableBodyData>{item.repair_delay ?? 'No repair delay'}</TableBodyData>
                                        <TableBodyData>{item.total_costs ? `${item.total_costs} €` : '-'}</TableBodyData>
                                        <TableBodyData className="flex space-x-2">
                                            {!closed && (
                                                <>
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
            <Modale
                title={'Delete intervention'}
                message={
                    'Are you sure to delete this intervention ? You will not be able to restore it afterwards ! All pictures, documents, ... will be deleted too.'
                }
                isOpen={showDeleteInterventionModale}
                onConfirm={deleteIntervention}
                onCancel={() => {
                    setInterventionToDelete(null);
                    setShowDeleteInterventionModale(false);
                }}
            />

            {addIntervention && (
                <div className="bg-background/50 fixed inset-0 z-50">
                    <div className="bg-background/20 flex h-dvh items-center justify-center">
                        <div className="bg-background flex items-center justify-center p-10">
                            {isProcessing && (
                                <div className="flex flex-col items-center gap-4">
                                    <Loader size={48} className="animate-pulse" />
                                    <p className="mx-auto animate-pulse text-3xl font-bold">Processing...</p>
                                    <p className="mx-auto">Intervention is being added...</p>
                                </div>
                            )}
                            {!isProcessing && (
                                <form onSubmit={submitEditIntervention} className="flex w-full flex-col space-y-4">
                                    <Label>Intervention Type</Label>
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
                                        <option value="">Select intervention type</option>
                                        {types?.map((interventionType) => (
                                            <option key={interventionType.id} value={interventionType.id}>
                                                {interventionType.label}
                                            </option>
                                        ))}
                                    </select>
                                    <Label>Status</Label>
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
                                        <option value="">Select status</option>
                                        <option value="draft">draft</option>
                                        <option value="planned">planned</option>
                                        <option value="in progress">in progress</option>
                                        <option value="waiting for parts">waiting for parts</option>
                                        <option value="completed">completed</option>
                                        <option value="cancelled">cancelled</option>
                                    </select>
                                    <Label>Priority</Label>
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
                                        <option value="">Select priority</option>
                                        <option value="low">Low</option>
                                        <option value="medium">Medium</option>
                                        <option value="high">High</option>
                                        <option value="urgent">Urgent</option>
                                    </select>
                                    <Label>Description</Label>
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
                                    {/* {!closed && (
                                        <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                            <h5>Pictures</h5>
                                            <Input
                                                type="file"
                                                multiple
                                                onChange={(e) =>
                                                    setInterventionDataForm((prev) => ({
                                                        ...prev,
                                                        pictures: e.target.files,
                                                    }))
                                                }
                                                accept="image/png, image/jpeg, image/jpg"
                                            />
                                        </div>
                                    )} */}
                                    <Label>Planned at</Label>
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
                                            Clear planned at
                                        </Button>
                                    </div>
                                    <Label>Repair delay</Label>
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
                                            Clear Repair delay
                                        </Button>
                                    </div>
                                    <Button type="submit">Submit</Button>
                                    <Button onClick={closeModale} type="button" variant={'secondary'}>
                                        Cancel
                                    </Button>
                                </form>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
