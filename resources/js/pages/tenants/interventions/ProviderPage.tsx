import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, CentralType, Intervention, InterventionAction, Ticket } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
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
};

export default function ProviderPage({ intervention, email, actionTypes, query, pastInterventions }: { intervention: Intervention; email: string; actionTypes: CentralType[]; query: string; pastInterventions: Intervention[] }) {

    const submitIntervention: FormEventHandler = (e) => {
        e.preventDefault();

    }

    console.log(pastInterventions);

    const [errors, setErrors] = useState<InterventionFormData>();
    const { data, setData } = useForm<InterventionFormData>(
        {
         action_id: null,
    intervention_id: intervention.id,
    action_type_id: null,
    description: null,
    intervention_date: Date.now().toString(),
    started_at: null,
    finished_at: null,
    intervention_costs: null,
    creator_email: email,
    })

       const submitInterventionAction: FormEventHandler = async (e) => {
           e.preventDefault();
           const url = route('tenant.intervention.provider.store', intervention.id) + '?' + query

           try {
               const response = await axios.post(url, data);
               if (response.data.status === 'success') {
                   //    closeModale();
                   console.log(response);
               }
           } catch (error) {
               console.error(error);
               setErrors(error.response.data.errors);
           }
       };
    
    console.log(intervention);
    
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

                                <h4>Intervention Information</h4>
                                <p>{intervention.intervention_type.label}</p>
                                <p>Last update: {intervention.updated_at}</p>
                                <p>{intervention.description}</p>
                                <ul className="bg-accent flex flex-col gap-3 rounded-md p-2">
                                    <h4>Past actions</h4>
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
                            <h3>Intervention</h3>
                            <form onSubmit={submitInterventionAction} className="flex flex-col gap-4">
                                <Label>E-mail</Label>
                                <Input
                                    type="email"
                                    required
                                    value={data.creator_email ?? ''}
                                    onChange={(e) => setData('creator_email', e.target.value)}
                                />
                                <InputError message={errors?.creator_email ?? ''} />
                                <Label>Action Type</Label>
                                <select
                                    name="action_type"
                                    id="intervention_type"
                                    required
                                    value={data.action_type_id ?? ''}
                                    onChange={(e) => setData('action_type_id', parseInt(e.target.value))}
                                >
                                    <option value="">Select action type</option>
                                    {actionTypes?.map((interventionActionType) => (
                                        <option key={interventionActionType.id} value={interventionActionType.id}>
                                            {interventionActionType.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError message={errors?.action_type_id ?? ''} />
                                <Label>Description</Label>
                                <Textarea
                                    placeholder="description"
                                    value={data.description ?? ''}
                                    required
                                    onChange={(e) => setData('description', e.target.value)}
                                ></Textarea>
                                <InputError message={errors?.description ?? ''} />
                                <Label>Intervention costs</Label>
                                <Input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    value={data.intervention_costs ?? ''}
                                    onChange={(e) => setData('intervention_costs', parseFloat(e.target.value))}
                                />
                                <InputError message={errors?.intervention_costs ?? ''} />
                                <Label>Intervention date</Label>
                                <Input
                                    type="date"
                                    value={data.intervention_date ?? ''}
                                    required
                                    onChange={(e) => setData('intervention_date', e.target.value)}
                                />
                                <InputError message={errors?.intervention_date ?? ''} />
                                <Label>Started at</Label>
                                <Input type="time" value={data.started_at ?? ''} onChange={(e) => setData('started_at', e.target.value)} />
                                <InputError message={errors?.started_at ?? ''} />
                                <Label>Finished at</Label>
                                <Input type="time" value={data.finished_at ?? ''} onChange={(e) => setData('finished_at', e.target.value)} />
                                <InputError message={errors?.finished_at ?? ''} />
                                <Button>Add intervention</Button>
                            </form>
                        </div>
                    </div>
                    <div className="border-sidebar-border bg-sidebar sticky top-10 flex h-fit w-full flex-col rounded-md border p-4 shadow-xl">
                        <h3>Past interventions</h3>
                        {pastInterventions.length > 0 ? (
                            <ul className="bg-accent flex flex-col gap-3 rounded-md p-2">
                                {pastInterventions.map((intervention: Intervention) => (
                                    <li key={intervention.id} className="">
                                        {intervention.updated_at}
                                        {intervention.type} -{intervention.description}
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
        </>
    );
}
