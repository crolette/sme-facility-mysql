import InputError from '@/components/input-error';
import SearchableInput from '@/components/SearchableInput';
import FileManager from '@/components/tenant/FileManager';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Asset, BreadcrumbItem, Contract, Provider, TenantBuilding, TenantFloor, TenantRoom, TenantSite } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { XIcon } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

interface Contractable {
    locationId: number;
    locationType: string;
    locationCode: string;
    name: string;
}

type TypeFormData = {
    provider_id: number;
    provider_name: string;
    name: string;
    type: string;
    notes: string;
    internal_reference: string;
    provider_reference: string;
    start_date: string;
    end_date: string;
    renewal_type: string;
    status: string;
    contract_duration: string;
    notice_period: string;
    contractables?: Contractable[];
    files: {
        file: File;
        name: string;
        description: string;
        typeId: null | number;
        typeSlug: string;
    }[];
};

export default function CreateUpdateContract({
    contract,
    statuses,
    contractTypes,
    renewalTypes,
    objects,
    contractDurations,
    noticePeriods,
}: {
    contract?: Contract;
    statuses: string[];
    contractTypes: string[];
    renewalTypes: string[];
    contractDurations: string[];
    noticePeriods: string[];
    objects: [];
}) {
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Contract`,
            href: `/contract/${contract?.id ?? 'create'}`,
        },
    ];
    const { showToast } = useToast();

    useEffect(() => {
        const updatedContractables: Contractable[] = [];
        if (objects?.length > 0) {
            objects.map((object: TenantSite | TenantBuilding | TenantFloor | TenantRoom | Asset) =>
                updatedContractables.push({
                    locationId: object.id,
                    locationCode: object.code,
                    locationType: object.asset_category ? 'asset' : object.location_type.level,
                    name: object.name,
                }),
            );

            setData('contractables', updatedContractables);
        }
    }, [objects]);

    const [selectedDocuments, setSelectedDocuments] = useState<TypeFormData['files']>([]);
    const { data, setData, setError } = useForm<TypeFormData>({
        provider_id: contract?.provider_id ?? null,
        provider_name: contract?.provider.name ?? null,
        name: contract?.name ?? '',
        type: contract?.type ?? '',
        notes: contract?.notes ?? '',
        internal_reference: contract?.internal_reference ?? '',
        provider_reference: contract?.provider_reference ?? '',
        start_date: contract?.start_date ?? new Date().toISOString().split('T')[0],
        end_date: contract?.end_date ?? '',
        renewal_type: contract?.renewal_type ?? '',
        status: contract?.status ?? '',
        contract_duration: contract?.contract_duration ?? '',
        notice_period: contract?.notice_period ?? '',
        contractables: [],
        files: selectedDocuments,
    });

    const [errors, setErrors] = useState<TypeFormData>();

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        if (contract) {
            try {
                const response = await axios.patch(route('api.contracts.update', contract.id), data);
                if (response.data.status === 'success') {
                    router.visit(route('tenant.contracts.show', contract.id));
                }
            } catch (error) {
                showToast(error.response.data.message, error.response.data.status);
                setErrors(error.response.data.errors);
            }
        } else {
            try {
                const response = await axios.post(route('api.contracts.store'), data, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                if (response.data.status === 'success') {
                    router.visit(route('tenant.contracts.show', response.data.data.id));
                }
            } catch (error) {
                showToast(error.response.data.message, error.response.data.status);
                setErrors(error.response.data.errors);
            }
        }
    };

    const handleAddAssetOrLocation = (location) => {
        const updatedContractables = [...data.contractables];

        if (updatedContractables.findIndex((contract) => contract.locationId === location.id) === -1) {
            updatedContractables.push({
                locationId: location.id,
                locationCode: location.code,
                locationType: location.type,
                name: location.name,
            });

            setData('contractables', updatedContractables);
        }
    };

    const handleRemoveAssetOrLocation = (location) => {
        const updatedContractables = [...data.contractables];

        setData(
            'contractables',
            updatedContractables.filter((contractable) => contractable.locationId !== location.locationId),
        );
    };

    const removeDocument = (index: number) => {
        const files = data.files.filter((file, indexFile) => {
            return index !== indexFile ? file : null;
        });
        setSelectedDocuments(() => {
            setData('files', files);
            return files;
        });
    };

    const [showFileModal, setShowFileModal] = useState(false);

    useEffect(() => {
        const date = new Date(data.start_date);

        if (data.contract_duration) {
            switch (data.contract_duration) {
                case '1_month':
                    date.setMonth(date.getMonth() + 1);
                    break;
                case '6_months':
                    date.setMonth(date.getMonth() + 6);
                    break;
                case '1_year':
                    date.setMonth(date.getMonth() + 12);
                    break;
                case '2_years':
                    date.setMonth(date.getMonth() + 24);
                    break;
            }
            setData('end_date', date.toISOString().split('T')[0]);
        }
    }, [data.contract_duration, data.start_date]);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head
                title={
                    contract
                        ? t('actions.edit-type', { type: tChoice('contracts.title', 1) })
                        : t('actions.create-type', { type: tChoice('contracts.title', 1) })
                }
            />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1>{contract?.name ?? 'New contract'}</h1>
                <form onSubmit={submit} className="space-y-4">
                    <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                        <div className="flex w-full flex-col gap-4 lg:flex-row">
                            <div className="w-full">
                                <Label>{t('common.name')}</Label>
                                <Input
                                    type="text"
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                    value={data.name}
                                    minLength={4}
                                    maxLength={100}
                                />
                                <InputError message={errors?.name ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label htmlFor="type">{t('common.type')}</Label>
                                <select
                                    name="type"
                                    onChange={(e) => setData('type', e.target.value)}
                                    id=""
                                    required
                                    value={data.type}
                                    className={cn(
                                        'border-input placeholder:text-muted-foreground mt-1 flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                    )}
                                >
                                    {contractTypes && contractTypes.length > 0 && (
                                        <>
                                            <option value="" disabled className="bg-background text-foreground">
                                                {t('actions.select-type', { type: t('common.type') })}
                                            </option>
                                            {contractTypes?.map((type, index) => (
                                                <option value={type} key={index} className="bg-background text-foreground">
                                                    {t(`contracts.type.${type}`)}
                                                </option>
                                            ))}
                                        </>
                                    )}
                                </select>

                                <InputError className="mt-2" message={errors?.type ?? ''} />
                            </div>
                        </div>

                        <div className="flex w-full flex-col gap-4 lg:flex-row">
                            <div className="w-full">
                                <Label>{t('contracts.internal_ref')}</Label>
                                <Input
                                    type="text"
                                    onChange={(e) => setData('internal_reference', e.target.value)}
                                    value={data.internal_reference}
                                    maxLength={50}
                                />
                                <InputError message={errors?.internal_reference ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label>{t('contracts.provider_ref')}</Label>
                                <Input
                                    type="text"
                                    onChange={(e) => setData('provider_reference', e.target.value)}
                                    value={data.provider_reference}
                                    maxLength={50}
                                />
                                <InputError message={errors?.provider_reference ?? ''} />
                            </div>
                        </div>

                        <Label className="font-medium">{tChoice('providers.title', 1)}</Label>
                        <SearchableInput<Provider>
                            required
                            multiple={false}
                            searchUrl={route('api.providers.search')}
                            getKey={(provider) => provider.id}
                            displayValue={data.provider_name}
                            onDelete={() => {
                                setData('provider_id', '');
                                setData('provider_name', '');
                            }}
                            getDisplayText={(provider) => provider.name}
                            onSelect={(provider) => {
                                setData('provider_id', provider.id);
                                setData('provider_name', provider.name);
                            }}
                            placeholder="Search provider..."
                        />
                        <Label htmlFor="end_date">{t('contracts.linked_to')}</Label>
                        <SearchableInput
                            multiple={false}
                            searchUrl={route('api.search.all')}
                            selectedItems={data.contractables ?? []}
                            getDisplayText={(location) => location.name}
                            getKey={(location) => location.reference_code}
                            onSelect={(location) => {
                                handleAddAssetOrLocation(location);
                            }}
                            placeholder="Search asset or location..."
                        />
                        {data.contractables && (
                            <ul className="flex flex-wrap gap-2 p-2">
                                {data.contractables.map((contractable) => (
                                    <li key={contractable.locationId} className="rounded-md bg-slate-600 px-2 py-1 text-sm">
                                        <div className="flex items-center gap-2">
                                            <p>{contractable.name}</p>
                                            <XIcon
                                                size={16}
                                                className="cursor-pointer hover:text-red-500"
                                                onClick={() => handleRemoveAssetOrLocation(contractable)}
                                            />
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        )}
                        <div className="mt-4 flex w-full flex-col gap-4">
                            <div className="grid grid-cols-2 gap-4">
                                <div>
                                    <Label htmlFor="start_date">{t('contracts.start_date')}</Label>
                                    <Input
                                        id="start_date"
                                        type="date"
                                        value={data.start_date}
                                        onChange={(e) => setData('start_date', e.target.value)}
                                    />
                                    <InputError className="mt-2" message={errors?.start_date ?? ''} />
                                </div>
                                <div>
                                    <Label htmlFor="end_date">{t('contracts.end_date')}</Label>
                                    <Input
                                        id="end_date"
                                        type="date"
                                        value={data.end_date}
                                        onChange={(e) => setData('end_date', e.target.value)}
                                        disabled
                                    />
                                    <p className="text-xs">{t('contracts.end_date_description')}</p>
                                    <InputError className="mt-2" message={errors?.end_date ?? ''} />
                                </div>
                            </div>
                            <div className="grid gap-4 lg:grid-cols-2">
                                <div>
                                    <Label htmlFor="contract_duration">{t('contracts.duration_contract')}</Label>
                                    <select
                                        name="contract_duration"
                                        onChange={(e) => setData('contract_duration', e.target.value)}
                                        id=""
                                        required
                                        value={data.contract_duration}
                                        className={cn(
                                            'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                            'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                            'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                        )}
                                    >
                                        {contractDurations && contractDurations.length > 0 && (
                                            <>
                                                <option value="" disabled className="bg-background text-foreground">
                                                    {t('actions.select-type', { type: t('contracts.duration_contract') })}
                                                </option>
                                                {contractDurations?.map((duration, index) => (
                                                    <option value={duration} key={index} className="bg-background text-foreground">
                                                        {t(`contracts.duration.${duration}`)}
                                                    </option>
                                                ))}
                                            </>
                                        )}
                                    </select>
                                    <InputError className="mt-2" message={errors?.contract_duration ?? ''} />
                                </div>
                                <div>
                                    <Label htmlFor="notice_period">{t('contracts.notice_period')}</Label>
                                    <select
                                        name="notice_period"
                                        onChange={(e) => setData('notice_period', e.target.value)}
                                        id=""
                                        defaultValue={''}
                                        value={data.notice_period}
                                        className={cn(
                                            'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                            'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                            'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                        )}
                                    >
                                        {noticePeriods && noticePeriods.length > 0 && (
                                            <>
                                                <option value="" disabled className="bg-background text-foreground">
                                                    {t('actions.select-type', { type: t('contracts.notice_period') })}
                                                </option>
                                                {noticePeriods?.map((noticePeriod, index) => (
                                                    <option value={noticePeriod} key={index} className="bg-background text-foreground">
                                                        {t(`contracts.notice_period.${noticePeriod}`)}
                                                    </option>
                                                ))}
                                            </>
                                        )}
                                    </select>

                                    <InputError className="mt-2" message={errors?.notice_period ?? ''} />
                                </div>
                            </div>
                        </div>
                        <div className="mt-4 flex w-full flex-col gap-4 lg:flex-row">
                            <div className="w-full">
                                <Label htmlFor="renewal_type">{t(`contracts.renewal_type`)}</Label>
                                <select
                                    name="renewal_type"
                                    onChange={(e) => setData('renewal_type', e.target.value)}
                                    id=""
                                    required
                                    value={data.renewal_type}
                                    className={cn(
                                        'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                    )}
                                >
                                    {renewalTypes && renewalTypes.length > 0 && (
                                        <>
                                            <option value="" disabled className="bg-background text-foreground">
                                                {t('actions.select-type', { type: t('contracts.renewal_type.title') })}
                                            </option>
                                            {renewalTypes?.map((renewalType, index) => (
                                                <option value={renewalType} key={index} className="bg-background text-foreground">
                                                    {t(`contracts.renewal_type.${renewalType}`)}
                                                </option>
                                            ))}
                                        </>
                                    )}
                                </select>
                            </div>
                            <div className="w-full">
                                <Label htmlFor="status">{t('common.status')}</Label>
                                <select
                                    name="status"
                                    value={data.status}
                                    required
                                    onChange={(e) => setData('status', e.target.value)}
                                    id=""
                                    className={cn(
                                        'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                    )}
                                >
                                    {statuses && statuses.length > 0 && (
                                        <>
                                            <option value="" disabled className="bg-background text-foreground">
                                                {t('actions.select-type', { type: t('common.status') })}
                                            </option>
                                            {statuses?.map((status, index) => (
                                                <option value={status} key={index} className="bg-background text-foreground">
                                                    {t(`contracts.status.${status}`)}
                                                </option>
                                            ))}
                                        </>
                                    )}
                                </select>
                            </div>
                        </div>
                        <div className="mt-4">
                            <Label>{t('common.notes')}</Label>
                            <Textarea
                                onChange={(e) => setData('notes', e.target.value)}
                                value={data.notes}
                                minLength={4}
                                maxLength={250}
                                placeholder={t('common.notes_placeholder')}
                            />
                            <InputError message={errors?.notes ?? ''} />
                        </div>

                        {!contract && (
                            <div id="files" className="mt-4">
                                <Label>{tChoice('documents.title', 2)}</Label>
                                <Button onClick={() => setShowFileModal(!showFileModal)} type="button" className="block">
                                    {t('actions.add-type', { type: tChoice('documents.title', 1) })}
                                </Button>
                                {selectedDocuments.length > 0 && (
                                    <ul className="flex gap-4">
                                        {selectedDocuments.map((document, index) => {
                                            const isImage = document.file.type.startsWith('image/');
                                            const isPdf = document.file.type === 'application/pdf';
                                            const fileURL = URL.createObjectURL(document.file);
                                            return (
                                                <li key={index} className="bg-foreground/10 flex w-50 flex-col gap-2 p-6">
                                                    {isImage && (
                                                        <img src={fileURL} alt="preview" className="mx-auto h-40 w-40 rounded object-cover" />
                                                    )}
                                                    {isPdf && <BiSolidFilePdf size={'160px'} />}
                                                    <p>{document.name}</p>

                                                    <p>{document.description}</p>
                                                    <Button type="button" variant="destructive" className="" onClick={() => removeDocument(index)}>
                                                        {t('actions.remove')}
                                                    </Button>
                                                </li>
                                            );
                                        })}
                                    </ul>
                                )}
                            </div>
                        )}
                    </div>
                    <div className="flex gap-4">
                        <Button type="submit">{contract ? t('actions.update') : t('actions.submit')}</Button>
                        <Button
                            variant={'secondary'}
                            onClick={() =>
                                contract ? router.visit(route('tenant.contracts.show', contract.id)) : router.visit(route('tenant.contracts.index'))
                            }
                        >
                            {t('actions.cancel')}
                        </Button>
                    </div>
                </form>
                <FileManager
                    documents={selectedDocuments}
                    showModal={showFileModal}
                    onDocumentsChange={(docs) => {
                        setSelectedDocuments(docs);
                        setData('files', docs);
                    }}
                    onToggleModal={() => setShowFileModal(!showFileModal)}
                />
            </div>
        </AppLayout>
    );
}
