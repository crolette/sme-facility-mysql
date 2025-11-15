import InputError from '@/components/input-error';
import ModaleForm from '@/components/ModaleForm';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { CentralType, Intervention, InterventionAction } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { BadgeAlert, BadgeCheck, Loader } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

type InterventionFormData = {
    action_id: null | number;
    intervention_id: null | number;
    action_type_id: null | number;
    description: null | string;
    intervention_date: null | string;
    started_at: null | string;
    finished_at: null | string;
    intervention_costs: null | number;
    creator_email: null | string;
    pictures: FileList | null;
};

export default function InterventionProviderPage({
    intervention,
    email,
    actionTypes,
    query,
    pastInterventions,
}: {
    intervention: Intervention;
    email: string;
    actionTypes: CentralType[];
    query: string;
    pastInterventions: Intervention[];
}) {
    const { t, tChoice } = useLaravelReactI18n();
    const [errors, setErrors] = useState<{ [key: string]: string }>();
    const [isProcessing, setIsProcessing] = useState(false);
    const [showSuccessModale, setShowSuccessModale] = useState<boolean>(false);
    const [showErrorModale, setShowErrorModale] = useState<boolean>(false);

    const { data, setData } = useForm<InterventionFormData>({
        action_id: null,
        intervention_id: intervention.id,
        action_type_id: null,
        description: null,
        intervention_date: new Date().toISOString().split('T')[0],
        started_at: null,
        finished_at: null,
        intervention_costs: null,
        creator_email: email,
        pictures: [],
    });

    const submitInterventionAction: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);
        const url = route('tenant.intervention.provider.store', intervention.id) + '?' + query;

        try {
            const response = await axios.post(url, data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            if (response.data.status === 'success') {
                setIsProcessing(false);
                setShowSuccessModale(true);
            }
        } catch (error) {
            setErrors(error.response.data.errors);
            setIsProcessing(false);
            setShowErrorModale(true);
        }
    };

    return (
        <>
            <Head title="Intervention Ticket" />
            <div className="bg-accent flex items-center justify-center py-5 md:py-10">
                <div className="grid w-full grid-cols-1 gap-5 px-5 md:grid-cols-[2fr_1fr] md:gap-10 md:px-10">
                    <div className="flex flex-col gap-5 md:gap-10">
                        <div className="border-sidebar-border bg-sidebar flex w-full flex-col rounded-md border p-4 shadow-xl">
                            <div className="my-4">
                                <h3>
                                    {intervention.interventionable.name} ({intervention.interventionable.category})
                                </h3>
                                <p>{intervention.interventionable.description}</p>

                                <h4>{t('common.information')}</h4>
                                <p>{intervention.intervention_type.label}</p>
                                <p>
                                    {t('interventions.last_update')}: {intervention.updated_at}
                                </p>
                                <p>{intervention.description}</p>
                                <ul className="bg-accent flex flex-col gap-3 rounded-md p-2">
                                    <h4>{t('interventions.past_actions')}</h4>
                                    <ul className="bg-secondary pl-5">
                                        {intervention.actions?.map((interventionAction: InterventionAction) => (
                                            <li key={interventionAction.id}>
                                                <p>
                                                    {interventionAction.updated_at}- {interventionAction.type} - {interventionAction.description}
                                                </p>
                                            </li>
                                        ))}
                                    </ul>
                                </ul>
                            </div>
                        </div>
                        <div className="border-sidebar-border bg-sidebar flex w-full flex-col rounded-md border p-4 shadow-xl">
                            <h3>{tChoice('interventions.title', 1)}</h3>
                            <form onSubmit={submitInterventionAction} className="flex flex-col gap-4">
                                <Label>{t('common.email')}</Label>
                                <Input
                                    type="email"
                                    required
                                    value={data.creator_email ?? ''}
                                    onChange={(e) => setData('creator_email', e.target.value)}
                                />
                                <InputError message={errors?.creator_email ?? ''} />
                                <Label>{tChoice('interventions.actions', 1)}</Label>
                                <select
                                    name="action_type"
                                    id="intervention_type"
                                    required
                                    value={data.action_type_id ?? ''}
                                    onChange={(e) => setData('action_type_id', parseInt(e.target.value))}
                                >
                                    <option value="">{t('actions.select-type', { type: tChoice('interventions.actions', 1) })}</option>
                                    {actionTypes?.map((interventionActionType) => (
                                        <option key={interventionActionType.id} value={interventionActionType.id}>
                                            {interventionActionType.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors?.action_type_id ?? ''} />
                                <Label>{t('common.description')}</Label>
                                <Textarea
                                    placeholder="description"
                                    value={data.description ?? ''}
                                    required
                                    onChange={(e) => setData('description', e.target.value)}
                                ></Textarea>
                                <InputError message={errors?.description ?? ''} />
                                <Label>{t('interventions.costs')}</Label>
                                <Input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={data.intervention_costs ?? ''}
                                    onChange={(e) => setData('intervention_costs', parseFloat(e.target.value))}
                                />
                                <InputError message={errors?.intervention_costs ?? ''} />
                                <Label>{t('common.date')}</Label>
                                <Input
                                    type="date"
                                    value={data.intervention_date ?? ''}
                                    required
                                    onChange={(e) => setData('intervention_date', e.target.value)}
                                />
                                <InputError message={errors?.intervention_date ?? ''} />
                                <Label>{t('interventions.started_at')}</Label>
                                <Input type="time" value={data.started_at ?? ''} onChange={(e) => setData('started_at', e.target.value)} />
                                <InputError message={errors?.started_at ?? ''} />
                                <Label>{t('interventions.finished_at')}</Label>
                                <Input type="time" value={data.finished_at ?? ''} onChange={(e) => setData('finished_at', e.target.value)} />
                                <InputError message={errors?.finished_at ?? ''} />
                                <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                    <h5>{tChoice('common.pictures', 2)}</h5>
                                    <Input
                                        type="file"
                                        multiple
                                        onChange={(e) => setData('pictures', e.target.files)}
                                        accept="image/png, image/jpeg, image/jpg"
                                    />
                                </div>
                                <Button>{t('actions.add-type', { type: tChoice('interventions.title', 1) })}</Button>
                            </form>
                        </div>
                    </div>
                    <div className="border-sidebar-border bg-sidebar sticky top-10 flex h-fit flex-col rounded-md border p-4 shadow-xl">
                        <h3>{t('interventions.past_interventions')}</h3>
                        {pastInterventions.length > 0 ? (
                            <ul className="bg-accent flex flex-col gap-3 rounded-md p-2">
                                {pastInterventions.map((intervention: Intervention) => (
                                    <li key={intervention.id} className="">
                                        <p>
                                            {intervention.updated_at} : {intervention.type} - {intervention.description}
                                        </p>
                                        <ul className="bg-secondary pl-5">
                                            {intervention.actions?.map((interventionAction: InterventionAction) => (
                                                <li key={interventionAction.id}>
                                                    <p>
                                                        {interventionAction.type} - {interventionAction.description}
                                                    </p>
                                                </li>
                                            ))}
                                        </ul>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p>No interventions</p>
                        )}
                    </div>
                </div>
            </div>
            {showSuccessModale && (
                <ModaleForm>
                    <div className="flex flex-col items-center gap-4">
                        <BadgeCheck size={48} className="text-success" />
                        <p className="text-success mx-auto text-3xl font-bold">{t('common.thank_you')}</p>
                        <p className="mx-auto">Intervention submitted</p>
                        <p className="mx-auto">{t('common.close_window')}</p>
                        <div className="mx-auto flex gap-4"></div>
                    </div>
                </ModaleForm>
            )}
            {showErrorModale && (
                <ModaleForm>
                    <div className="flex flex-col items-center gap-4">
                        <BadgeAlert size={48} className="text-destructive" />
                        <p className="text-destructive mx-auto text-3xl font-bold">{t('common.error')}</p>
                        <p className="mx-auto">{t('common.error_submitting')}</p>
                        <div className="mx-auto flex gap-4">
                            <Button variant={'secondary'} onClick={() => setShowErrorModale(false)}>
                                {t('actions.close')}
                            </Button>
                        </div>
                    </div>
                </ModaleForm>
            )}

            {isProcessing && (
                <ModaleForm>
                    <div className="flex flex-col items-center gap-4">
                        <Loader size={48} className="animate-pulse" />
                        <p className="mx-auto animate-pulse text-3xl font-bold">{t('actions.processing')}</p>
                        <p className="mx-auto">{t('actions.type-being-submitted', { type: tChoice('interventions.title', 1) })}</p>
                    </div>
                </ModaleForm>
            )}
        </>
    );
}
