import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { CentralType, Intervention, Provider, User } from '@/types';
import axios from 'axios';
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
            console.log(response.data.data);
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
    const [interventionAssignee, setInterventionAssignee] = useState<User | null>(null);
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

    const sendInterventionMail = async () => {
        if (!interventionToSend || !interventionAssignee || (!provider && !user)) return;

        setIsProcessing(true);

        try {
            const response = await axios.post(route('api.interventions.send-provider', interventionToSend), {
                email: interventionAssignee.email,
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
        setInterventionAssignee(null);
        setProviders(null);
        setProvider(null);
        setUser(null);
        setInterventionToSend(null);
        setIsProcessing(false);
        fetchInterventions();
    };

    console.log(interventions);
    console.log(user);
    console.log(provider);
    console.log(interventionAssignee);

    return (
        <div className="border-sidebar-border bg-sidebar font rounded-md border p-4 shadow-xl">
            <div className="flex items-center justify-between">
                <h2 className="inline">Interventions ({interventions?.length ?? 0})</h2>
                {!closed && (
                    <Button onClick={openModale}>
                        <PlusCircle />
                        Add intervention
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
                                    <TableHeadData className="">Description</TableHeadData>
                                    <TableHeadData>Type</TableHeadData>
                                    <TableHeadData>Priority</TableHeadData>
                                    <TableHeadData>Status</TableHeadData>
                                    <TableHeadData>Assigned to</TableHeadData>
                                    <TableHeadData>Planned at</TableHeadData>
                                    <TableHeadData>Repair delay</TableHeadData>
                                    <TableHeadData>Total costs</TableHeadData>
                                    <TableHeadData>
                                        <Button onClick={() => sendIntervention(intervention.id)} variant={'cta'}>
                                            Assign To
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
                                        <Pill variant={intervention.priority}>{intervention.priority}</Pill>
                                    </TableBodyData>
                                    <TableBodyData>{intervention.status}</TableBodyData>
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
                                            'not assigned'
                                        )}
                                    </TableBodyData>
                                    <TableBodyData>{intervention.planned_at ?? 'Not planned'}</TableBodyData>
                                    <TableBodyData>{intervention.repair_delay ?? 'No repair delay'}</TableBodyData>
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

            {sendInterventionToProviderModale && (
                <ModaleForm title="Assign intervention to provider/user">
                    {isProcessing && (
                        <div className="flex flex-col items-center gap-4">
                            <Loader size={48} className="animate-pulse" />
                            <p className="mx-auto animate-pulse text-3xl font-bold">Processing...</p>
                            <p className="mx-auto">Intervention is being sent...</p>
                        </div>
                    )}
                    {!isProcessing && (
                        <div className="flex flex-col gap-4">
                            <p>Select user provider or internal user to assign this intervention to</p>

                            <div className="flex w-full flex-col">
                                <p className="font-semibold">Linked Providers</p>
                                {providers ? (
                                    providers.length > 0 ? (
                                        <>
                                            <ul>
                                                {providers.map((provider) => (
                                                    <>
                                                        <li key={provider.id} className="font-bold">
                                                            {provider.name}
                                                        </li>
                                                        <ul>
                                                            {provider.users && provider.users?.length > 0 ? (
                                                                provider.users.map((user: User) => (
                                                                    <li
                                                                        className="cursor-pointer"
                                                                        onClick={() => {
                                                                            setInterventionAssignee(user);
                                                                            setProvider(provider.id);
                                                                            setUser(null);
                                                                        }}
                                                                    >
                                                                        {user.full_name} -{user.email}
                                                                    </li>
                                                                ))
                                                            ) : (
                                                                <p>No users</p>
                                                            )}
                                                        </ul>
                                                    </>
                                                ))}
                                            </ul>
                                        </>
                                    ) : (
                                        <p>No providers</p>
                                    )
                                ) : (
                                    <p className="animate-pulse">Loading providers...</p>
                                )}
                            </div>
                            <div className="flex w-full flex-col">
                                <p className="font-semibold">Internal users</p>
                                <SearchableInput<User>
                                    searchUrl={route('api.users.search')}
                                    searchParams={{ interns: 1 }}
                                    displayValue={''}
                                    getDisplayText={(user) => user.full_name}
                                    getKey={(user) => user.id}
                                    onDelete={() => setInterventionAssignee(null)}
                                    onSelect={(user) => {
                                        setInterventionAssignee(user);
                                        setProvider(null);
                                        setUser(user.id);
                                    }}
                                    placeholder="Search internal user..."
                                    className="mb-4"
                                />
                            </div>

                            {interventionAssignee && (
                                <div>
                                    <p className="text-center">Send email to :</p>
                                    <div className="flex">
                                        <p>
                                            {interventionAssignee.full_name} - {interventionAssignee.email}
                                        </p>
                                        <X
                                            onClick={() => {
                                                setInterventionAssignee(null);
                                                setProvider(null);
                                                setUser(null);
                                            }}
                                        />
                                    </div>
                                </div>
                            )}

                            <div className="flex w-full justify-between">
                                <Button onClick={sendInterventionMail} disabled={!interventionToSend || !interventionAssignee}>
                                    Send
                                </Button>

                                <Button onClick={closeSendInterventionToProviderModale} variant="secondary">
                                    Cancel
                                </Button>
                            </div>
                        </div>
                    )}
                </ModaleForm>
            )}

            {addIntervention && (
                <ModaleForm title="Add intervention">
                    {isProcessing && (
                        <div className="flex flex-col items-center gap-4">
                            <Loader size={48} className="animate-pulse" />
                            <p className="mx-auto animate-pulse text-3xl font-bold">Processing...</p>
                            <p className="mx-auto">Intervention is being added...</p>
                        </div>
                    )}
                    {!isProcessing && (
                        <form
                            onSubmit={submitType === 'new' ? submitIntervention : submitEditIntervention}
                            className="flex w-full flex-col space-y-4"
                        >
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
                                {interventionTypes?.map((interventionType) => (
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
                            {!closed && (
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
                            )}
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
                </ModaleForm>
            )}
        </div>
    );
};
