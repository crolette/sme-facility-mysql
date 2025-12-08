import Modale from '@/components/Modale';
import ModaleForm from '@/components/ModaleForm';
import SearchableInput from '@/components/SearchableInput';
import { InterventionActionManager } from '@/components/tenant/interventionActionManager';
import { PictureManager } from '@/components/tenant/pictureManager';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import Field from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Pill } from '@/components/ui/pill';
import { Textarea } from '@/components/ui/textarea';
import { usePermissions } from '@/hooks/usePermissions';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { BreadcrumbItem, CentralType, Intervention, InterventionStatus, Provider, User } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Loader, Pencil, Trash2, X } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

export default function ShowIntervention({
    intervention,
    statuses,
    types,
}: {
    intervention: Intervention;
    statuses: InterventionStatus[];
    types: CentralType[];
}) {
    const { hasPermission } = usePermissions();
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${tChoice('interventions.title', 2)}`,
            href: `/interventions/`,
        },
        {
            title: intervention.description,
            href: `/interventions/${intervention}`,
        },
    ];
    const { showToast } = useToast();

    const changeInterventionStatus = async (status: string) => {
        if (status === intervention.status) return;

        try {
            const response = await axios.patch(route('api.interventions.status', intervention.id), { status: status });
            if (response.data.status === 'success') {
                router.visit(route('tenant.interventions.show', intervention.id));
            }
        } catch (error) {
            console.log(error);
        }
    };

    const [activeTab, setActiveTab] = useState('information');
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
        // router.visit(route('tenant.interventions.show', intervention.id));
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

    const [showDeleteInterventionModale, setShowDeleteInterventionModale] = useState(false);
    const [interventionToDelete, setInterventionToDelete] = useState<null | Intervention>(null);
    const deleteIntervention = async () => {
        if (!interventionToDelete) return;

        try {
            setIsProcessing(true);
            const response = await axios.delete(route('api.interventions.destroy', interventionToDelete.id), {});

            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
                setShowDeleteInterventionModale(false);
                setInterventionToDelete(null);
                router.visit(route('tenant.interventions.index'));
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
            setIsProcessing(false);
        }
    };

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
    const [addIntervention, setAddIntervention] = useState<boolean>(false);

    const editIntervention = (id: number) => {
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

    const closeModale = () => {
        setInterventionDataForm(interventionData);
        setAddIntervention(false);
        setIsProcessing(false);
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
                router.visit(route('tenant.interventions.show', intervention.id));
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
            setIsProcessing(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={intervention.description} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <ul className="flex items-center gap-2">
                        <p>{t('common.status.title')} : </p>
                        {statuses.map((status, index) => (
                            <li key={index} className="flex items-center gap-2">
                                <Pill
                                    variant={intervention.status === status ? status : 'disabled'}
                                    onClick={() => changeInterventionStatus(status)}
                                    className={cn(
                                        intervention.status === status ? 'border-2 border-amber-50 font-extrabold uppercase' : 'cursor-pointer',
                                        '',
                                    )}
                                >
                                    {t(`common.status.${status}`)}
                                </Pill>
                                {index !== statuses.length - 1 && <span className="">{' > '}</span>}
                            </li>
                        ))}
                    </ul>
                    <div className="flex items-center gap-4">
                        <Button onClick={() => sendIntervention(intervention.id)} variant={'cta'}>
                            {t('interventions.assign_to')}
                        </Button>
                        {hasPermission('update interventions') && (
                            <Button onClick={() => editIntervention(intervention.id)}>
                                <Pencil />
                            </Button>
                        )}
                        {hasPermission('delete interventions') && (
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
                        )}
                    </div>
                </div>
                <div className="grid max-w-full gap-4 lg:grid-cols-[1fr_6fr]">
                    <SidebarMenuAssetLocation
                        activeTab={activeTab}
                        setActiveTab={setActiveTab}
                        menu={'interventions'}
                        infos={{
                            name: intervention.type,
                            code: intervention.updated_at,
                            status: intervention.status,
                            priority: intervention.priority,
                            levelPath: intervention.interventionable?.location_route ?? '',
                            levelName: intervention.interventionable?.reference_code ?? intervention.interventionable?.name,
                        }}
                    />
                    <div className="overflow-hidden">
                        {activeTab === 'information' && (
                            <>
                                <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                    <h2>{t('common.information')}</h2>
                                    <div className="space-y-2">
                                        <Field label={t('common.type')} text={intervention.type} />
                                        {intervention.planned_at && (
                                            <Field label={t('interventions.planned_at')} date text={intervention.planned_at} />
                                        )}
                                        <Field label={t('common.description')} text={intervention.description} />
                                        {intervention.total_costs && <Field label={t('interventions.total_costs')} text={intervention.total_costs} />}
                                        {intervention.repair_delay && (
                                            <Field label={t('interventions.repair_delay')} text={intervention.repair_delay} />
                                        )}
                                        <div className="flex flex-wrap gap-4">
                                            <Field label={t('common.created_at')} date text={intervention.created_at} />
                                            <Field label={t('common.updated_at')} date text={intervention.updated_at} />
                                        </div>
                                        <Field
                                            label={t('interventions.assigned_to')}
                                            text={
                                                intervention.assignable ? (
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
                                                )
                                            }
                                        />
                                    </div>
                                </div>
                                <InterventionActionManager
                                    interventionId={intervention.id}
                                    actionsChanged={console.log('change')}
                                    closed={closed ? true : intervention.status === 'completed' || intervention.status === 'cancelled' ? true : false}
                                />
                            </>
                        )}
                        {activeTab === 'pictures' && (
                            <PictureManager
                                itemCodeId={intervention.id}
                                getPicturesUrl={`api.interventions.pictures`}
                                uploadRoute={`api.interventions.pictures.post`}
                                deleteRoute={`api.pictures.delete`}
                                showRoute={'api.pictures.show'}
                            />
                        )}
                    </div>
                </div>
            </div>
            <Modale
                title={t('actions.delete-type', { type: tChoice('interventions.title', 1) })}
                message={t('interventions.delete_description')}
                isOpen={showDeleteInterventionModale}
                isProcessing={isProcessing}
                onConfirm={deleteIntervention}
                onCancel={() => {
                    setInterventionToDelete(null);
                    setShowDeleteInterventionModale(false);
                }}
            />
            {addIntervention && (
                <ModaleForm title={t('actions.add-type', { type: tChoice('interventions.title', 1) })}>
                    {isProcessing && (
                        <div className="flex flex-col items-center gap-4">
                            <Loader size={48} className="animate-pulse" />
                            <p className="mx-auto animate-pulse text-3xl font-bold">{t('actions.processing')}</p>
                            <p className="mx-auto">{t('actions.being-created-type', { type: tChoice('interventions.title', 1) })}</p>
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
                            <Label>{t('interventions.priority.title')}</Label>
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
                                <option value="">{t('actions.select-type', { type: t('interventions.priority.title') })}</option>
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
                                            <ul className="">
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
                                    <Label htmlFor="search">{t('actions.search')}</Label>

                                    <div className="flex items-center gap-4">
                                        <Input
                                            id="search"
                                            type="text"
                                            value={externalProvidersQuery ?? ''}
                                            onChange={(e) => setExternalProvidersQuery(e.target.value)}
                                        />
                                        <Button type="submit">{t('actions.search')}</Button>
                                    </div>
                                </form>
                                {externalProviders &&
                                    externalProviders.length > 0 &&
                                    externalProviders?.map((provider, i) => (
                                        <ul>
                                            <li
                                                key={i}
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
                                                {provider.users && provider.users.length > 0 && (
                                                    <ul className="mt-1 font-normal">
                                                        {provider.users.map((user) => (
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
                                                        ))}
                                                    </ul>
                                                )}
                                            </li>
                                        </ul>
                                    ))}
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
        </AppLayout>
    );
}
