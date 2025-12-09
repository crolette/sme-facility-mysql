import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { CentralType, InterventionAction } from '@/types';
import { usePage } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Pencil, PlusCircle, Trash2 } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import Modale from '../Modale';
import ModaleForm from '../ModaleForm';
import { useToast } from '../ToastrContext';
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
    pictures: FileList | null;
};

export const InterventionActionManager = ({ interventionId, closed, actionsChanged }: InterventionActionManagerProps) => {
    const { t, tChoice } = useLaravelReactI18n();
    const auth = usePage().props.auth;
    const { showToast } = useToast();
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
        pictures: [],
    };

    const [interventionActionDataForm, setInterventionActionDataForm] = useState<InterventionFormData>(interventionActionData);

    // console.log(interventionActionData);
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
            const response = await axios.post(route('api.interventions.actions.store', interventionId), interventionActionDataForm, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            if (response.data.status === 'success') {
                closeModale();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
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
        if (!interventionActionDataForm.action_id) return;

        try {
            const response = await axios.patch(
                route('api.interventions.actions.update', interventionActionDataForm.action_id),
                interventionActionDataForm,
            );
            if (response.data.status === 'success') {
                console.log('success');
                closeModale();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
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

    const [showDeleteActionModale, setShowDeleteActionModale] = useState<boolean>(false);
    const [actionToDelete, setActionToDelete] = useState<null | InterventionAction>(null);
    const deleteInterventionAction = async () => {
        if (!actionToDelete) return;

        try {
            const response = await axios.delete(route('api.interventions.actions.destroy', actionToDelete.id));
            if (response.data.status === 'success') {
                fetchInterventionActions();
                showToast(response.data.message, response.data.status);
                setShowDeleteActionModale(false);
                setActionToDelete(null);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    return (
        <>
            <ul className={'bg-secondary p-2'}>
                <div className="flex items-center gap-4">
                    <span className="font-semibold">
                        {tChoice('interventions.actions', 2)} ({interventionActions.length})
                    </span>
                    {!closed && (
                        <Button onClick={openModale} size="xs" variant={'outline'}>
                            <PlusCircle />
                            <span>{t('actions.add-type', { type: tChoice('interventions.actions', 1) })} </span>
                        </Button>
                    )}
                </div>
                {interventionActions && interventionActions.length > 0 && (
                    <Table className="">
                        <TableHead>
                            <TableHeadRow>
                                <TableHeadData className="">{t('common.description')}</TableHeadData>
                                <TableHeadData className="w-32">{tChoice('interventions.actions', 1)}</TableHeadData>
                                <TableHeadData className="w-32">{t('common.date')}</TableHeadData>
                                <TableHeadData className="w-32">{t('interventions.started_at')}</TableHeadData>
                                <TableHeadData className="w-32">{t('interventions.finished_at')}</TableHeadData>
                                <TableHeadData className="w-32">{t('interventions.costs')}</TableHeadData>
                                <TableHeadData></TableHeadData>
                            </TableHeadRow>
                        </TableHead>

                        <TableBody>
                            {interventionActions.map((action, index) => (
                                <TableBodyRow key={index}>
                                    <TableBodyData className="max-w-72">
                                        <p className="overflow-hidden overflow-ellipsis whitespace-nowrap">{action.description}</p>
                                    </TableBodyData>
                                    <TableBodyData className="w-32">{action.action_type.label}</TableBodyData>
                                    <TableBodyData className="w-32">{action.intervention_date}</TableBodyData>
                                    <TableBodyData className="w-32">{action.started_at}</TableBodyData>
                                    <TableBodyData className="w-32">{action.finished_at}</TableBodyData>
                                    <TableBodyData className="w-32">
                                        {action.intervention_costs ? `${action.intervention_costs} €` : '-'}{' '}
                                    </TableBodyData>

                                    <TableBodyData className="space-x-2">
                                        {!closed && (
                                            <>
                                                <Button onClick={() => editInterventionAction(action.id)}>
                                                    <Pencil />
                                                </Button>
                                                <Button
                                                    type="button"
                                                    variant="destructive"
                                                    onClick={() => {
                                                        setActionToDelete(action);
                                                        setShowDeleteActionModale(true);
                                                    }}
                                                >
                                                    <Trash2 />
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
            <Modale
                title={'Delete definitely'}
                message={
                    'Are you sure to delete this action ? You will not be able to restore it afterwards ! All pictures, documents, ... will be deleted too.'
                }
                isOpen={showDeleteActionModale}
                onConfirm={deleteInterventionAction}
                onCancel={() => {
                    setActionToDelete(null);
                    setShowDeleteActionModale(false);
                }}
            />
            {addInterventionAction && (
                <ModaleForm title={t('actions.add-type', { type: tChoice('interventions.actions', 1) })}>
                    <form
                        onSubmit={submitType === 'new' ? submitInterventionAction : submitEditInterventionAction}
                        className="flex flex-col space-y-4"
                    >
                        <div className="flex flex-col gap-2 space-y-2">
                            <Label htmlFor="intervention_action_type">
                                {tChoice('interventions.actions', 1)} {t('common.type')}
                            </Label>
                            <select
                                name="action_type"
                                id="intervention_action_type"
                                required
                                value={interventionActionDataForm.action_type_id ?? ''}
                                onChange={(e) =>
                                    setInterventionActionDataForm((prev) => ({
                                        ...prev,
                                        action_type_id: parseInt(e.target.value),
                                    }))
                                }
                            >
                                <option value="">{t('actions.select-type', { type: t('common.type') })}</option>
                                {interventionActionTypes?.map((interventionActionType) => (
                                    <option key={interventionActionType.id} value={interventionActionType.id}>
                                        {interventionActionType.label}
                                    </option>
                                ))}
                            </select>
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="description">{t('common.description')}</Label>
                            <Textarea
                                id="description"
                                placeholder="description"
                                value={interventionActionDataForm.description ?? ''}
                                onChange={(e) =>
                                    setInterventionActionDataForm((prev) => ({
                                        ...prev,
                                        description: e.target.value,
                                    }))
                                }
                            ></Textarea>
                        </div>
                        {!closed && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h5>{tChoice('common.pictures', 2)}</h5>
                                <Input
                                    type="file"
                                    multiple
                                    onChange={(e) =>
                                        setInterventionActionDataForm((prev) => ({
                                            ...prev,
                                            pictures: e.target.files,
                                        }))
                                    }
                                    accept="image/png, image/jpeg, image/jpg"
                                />
                            </div>
                        )}
                        <div className="space-y-2">
                            <Label htmlFor="intervention_costs">
                                {tChoice('interventions.title', 1)} {t('interventions.costs')}
                            </Label>
                            <Input
                                id="intervention_costs"
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
                        </div>
                        <div className="space-y-2">
                            <Label htmlFor="intervention_date">
                                {tChoice('interventions.title', 1)} {t('common.date')}
                            </Label>
                            <div className="flex flex-col gap-4 sm:flex-row">
                                <Input
                                    id="intervention_date"
                                    type="date"
                                    required
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
                                    className="w-full"
                                    onClick={() =>
                                        setInterventionActionDataForm((prev) => ({
                                            ...prev,
                                            intervention_date: null,
                                        }))
                                    }
                                >
                                    {t('actions.clear-type', { type: tChoice('interventions.title', 1) + ' ' + t('common.date') })}
                                </Button>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label>{t('interventions.started_at')}</Label>
                            <div className="flex flex-col gap-4 sm:flex-row">
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
                                    className="w-full"
                                    onClick={() =>
                                        setInterventionActionDataForm((prev) => ({
                                            ...prev,
                                            started_at: null,
                                        }))
                                    }
                                >
                                    {t('actions.clear-type', { type: t('interventions.started_at') })}
                                </Button>
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label>{t('interventions.finished_at')}</Label>
                            <div className="flex flex-col gap-4 sm:flex-row">
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
                                    className="w-full"
                                    onClick={() =>
                                        setInterventionActionDataForm((prev) => ({
                                            ...prev,
                                            finished_at: null,
                                        }))
                                    }
                                >
                                    {t('actions.clear-type', { type: t('interventions.finished_at') })}
                                </Button>
                            </div>
                        </div>
                        <div className="flex justify-between gap-4">
                            <Button type="submit">{t('actions.submit')}</Button>
                            <Button onClick={cancelModale} type="button" variant={'secondary'}>
                                {t('actions.cancel')}
                            </Button>
                        </div>
                    </form>
                </ModaleForm>
            )}
        </>
    );
};
