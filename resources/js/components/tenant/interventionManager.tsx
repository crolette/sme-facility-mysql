import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { CentralType, Intervention, Provider, User } from '@/types';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Loader, Pencil, PlusCircle, Trash2, X } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import Modale from '../Modale';
import ModaleForm from '../ModaleForm';
import SearchableInput from '../SearchableInput';
import { useToast } from '../ToastrContext';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Label } from '../ui/label';
import { Pill } from '../ui/pill';
import { Textarea } from '../ui/textarea';
import { InterventionActionManager } from './interventionActionManager';

interface InterventionManagerProps {
    itemCodeId: number | string;
    getInterventionsUrl: string;
    uploadRoute?: string;
    editRoute?: string;
    deleteRoute?: string;
    showRoute?: string;
    type: string;
    closed?: boolean;
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

export const InterventionManager = ({ itemCodeId, getInterventionsUrl, type, closed = false }: InterventionManagerProps) => {
    const { t, tChoice } = useLaravelReactI18n();
    const [interventions, setInterventions] = useState<Intervention[]>([]);
    const { showToast } = useToast();

    const [addIntervention, setAddIntervention] = useState<boolean>(false);
    const [submitType, setSubmitType] = useState<'edit' | 'new'>('edit');

    const fetchInterventions = async () => {
        try {
            const response = await axios.get(route(getInterventionsUrl, itemCodeId));
            setInterventions(response.data.data);
        } catch (error) {
            console.error('Erreur lors de la recherche : ', error);
        }
    };

    const [interventionTypes, setInterventionTypes] = useState<CentralType[]>([]);

    const fetchInterventionTypes = async () => {
        try {
            const response = await axios.get(route('api.category-types', { type: 'intervention' }));
            setInterventionTypes(response.data.data);
        } catch (error) {
            console.log(error);
        }
    };

    useEffect(() => {
        fetchInterventions();
    }, []);

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

    const [interventionDataForm, setInterventionDataForm] = useState<InterventionFormData>(interventionData);

    const openModale = () => {
        setSubmitType('new');
        if (type === 'ticket') {
            setInterventionDataForm((prev) => ({
                ...prev,
                ticket_id: parseInt(itemCodeId),
            }));
        } else {
            setInterventionDataForm((prev) => ({
                ...prev,
                locationType: type,
                locationId: itemCodeId,
            }));
        }

        if (interventionTypes.length === 0) fetchInterventionTypes();
        setAddIntervention(true);
    };

    const closeModale = () => {
        setInterventionDataForm(interventionData);
        setAddIntervention(false);
        fetchInterventions();
        setSubmitType('edit');
        setIsProcessing(false);
    };

    const submitIntervention: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);

        try {
            const response = await axios.post(route('api.interventions.store'), interventionDataForm, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            if (response.data.status === 'success') {
                closeModale();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            console.error(error);
            setIsProcessing(false);
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const editIntervention = (id: number) => {
        setSubmitType('edit');
        const intervention = interventions.find((intervention) => {
            return intervention.id === id;
        });
        if (interventionTypes.length === 0) fetchInterventionTypes();

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

    const submitEditIntervention: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);

        try {
            const response = await axios.patch(route('api.interventions.update', interventionDataForm.intervention_id), interventionDataForm);
            if (response.data.status === 'success') {
                fetchInterventions();
                setAddIntervention(false);
                setSubmitType('new');
                setIsProcessing(false);
                setInterventionDataForm(interventionData);
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
            setIsProcessing(false);
        }
    };

    function formatDateForInput(dateStr: string) {
        const [day, month, year] = dateStr.split('-');
        return `${year}-${month}-${day}`;
    }

    const [showDeleteInterventionModale, setShowDeleteInterventionModale] = useState(false);
    const [interventionToDelete, setInterventionToDelete] = useState<null | Intervention>(null);
    const deleteIntervention = async () => {
        if (!interventionToDelete) return;

        try {
            const response = await axios.delete(route('api.interventions.destroy', interventionToDelete.id));
            if (response.data.status === 'success') {
                fetchInterventions();
                showToast(response.data.message, response.data.status);
                setShowDeleteInterventionModale(false);
                setInterventionToDelete(null);
            }
        } catch (error) {
            console.error(error);
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const [actionsChanged, setActionsChanged] = useState<boolean>(false);
    useEffect(() => {
        fetchInterventions();
        setActionsChanged(false);
    }, [actionsChanged === true]);

    const [sendInterventionToProviderModale, setSendInterventionToProviderModale] = useState<boolean>(false);
    const [interventionToSend, setInterventionToSend] = useState<number | null>(null);
    const [isProcessing, setIsProcessing] = useState<boolean>(false);

    const [providers, setProviders] = useState<Provider[] | null>(null);
    const [interventionAssignees, setInterventionAssignees] = useState<(Provider | User)[]>([]);
    const [provider, setProvider] = useState<number | null>(null);
    const [user, setUser] = useState<number | null>(null);

    const sendIntervention = (id: number) => {
        fetchProviders(id);
        setInterventionToSend(id);
        setSendInterventionToProviderModale(true);
    };

    const fetchProviders = async (id: number) => {
        try {
            const response = await axios.get(route('api.interventions.providers', id));
            if (response.data.status === 'success') {
                setProviders(response.data.data);
            }
        } catch (error) {
            console.log(error);
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const [externalProviders, setExternalProviders] = useState<Provider[] | null>(null);
    const [externalProvidersQuery, setExternalProvidersQuery] = useState<string | null>(null);

    const fetchExternalProviders: FormEventHandler = async (e) => {
        e.preventDefault();
        if (externalProvidersQuery)
            try {
                const response = await axios.get(route('api.providers.search', { q: externalProvidersQuery, users: 1 }));
                if (response.data.status === 'success') {
                    setExternalProviders(response.data.data);
                }
            } catch (error) {
                console.log(error);
                showToast(error.response.data.message, error.response.data.status);
            }
    };

    const sendInterventionMail = async () => {
        if (!interventionToSend || !interventionAssignees || (!provider && !user)) return;

        setIsProcessing(true);

        const emails = interventionAssignees.map((assignee) => {
            return assignee.email;
        });

        try {
            const response = await axios.post(route('api.interventions.send-provider', interventionToSend), {
                emails: emails,
                provider_id: provider,
                user_id: user,
            });
            if (response.data.status === 'success') {
                closeSendInterventionToProviderModale();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
            setIsProcessing(false);
        }
    };

    const closeSendInterventionToProviderModale = () => {
        setSendInterventionToProviderModale(false);
        setExternalProvidersQuery(null);
        setExternalProviders(null);
        setInterventionAssignees([]);
        setProviders(null);
        setProvider(null);
        setUser(null);
        setInterventionToSend(null);
        setIsProcessing(false);
        // fetchInterventions();
    };

    const addAssignee = (assignee: User | Provider, provider_id?: number) => {
        if (provider_id) {
            if (provider_id === provider && interventionAssignees[0].name) {
                // cela veut dire que c'est le provider qui est assigné
                setInterventionAssignees([assignee]);
            } else if (provider_id === provider) {
                // cela veut dire que c'est un user du même provider
                if (!interventionAssignees.find((elem) => elem.id === assignee.id)) {
                    const newAssignees = user ? [] : [...interventionAssignees];
                    newAssignees.push(assignee);
                    setInterventionAssignees(newAssignees);
                }
            } else {
                // cela veut dire que c'est un nouveau user d'un nouveau provider
                setProvider(provider_id);
                setInterventionAssignees([assignee]);
            }
        }
    };

    const removeAssignee = (assignee: User) => {
        const newAssignees = interventionAssignees.filter((x) => {
            if (x.id !== assignee.id) return x;
        });

        setInterventionAssignees(newAssignees);

        if (newAssignees.length === 0 && provider !== null) {
            setProvider(null);
            setUser(null);
        }
    };

    return (
        <div className="border-sidebar-border bg-sidebar font rounded-md border p-4 shadow-xl">
            <div className="flex items-center justify-between">
                <h2 className="inline">
                    {tChoice('interventions.title', 2)} ({interventions?.length ?? 0})
                </h2>
                {!closed && (
                    <Button onClick={openModale}>
                        <PlusCircle />
                        {t('actions.add-type', { type: tChoice('interventions.title', 1) })}
                    </Button>
                )}
            </div>
            {interventions &&
                interventions.length > 0 &&
                interventions.map((intervention, index) => (
                    <>
                        <Table key={intervention.id} className="table-fixed">
                            <TableHead>
                                <TableHeadRow>
                                    <TableHeadData className="w-52">{t('common.description')}</TableHeadData>
                                    <TableHeadData>{t('common.type')}</TableHeadData>
                                    <TableHeadData>{t('interventions.priority.title')}</TableHeadData>
                                    <TableHeadData>{t('common.status.title')}</TableHeadData>
                                    <TableHeadData>{t('interventions.assigned_to')}</TableHeadData>
                                    <TableHeadData>{t('interventions.planned_at')}</TableHeadData>
                                    <TableHeadData>{t('interventions.repair_delay')}</TableHeadData>
                                    <TableHeadData>{t('interventions.total_costs')}</TableHeadData>
                                    <TableHeadData>
                                        <Button onClick={() => sendIntervention(intervention.id)} variant={'cta'}>
                                            {t('interventions.assign_to')}
                                        </Button>
                                    </TableHeadData>
                                </TableHeadRow>
                            </TableHead>

                            <TableBody>
                                <TableBodyRow className="">
                                    <TableBodyData className="flex max-w-72">
                                        <a
                                            className="overflow-hidden overflow-ellipsis whitespace-nowrap"
                                            href={route('tenant.interventions.show', intervention.id)}
                                        >
                                            {intervention.description}
                                        </a>
                                        <p className="tooltip tooltip-bottom">{intervention.description}</p>
                                    </TableBodyData>
                                    <TableBodyData>{intervention.type}</TableBodyData>
                                    <TableBodyData>
                                        <Pill variant={intervention.priority}>{t(`interventions.priority.${intervention.priority}`)}</Pill>
                                    </TableBodyData>
                                    <TableBodyData>{t(`interventions.status.${intervention.status}`)}</TableBodyData>
                                    <TableBodyData>
                                        {intervention.assignable ? (
                                            intervention.assignable.full_name ? (
                                                <a href={route('tenant.users.show', intervention.assignable.id)}>
                                                    {intervention.assignable.full_name}
                                                </a>
                                            ) : (
                                                <a href={route('tenant.providers.show', intervention.assignable.id)}>
                                                    {intervention.assignable.name}
                                                </a>
                                            )
                                        ) : (
                                            t('interventions.assigned_not')
                                        )}
                                    </TableBodyData>
                                    <TableBodyData>{intervention.planned_at ?? t('interventions.planned_at_no')}</TableBodyData>
                                    <TableBodyData>{intervention.repair_delay ?? t('interventions.repair_delay_no')}</TableBodyData>
                                    <TableBodyData>{intervention.total_costs ? `${intervention.total_costs} €` : '-'}</TableBodyData>
                                    <TableBodyData className="flex space-x-2">
                                        {!closed && (
                                            <>
                                                <Button onClick={() => editIntervention(intervention.id)}>
                                                    <Pencil />
                                                </Button>
                                                <Button
                                                    type="button"
                                                    variant="destructive"
                                                    onClick={() => {
                                                        setInterventionToDelete(intervention);
                                                        setShowDeleteInterventionModale(true);
                                                    }}
                                                >
                                                    <Trash2 />
                                                </Button>
                                            </>
                                        )}
                                    </TableBodyData>
                                </TableBodyRow>
                                <TableBodyRow key={`action-${index}`}>
                                    <TableBodyData colSpan={9}>
                                        <InterventionActionManager
                                            interventionId={intervention.id}
                                            actionsChanged={setActionsChanged}
                                            closed={
                                                closed
                                                    ? true
                                                    : intervention.status === 'completed' || intervention.status === 'cancelled'
                                                      ? true
                                                      : false
                                            }
                                        />
                                    </TableBodyData>
                                </TableBodyRow>
                            </TableBody>
                        </Table>
                        <hr className="border-foreground border" />
                    </>
                ))}

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

            {sendInterventionToProviderModale && (
                <ModaleForm title="Assign intervention to provider/user">
                    {isProcessing && (
                        <div className="flex flex-col items-center gap-4">
                            <Loader size={48} className="animate-pulse" />
                            <p className="mx-auto animate-pulse text-3xl font-bold">{t('actions.processing')}</p>
                            <p className="mx-auto">{t('actions.type-being-sent', { type: tChoice('interventions.title', 1) })}</p>
                        </div>
                    )}
                    {!isProcessing && (
                        <div className="flex flex-col gap-4">
                            <p>{t('interventions.assign_to_description')}</p>

                            <div className="flex w-full flex-col">
                                <p className="font-semibold">{t('providers.linked')}</p>
                                {providers ? (
                                    providers.length > 0 ? (
                                        <>
                                            <ul>
                                                {providers.map((provider) => (
                                                    <>
                                                        <li
                                                            key={provider.id}
                                                            className="border-foreground mt-2 cursor-pointer border-t font-bold last:border-b"
                                                            onClick={() => {
                                                                setInterventionAssignees([provider]);
                                                                setProvider(provider.id);
                                                                setUser(null);
                                                            }}
                                                        >
                                                            <p className="hover:bg-accent bg-sidebar-border text-background dark:text-foreground px-2 py-1">
                                                                {provider.name}
                                                                <span className="ml-2 text-sm">({provider.email})</span>
                                                            </p>
                                                            <ul className="mt-1 font-normal">
                                                                {provider.users && provider.users?.length > 0 ? (
                                                                    provider.users.map((user: User) => (
                                                                        <li
                                                                            className="odd:bg-sidebar hover:bg-accent cursor-pointer px-4 py-1"
                                                                            onClick={(e) => {
                                                                                e.stopPropagation();
                                                                                setUser(null);
                                                                                addAssignee(user, provider.id);
                                                                            }}
                                                                        >
                                                                            {user.full_name} - {user.email}
                                                                        </li>
                                                                    ))
                                                                ) : (
                                                                    <p>{t('contacts.none')}</p>
                                                                )}
                                                            </ul>
                                                        </li>
                                                    </>
                                                ))}
                                            </ul>
                                        </>
                                    ) : (
                                        <p>{t('providers.none')}</p>
                                    )
                                ) : (
                                    <p className="animate-pulse">{t('actions.loading')}</p>
                                )}
                            </div>
                            <div>
                                <p className="font-semibold">{t('actions.search-type', { type: tChoice('providers.title', 2) })}</p>

                                <form onSubmit={fetchExternalProviders}>
                                    <Label htmlFor="">{t('actions.search')}</Label>
                                    <div className="flex items-center gap-4">
                                        <Input
                                            type="text"
                                            value={externalProvidersQuery ?? ''}
                                            onChange={(e) => setExternalProvidersQuery(e.target.value)}
                                        />
                                        <Button type="submit">{t('actions.search')}</Button>
                                    </div>
                                </form>
                                <ul>
                                    {externalProviders &&
                                        externalProviders.length > 0 &&
                                        externalProviders?.map((provider, i) => (
                                            <>
                                                <li
                                                    key={provider.id}
                                                    className="border-foreground mt-2 cursor-pointer border-t font-bold last:border-b"
                                                    onClick={() => {
                                                        setInterventionAssignees([provider]);
                                                        setProvider(provider.id);
                                                        setUser(null);
                                                    }}
                                                >
                                                    <p className="hover:bg-accent bg-sidebar-border text-background dark:text-foreground px-2 py-1">
                                                        {provider.name}
                                                        <span className="ml-2 text-sm">({provider.email})</span>
                                                    </p>
                                                    <ul className="mt-1 font-normal">
                                                        {provider.users && provider.users?.length > 0 ? (
                                                            provider.users.map((user: User) => (
                                                                <li
                                                                    className="odd:bg-sidebar hover:bg-accent cursor-pointer px-4 py-1"
                                                                    onClick={(e) => {
                                                                        e.stopPropagation();
                                                                        setUser(null);
                                                                        addAssignee(user, provider.id);
                                                                    }}
                                                                >
                                                                    {user.full_name} -{user.email}
                                                                </li>
                                                            ))
                                                        ) : (
                                                            <p>{t('contacts.none')}</p>
                                                        )}
                                                    </ul>
                                                </li>
                                            </>
                                        ))}
                                    {externalProviders?.length === 0 && <li>{t('common.no_results')}</li>}
                                </ul>
                            </div>
                            <div className="flex w-full flex-col">
                                <p className="font-semibold">{tChoice('contacts.title', 2)}</p>
                                <SearchableInput<User>
                                    searchUrl={route('api.users.search')}
                                    searchParams={{ interns: 1 }}
                                    displayValue={''}
                                    getDisplayText={(user) => user.full_name}
                                    getKey={(user) => user.id}
                                    onDelete={() => {
                                        setInterventionAssignees([]);
                                        setProvider(null);
                                        setUser(null);
                                    }}
                                    onSelect={(user) => {
                                        setInterventionAssignees([user]);
                                        setProvider(null);
                                        setUser(user.id);
                                    }}
                                    placeholder={t('actions.search-type', { type: tChoice('contacts.title', 2) })}
                                    className="mb-4"
                                />
                            </div>

                            {interventionAssignees && (
                                <div className="">
                                    <p className="text-center">{t('interventions.assign_to')} :</p>
                                    <ul className="flex flex-col gap-2">
                                        {interventionAssignees.length > 0 &&
                                            interventionAssignees.map((assignee, index) => (
                                                <li key={index} className="flex items-center">
                                                    <p>
                                                        {assignee.name ? assignee.name : assignee.full_name} - {assignee.email}
                                                    </p>
                                                    <X
                                                        onClick={() => {
                                                            removeAssignee(assignee);
                                                        }}
                                                    />
                                                </li>
                                            ))}
                                    </ul>
                                </div>
                            )}

                            <div className="flex w-full justify-between">
                                <Button
                                    onClick={sendInterventionMail}
                                    disabled={isProcessing || !interventionToSend || interventionAssignees.length === 0}
                                >
                                    {t('actions.send')}
                                </Button>

                                <Button onClick={closeSendInterventionToProviderModale} variant="secondary">
                                    {t('actions.cancel')}
                                </Button>
                            </div>
                        </div>
                    )}
                </ModaleForm>
            )}

            {addIntervention && (
                <ModaleForm title={t('actions.add-type', { type: tChoice('interventions.title', 1) })}>
                    {isProcessing && (
                        <div className="flex flex-col items-center gap-4">
                            <Loader size={48} className="animate-pulse" />
                            <p className="mx-auto animate-pulse text-3xl font-bold">{t('actions.processing')}</p>
                            <p className="mx-auto">{t('actions.type-being-created', { type: tChoice('interventions.title', 1) })}</p>
                        </div>
                    )}
                    {!isProcessing && (
                        <form
                            onSubmit={submitType === 'new' ? submitIntervention : submitEditIntervention}
                            className="flex w-full flex-col space-y-4"
                        >
                            <Label htmlFor="intervention_type">{t('common.type')}</Label>
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
                                {interventionTypes?.map((interventionType) => (
                                    <option key={interventionType.id} value={interventionType.id}>
                                        {interventionType.label}
                                    </option>
                                ))}
                            </select>
                            <Label htmlFor="status">{t('common.status.title')}</Label>
                            <select
                                name="status"
                                id="status"
                                required
                                value={interventionDataForm.status ?? ''}
                                onChange={(e) =>
                                    setInterventionDataForm((prev) => ({
                                        ...prev,
                                        status: e.target.value,
                                    }))
                                }
                            >
                                <option value="">{t('actions.select-type', { type: t('common.status.title') })}</option>
                                <option value="draft">{t('common.status.draft')}</option>
                                <option value="planned">{t('common.status.planned')}</option>
                                <option value="in_progress">{t('common.status.in_progress')}</option>
                                <option value="waiting_parts">{t('common.status.waiting_parts')}</option>
                                <option value="completed">{t('common.status.completed')}</option>
                                <option value="cancelled">{t('common.status.cancelled')}</option>
                            </select>
                            <Label htmlFor="priority">{t('interventions.priority.title')}</Label>
                            <select
                                name=""
                                id="priority"
                                required
                                value={interventionDataForm.priority ?? ''}
                                onChange={(e) =>
                                    setInterventionDataForm((prev) => ({
                                        ...prev,
                                        priority: e.target.value,
                                    }))
                                }
                            >
                                <option value="">{t('actions.select-type', { type: t('interventions.priority.title') })}</option>
                                <option value="low">{t('interventions.priority.low')}</option>
                                <option value="medium">{t('interventions.priority.medium')}</option>
                                <option value="high">{t('interventions.priority.high')}</option>
                                <option value="urgent">{t('interventions.priority.urgent')}</option>
                            </select>
                            <Label htmlFor="description">{t('common.description')}</Label>
                            <Textarea
                                id="description"
                                placeholder="description"
                                maxLength={255}
                                value={interventionDataForm.description ?? ''}
                                onChange={(e) =>
                                    setInterventionDataForm((prev) => ({
                                        ...prev,
                                        description: e.target.value,
                                    }))
                                }
                            ></Textarea>
                            {!closed && (
                                <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                    <h5>{tChoice('common.pictures', 2)}</h5>
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
                            )}
                            <Label>{t('interventions.planned_at')}</Label>
                            <div className="flex items-center gap-2">
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
                            <div className="flex items-center gap-2">
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
                            <div className="flex gap-4">
                                <Button type="submit">
                                    <Label>{t('actions.submit')}</Label>
                                </Button>
                                <Button onClick={closeModale} type="button" variant={'secondary'}>
                                    <Label>{t('actions.cancel')}</Label>
                                </Button>
                            </div>
                        </form>
                    )}
                </ModaleForm>
            )}
        </div>
    );
};
