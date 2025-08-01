import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { CentralType, InterventionAction } from '@/types';
import { usePage } from '@inertiajs/react';
import axios from 'axios';
import { FormEventHandler, useEffect, useState } from 'react';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Label } from '../ui/label';
import { Textarea } from '../ui/textarea';

interface InterventionActionManagerProps {
    interventionId: number;
    getInterventionsActionUrl?: string;
    uploadRoute?: string;
    editRoute?: string;
    deleteRoute?: string;
    showRoute?: string;
    closed: boolean;
    actionsChanged: (val: boolean) => void;
}

type InterventionFormData = {
    action_id: null | number;
    intervention_id: null | number;
    action_type_id: null | number;
    description: null | string;
    intervention_date: null | string;
    started_at: null | string;
    finished_at: null | string;
    intervention_costs: null | number;
    created_by: null | number;
    creator_email: null | string;
    updated_by: null | number;
};

export const InterventionActionManager = ({ interventionId, closed, actionsChanged }: InterventionActionManagerProps) => {
    const auth = usePage().props.auth;
    const [interventionActions, setInterventionActions] = useState<InterventionAction[]>([]);

    const [addInterventionAction, setAddInterventionAction] = useState<boolean>(false);
    const [submitType, setSubmitType] = useState<'edit' | 'new'>('edit');

    const fetchInterventionActions = async () => {
        try {
            const response = await axios.get(route('api.interventions.actions.index', interventionId));
            setInterventionActions(response.data.data);
        } catch (error) {
            console.error('Erreur lors de la recherche : ', error);
        }
    };

    const [interventionActionTypes, setInterventionActionTypes] = useState<CentralType[]>([]);

    const fetchInterventionActionTypes = async () => {
        try {
            const response = await axios.get(route('api.category-types', { type: 'action' }));
            setInterventionActionTypes(response.data.data);
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
            const errors = error.response.data.errors;
            console.error('Erreur de validation :', errors);
        }
    };

    useEffect(() => {
        fetchInterventionActions();
    }, []);

    const interventionActionData = {
        action_id: null,
        intervention_id: interventionId,
        action_type_id: null,
        description: null,
        intervention_date: null,
        started_at: null,
        finished_at: null,
        intervention_costs: null,
        created_by: auth?.user ? auth.user.id : null,
        creator_email: null,
        updated_by: null,
    };

    const [interventionActionDataForm, setInterventionActionDataForm] = useState<InterventionFormData>(interventionActionData);

    const openModale = () => {
        setSubmitType('new');

        if (interventionActionTypes.length === 0) fetchInterventionActionTypes();
        setAddInterventionAction(true);
    };

    const cancelModale = () => {
        setInterventionActionDataForm(interventionActionData);
        setAddInterventionAction(false);
        setSubmitType('edit');
    };
    const closeModale = () => {
        setInterventionActionDataForm(interventionActionData);
        setAddInterventionAction(false);
        fetchInterventionActions();
        actionsChanged(true);
        setSubmitType('edit');
    };

    const submitInterventionAction: FormEventHandler = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.post(route('api.interventions.actions.store', interventionId), interventionActionDataForm);
            if (response.data.status === 'success') {
                closeModale();
            }
        } catch (error) {
            console.error(error);
        }
    };

    const editInterventionAction = (id: number) => {
        setSubmitType('edit');
        const interventionAction = interventionActions.find((intervention) => {
            return intervention.id === id;
        });
        if (interventionActionTypes.length === 0) fetchInterventionActionTypes();

        setInterventionActionDataForm((prev) => ({
            ...prev,
            action_id: id,
            action_type_id: interventionAction?.action_type.id ?? null,
            description: interventionAction?.description ?? null,
            intervention_date: interventionAction?.intervention_date ? formatDateForInput(interventionAction?.intervention_date) : null,
            started_at: interventionAction?.started_at ? formatHourForInput(interventionAction?.started_at) : null,
            finished_at: interventionAction?.finished_at ? formatHourForInput(interventionAction?.finished_at) : null,
            intervention_costs: interventionAction?.intervention_costs ?? null,
            updated_by: null,
        }));
        setAddInterventionAction(true);
    };

    const submitEditInterventionAction: FormEventHandler = async (e) => {
        e.preventDefault();
        try {
            const response = await axios.patch(
                route('api.interventions.actions.update', interventionActionDataForm.action_id),
                interventionActionDataForm,
            );
            if (response.data.status === 'success') {
                closeModale();
            }
        } catch (error) {
            console.error('Erreur lors de la recherche : ', error);
        }
    };

    function formatDateForInput(dateStr: string) {
        const [day, month, year] = dateStr.split('-');
        return `${year}-${month}-${day}`;
    }

    function formatHourForInput(dateStr: string) {
        const [hours, minutes] = dateStr.split(':');
        return `${hours}:${minutes}`;
    }

    const deleteInterventionAction = async (id: number) => {
        try {
            const response = await axios.delete(route('api.interventions.actions.destroy', id));
            if (response.data.status === 'success') {
                fetchInterventionActions();
            }
        } catch (error) {
            console.error(error);
        }
    };

    return (
        <>
            <ul>
                Actions ({interventionActions.length}){!closed && <Button onClick={openModale}>add action</Button>}
                {interventionActions && interventionActions.length > 0 && (
                    <Table>
                        <TableHead>
                            <TableHeadRow>
                                <TableHeadData>Description</TableHeadData>
                                <TableHeadData>Action</TableHeadData>
                                <TableHeadData>Date</TableHeadData>
                                <TableHeadData>Started at</TableHeadData>
                                <TableHeadData>Finished at</TableHeadData>
                                <TableHeadData>Costs</TableHeadData>
                                {/* <TableHeadData>Description</TableHeadData> */}
                                {/* <TableHeadData>Planned at</TableHeadData> */}
                                {/* <TableHeadData>Repair delay</TableHeadData> */}
                                <TableHeadData></TableHeadData>
                            </TableHeadRow>
                        </TableHead>

                        <TableBody>
                            {interventionActions.map((action, index) => (
                                <TableBodyRow key={index}>
                                    <TableBodyData>{action.description}</TableBodyData>
                                    <TableBodyData>{action.action_type.label}</TableBodyData>
                                    <TableBodyData>{action.intervention_date}</TableBodyData>
                                    <TableBodyData>{action.started_at}</TableBodyData>
                                    <TableBodyData>{action.finished_at}</TableBodyData>
                                    <TableBodyData>{action.intervention_costs ? `${action.intervention_costs} €` : '-'} </TableBodyData>

                                    <TableBodyData>
                                        {!closed && (
                                            <>
                                                <Button onClick={() => editInterventionAction(action.id)}>Edit</Button>
                                                <Button type="button" variant="destructive" onClick={() => deleteInterventionAction(action.id)}>
                                                    Delete
                                                </Button>
                                            </>
                                        )}
                                    </TableBodyData>
                                </TableBodyRow>
                            ))}
                        </TableBody>
                    </Table>
                )}
            </ul>
            {addInterventionAction && (
                <div className="bg-background/50 absolute inset-0 z-50">
                    <div className="bg-background/20 flex h-dvh items-center justify-center">
                        <div className="bg-background flex items-center justify-center p-4">
                            <form
                                onSubmit={submitType === 'new' ? submitInterventionAction : submitEditInterventionAction}
                                className="flex flex-col space-y-2"
                            >
                                <Label>Action Type</Label>
                                <select
                                    name="action_type"
                                    id="intervention_type"
                                    required
                                    value={interventionActionDataForm.action_type_id ?? ''}
                                    onChange={(e) =>
                                        setInterventionActionDataForm((prev) => ({
                                            ...prev,
                                            action_type_id: parseInt(e.target.value),
                                        }))
                                    }
                                >
                                    <option value="">Select action type</option>
                                    {interventionActionTypes?.map((interventionActionType) => (
                                        <option key={interventionActionType.id} value={interventionActionType.id}>
                                            {interventionActionType.label}
                                        </option>
                                    ))}
                                </select>

                                <Label>Description</Label>
                                <Textarea
                                    placeholder="description"
                                    value={interventionActionDataForm.description ?? ''}
                                    onChange={(e) =>
                                        setInterventionActionDataForm((prev) => ({
                                            ...prev,
                                            description: e.target.value,
                                        }))
                                    }
                                ></Textarea>
                                <Label>Intervention costs</Label>
                                <Input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={interventionActionDataForm.intervention_costs ?? ''}
                                    onChange={(e) =>
                                        setInterventionActionDataForm((prev) => ({
                                            ...prev,
                                            intervention_costs: parseFloat(e.target.value),
                                        }))
                                    }
                                />
                                <Label>Intervention date</Label>
                                <Input
                                    type="date"
                                    value={interventionActionDataForm.intervention_date ?? ''}
                                    onChange={(e) =>
                                        setInterventionActionDataForm((prev) => ({
                                            ...prev,
                                            intervention_date: e.target.value,
                                        }))
                                    }
                                />
                                <Button
                                    variant={'outline'}
                                    type="button"
                                    onClick={() =>
                                        setInterventionActionDataForm((prev) => ({
                                            ...prev,
                                            intervention_date: null,
                                        }))
                                    }
                                >
                                    Clear planned at
                                </Button>
                                <Label>Started at</Label>
                                <Input
                                    type="time"
                                    value={interventionActionDataForm.started_at ?? ''}
                                    onChange={(e) =>
                                        setInterventionActionDataForm((prev) => ({
                                            ...prev,
                                            started_at: e.target.value,
                                        }))
                                    }
                                />
                                <Button
                                    variant={'outline'}
                                    type="button"
                                    onClick={() =>
                                        setInterventionActionDataForm((prev) => ({
                                            ...prev,
                                            started_at: null,
                                        }))
                                    }
                                >
                                    Clear Started at
                                </Button>
                                <Label>Finished at</Label>
                                <Input
                                    type="time"
                                    value={interventionActionDataForm.finished_at ?? ''}
                                    onChange={(e) =>
                                        setInterventionActionDataForm((prev) => ({
                                            ...prev,
                                            finished_at: e.target.value,
                                        }))
                                    }
                                />
                                <Button
                                    variant={'outline'}
                                    type="button"
                                    onClick={() =>
                                        setInterventionActionDataForm((prev) => ({
                                            ...prev,
                                            finished_at: null,
                                        }))
                                    }
                                >
                                    Clear finished at
                                </Button>
                                <Button type="submit">Submit</Button>
                                <Button onClick={cancelModale} type="button">
                                    Cancel
                                </Button>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
};
