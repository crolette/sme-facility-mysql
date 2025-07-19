import { CentralType, Intervention } from '@/types';
import axios from 'axios';
import { FormEventHandler, useEffect, useState } from 'react';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Label } from '../ui/label';
import { Textarea } from '../ui/textarea';

interface InterventionManagerProps {
    itemCodeId: number | string;
    getInterventionsUrl: string;
    uploadRoute?: string;
    editRoute?: string;
    deleteRoute?: string;
    showRoute?: string;
    type: string;
}

type InterventionFormData = {
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

export const InterventionManager = ({ itemCodeId, getInterventionsUrl, type }: InterventionManagerProps) => {
    const [interventions, setInterventions] = useState<Intervention[]>([]);

    const [addIntervention, setAddIntervention] = useState<boolean>(false);

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
            const response = await axios.get(`/api/v1/category-types/?type=intervention`);
            setInterventionTypes(await response.data.data);
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

        fetchInterventionTypes();
        setAddIntervention(true);
    };

    const cancelModale = () => {
        setInterventionDataForm(interventionData);
        setAddIntervention(false);
    };
    const closeModale = () => {
        setInterventionDataForm(interventionData);
        setAddIntervention(false);
        fetchInterventions();
    };

    const submitIntervention: FormEventHandler = async (e) => {
        e.preventDefault();
        console.log('submit');
        try {
            const response = await axios.post(route('api.interventions.store'), interventionDataForm);
            console.log(response.data.message);
            if (response.data.status === 'success') {
                closeModale();
            }
        } catch (error) {
            console.error(error);
        }
    };

    console.log(interventionDataForm);

    return (
        <div>
            Intervention Manager ({interventions.length})
            <ul>
                {interventions &&
                    interventions.map((intervention) => (
                        <li key={intervention.id}>
                            <p>Description: {intervention.description}</p>
                            <p>Priority: {intervention.priority}</p>
                            <p>Status: {intervention.status}</p>
                            <p>Planned at: {intervention.planned_at ?? 'Not planned'}</p>
                            <p>Repair delay: {intervention.repair_delay ?? 'Not repair delay'}</p>
                            Actions
                            <ul>
                                {intervention.actions?.map((action) => (
                                    <li>
                                        <p>{action.description}</p>
                                        <p>{action.intervention_date}</p>
                                    </li>
                                ))}
                            </ul>
                        </li>
                    ))}
            </ul>
            <Button onClick={openModale}>add intervention</Button>
            {addIntervention && (
                <div className="bg-background/50 absolute inset-0 z-50">
                    <div className="bg-background/20 flex h-dvh items-center justify-center">
                        <div className="bg-background flex items-center justify-center p-4">
                            <form onSubmit={submitIntervention} className="flex flex-col space-y-2">
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
                                    onChange={(e) =>
                                        setInterventionDataForm((prev) => ({
                                            ...prev,
                                            description: e.target.value,
                                        }))
                                    }
                                ></Textarea>
                                <Label>Planned at</Label>
                                <Input
                                    type="date"
                                    onChange={(e) =>
                                        setInterventionDataForm((prev) => ({
                                            ...prev,
                                            planned_at: e.target.value,
                                        }))
                                    }
                                />
                                <Label>Repair delay</Label>
                                <Input
                                    type="date"
                                    onChange={(e) =>
                                        setInterventionDataForm((prev) => ({
                                            ...prev,
                                            repair_delay: e.target.value,
                                        }))
                                    }
                                />
                                <Button type="submit">Submit</Button>
                                <Button onClick={cancelModale} type="button">
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
