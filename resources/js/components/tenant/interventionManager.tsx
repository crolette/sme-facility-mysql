import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { CentralType, Intervention } from '@/types';
import axios from 'axios';
import { FormEventHandler, useEffect, useState } from 'react';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Label } from '../ui/label';
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
};

export const InterventionManager = ({ itemCodeId, getInterventionsUrl, type, closed = false }: InterventionManagerProps) => {
    const [interventions, setInterventions] = useState<Intervention[]>([]);

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
            console.error('Erreur lors de la recherche :', error);
            const errors = error.response.data.errors;
            console.error('Erreur de validation :', errors);
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

    const cancelModale = () => {
        setInterventionDataForm(interventionData);
        setAddIntervention(false);
        setSubmitType('edit');
    };
    const closeModale = () => {
        setInterventionDataForm(interventionData);
        setAddIntervention(false);
        fetchInterventions();
        setSubmitType('edit');
    };

    const submitIntervention: FormEventHandler = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.post(route('api.interventions.store'), interventionDataForm);
            if (response.data.status === 'success') {
                closeModale();
            }
        } catch (error) {
            console.error(error);
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
            locationType: intervention?.ticket_id ? null : type,
            locationId: intervention?.ticket_id ? null : (intervention?.interventionable_id ?? null),
        }));
        setAddIntervention(true);
    };

    const submitEditIntervention: FormEventHandler = async (e) => {
        e.preventDefault();
        try {
            const response = await axios.patch(route('api.interventions.update', interventionDataForm.intervention_id), interventionDataForm);
            if (response.data.status === 'success') {
                fetchInterventions();
                setAddIntervention(false);
                setSubmitType('new');
                setInterventionDataForm(interventionData);
            }
        } catch (error) {
            console.error('Erreur lors de la recherche : ', error);
        }
    };

    function formatDateForInput(dateStr: string) {
        const [day, month, year] = dateStr.split('-');
        return `${year}-${month}-${day}`;
    }

    const deleteIntervention = async (id: number) => {
        try {
            const response = await axios.delete(route('api.interventions.destroy', id));
            if (response.data.status === 'success') {
                fetchInterventions();
            }
        } catch (error) {
            console.error(error);
        }
    };

    const [actionsChanged, setActionsChanged] = useState<boolean>(false);
    useEffect(() => {
        fetchInterventions();
        setActionsChanged(false);
    }, [actionsChanged === true]);

    return (
        <div>
            <details>
                <summary className="bg-red-5 border-2 p-2">
                    <h3 className="inline">Interventions ({interventions?.length ?? 0})</h3>
                    {!closed && <Button onClick={openModale}>add intervention</Button>}
                </summary>
                {interventions &&
                    interventions.length > 0 &&
                    interventions.map((intervention, index) => (
                        <Table key={index}>
                            <TableHead>
                                <TableHeadRow>
                                    <TableHeadData>Type</TableHeadData>
                                    <TableHeadData>Description</TableHeadData>
                                    <TableHeadData>Priority</TableHeadData>
                                    <TableHeadData>Status</TableHeadData>
                                    <TableHeadData>Planned at</TableHeadData>
                                    <TableHeadData>Repair delay</TableHeadData>
                                    <TableHeadData>Total costs</TableHeadData>
                                    <TableHeadData></TableHeadData>
                                </TableHeadRow>
                            </TableHead>

                            <TableBody>
                                <TableBodyRow>
                                    <TableBodyData>{intervention.intervention_type.label}</TableBodyData>
                                    <TableBodyData>{intervention.description}</TableBodyData>
                                    <TableBodyData>{intervention.priority}</TableBodyData>
                                    <TableBodyData>{intervention.status}</TableBodyData>
                                    <TableBodyData>{intervention.planned_at ?? 'Not planned'}</TableBodyData>
                                    <TableBodyData>{intervention.repair_delay ?? 'No repair delay'}</TableBodyData>
                                    <TableBodyData>{intervention.total_costs ? `${intervention.total_costs} €` : '-'}</TableBodyData>
                                    <TableBodyData>
                                        {!closed && (
                                            <>
                                                <Button onClick={() => editIntervention(intervention.id)}>Edit</Button>
                                                <Button type="button" variant="destructive" onClick={() => deleteIntervention(intervention.id)}>
                                                    Delete
                                                </Button>
                                            </>
                                        )}
                                    </TableBodyData>
                                </TableBodyRow>
                                <TableBodyRow key={`action-${index}`}>
                                    <TableBodyData colSpan={8}>
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
                    ))}
            </details>
            {addIntervention && (
                <div className="bg-background/50 fixed inset-0 z-50">
                    <div className="bg-background/20 flex h-dvh items-center justify-center">
                        <div className="bg-background flex items-center justify-center p-10">
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
                                <Button onClick={cancelModale} type="button" variant={'secondary'}>
                                    Cancel
                                </Button>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};
