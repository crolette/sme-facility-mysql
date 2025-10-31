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
    renewalTypes,
    objects,
    contractDurations,
    noticePeriods,
}: {
    contract?: Contract;
    statuses: string[];
    renewalTypes: string[];
    contractDurations: string[];
    noticePeriods: string[];
    objects: [];
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Contract`,
            href: `/contract/${contract?.id ?? 'create'}`,
        },
    ];
    const { showToast } = useToast();

    // const [contractables, setContractables] = useState<Contractable[]>([]);
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
        console.log(data);
        if (contract) {
            try {
                const response = await axios.patch(route('api.contracts.update', contract.id), data);
                console.log(response);
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Contract" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1>{contract?.name ?? 'New contract'}</h1>
                <form onSubmit={submit} className="space-y-4">
                    <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                        <Label>Name</Label>
                        <Input
                            type="text"
                            onChange={(e) => setData('name', e.target.value)}
                            required
                            value={data.name}
                            minLength={4}
                            maxLength={100}
                        />
                        <InputError message={errors?.name ?? ''} />
                        <Label>Type</Label>
                        <Input
                            type="text"
                            onChange={(e) => setData('type', e.target.value)}
                            required
                            value={data.type}
                            minLength={4}
                            maxLength={100}
                        />
                        <InputError message={errors?.type ?? ''} />

                        <div className="flex w-full flex-col gap-4 lg:flex-row">
                            <div className="w-full">
                                <Label>Internal reference</Label>
                                <Input
                                    type="text"
                                    onChange={(e) => setData('internal_reference', e.target.value)}
                                    value={data.internal_reference}
                                    maxLength={50}
                                />
                                <InputError message={errors?.internal_reference ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label>Provider reference</Label>
                                <Input
                                    type="text"
                                    onChange={(e) => setData('provider_reference', e.target.value)}
                                    value={data.provider_reference}
                                    maxLength={50}
                                />
                                <InputError message={errors?.provider_reference ?? ''} />
                            </div>
                        </div>

                        <Label className="font-medium">Provider</Label>
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
                            // className="mb-4"
                        />
                        <Label htmlFor="end_date">Linked to</Label>
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
                            <ul className="flex gap-2 p-2">
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
                            <div className="grid gap-4 lg:grid-cols-2">
                                <div>
                                    <Label htmlFor="start_date">Start date</Label>
                                    <Input
                                        id="start_date"
                                        type="date"
                                        value={data.start_date}
                                        onChange={(e) => setData('start_date', e.target.value)}
                                    />
                                    <InputError className="mt-2" message={errors?.start_date ?? ''} />
                                </div>
                                <div>
                                    <Label htmlFor="end_date">End date</Label>
                                    <Input
                                        id="end_date"
                                        type="date"
                                        value={data.end_date}
                                        onChange={(e) => setData('end_date', e.target.value)}
                                        disabled
                                    />
                                    <p className="text-xs">The end date is automatically calculated based on the contract duration.</p>
                                    <InputError className="mt-2" message={errors?.end_date ?? ''} />
                                </div>
                            </div>
                            <div className="grid gap-4 lg:grid-cols-2">
                                <div>
                                    <Label htmlFor="contract_duration">Contract duration</Label>
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
                                                    Select a duration
                                                </option>
                                                {contractDurations?.map((type, index) => (
                                                    <option value={type} key={index} className="bg-background text-foreground">
                                                        {type}
                                                    </option>
                                                ))}
                                            </>
                                        )}
                                    </select>
                                    <InputError className="mt-2" message={errors?.contract_duration ?? ''} />
                                </div>
                                <div>
                                    <Label htmlFor="notice_period">Notice period</Label>
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
                                                    Select a duration
                                                </option>
                                                {noticePeriods?.map((type, index) => (
                                                    <option value={type} key={index} className="bg-background text-foreground">
                                                        {type}
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
                                <Label htmlFor="renewal_type">Renewal type</Label>
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
                                                Select an option
                                            </option>
                                            {renewalTypes?.map((type, index) => (
                                                <option value={type} key={index} className="bg-background text-foreground">
                                                    {type}
                                                </option>
                                            ))}
                                        </>
                                    )}
                                </select>
                            </div>
                            <div className="w-full">
                                <Label htmlFor="status">Status</Label>
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
                                                Select an option
                                            </option>
                                            {statuses?.map((status, index) => (
                                                <option value={status} key={index} className="bg-background text-foreground">
                                                    {status}
                                                </option>
                                            ))}
                                        </>
                                    )}
                                </select>
                            </div>
                        </div>
                        <div className="mt-4">
                            <Label>Notes</Label>
                            <Textarea onChange={(e) => setData('notes', e.target.value)} value={data.notes} minLength={4} maxLength={250} />
                            <InputError message={errors?.notes ?? ''} />
                        </div>

                        {!contract && (
                            <div id="files" className="mt-4">
                                <Label>Documents</Label>
                                <Button onClick={() => setShowFileModal(!showFileModal)} type="button" className="block">
                                    Add file
                                </Button>
                                {selectedDocuments.length > 0 && (
                                    <ul className="flex gap-4">
                                        {selectedDocuments.map((document, index) => {
                                            const isImage = document.file.type.startsWith('image/');
                                            const isPdf = document.file.type === 'application/pdf';
                                            const fileURL = URL.createObjectURL(document.file);
                                            return (
                                                <li key={index} className="bg-foreground/10 flex w-50 flex-col gap-2 p-6">
                                                    {/* <p>
                                                                            {
                                                                                documentTypes.find((type) => {
                                                                                    return type.id === document.type;
                                                                                })?.label
                                                                            }
                                                                        </p> */}
                                                    {isImage && (
                                                        <img src={fileURL} alt="preview" className="mx-auto h-40 w-40 rounded object-cover" />
                                                    )}
                                                    {isPdf && <BiSolidFilePdf size={'160px'} />}
                                                    <p>{document.name}</p>

                                                    <p>{document.description}</p>
                                                    <Button type="button" variant="destructive" className="" onClick={() => removeDocument(index)}>
                                                        Remove
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
                        <Button type="submit">{contract ? 'Update' : 'Submit'}</Button>
                        <Button
                            variant={'secondary'}
                            onClick={() =>
                                contract ? router.visit(route('tenant.contracts.show', contract.id)) : router.visit(route('tenant.contracts.index'))
                            }
                        >
                            Cancel
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
