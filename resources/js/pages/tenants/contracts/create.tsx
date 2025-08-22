import InputError from '@/components/input-error';
import SearchableInput from '@/components/SearchableInput';
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
import { FormEventHandler, useEffect } from 'react';

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
    contractables?: Contractable[];
};

export default function CreateContract({
    contract,
    statuses,
    renewalTypes,
    objects,
}: {
    contract?: Contract;
    statuses: string[];
    renewalTypes: string[];
    objects: [];
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Contract`,
            href: `/contract/${contract?.id ?? 'create'}`,
        },
    ];

    // const [contractables, setContractables] = useState<Contractable[]>([]);

    useEffect(() => {
        const updatedContractables: Contractable[] = [];
        if (objects?.length > 0) {
            objects.map((object: TenantSite | TenantBuilding | TenantFloor | TenantRoom | Asset) =>
                updatedContractables.push({
                    locationId: object.id,
                    locationCode: object.code,
                    locationType: object.asset_category ? 'asset' : object.location_type.slug,
                    name: object.name,
                }),
            );

            setData('contractables', updatedContractables);
        }
    }, [objects]);

    const { data, setData, errors } = useForm<TypeFormData>({
        provider_id: contract?.provider_id ?? null,
        provider_name: contract?.provider.name ?? null,
        name: contract?.name ?? '',
        type: contract?.type ?? '',
        notes: contract?.notes ?? '',
        internal_reference: contract?.internal_reference ?? '',
        provider_reference: contract?.provider_reference ?? '',
        start_date: contract?.start_date ?? '',
        end_date: contract?.end_date ?? '',
        renewal_type: contract?.renewal_type ?? '',
        status: contract?.status ?? '',
        contractables: [],
    });

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
                console.log(error);
            }
        } else {
            try {
                const response = await axios.post(route('api.contracts.store'), data);
                console.log(response);
                if (response.data.status === 'success') {
                    router.visit(route('tenant.contracts.show', response.data.data.id));
                }
            } catch (error) {
                console.log(error);
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

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Contract" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1>{contract?.name ?? 'New contract'}</h1>
                <form onSubmit={submit}>
                    <Label>Name</Label>
                    <Input type="text" onChange={(e) => setData('name', e.target.value)} required value={data.name} />
                    <InputError message={errors.name} />
                    <Label>Type</Label>
                    <Input type="text" onChange={(e) => setData('type', e.target.value)} required value={data.type} />
                    <InputError message={errors.type} />
                    <Label>Notes</Label>
                    <Textarea onChange={(e) => setData('notes', e.target.value)} value={data.notes} />
                    <InputError message={errors.notes} />
                    <Label>Internal reference</Label>
                    <Input type="text" onChange={(e) => setData('internal_reference', e.target.value)} value={data.internal_reference} />
                    <InputError message={errors.internal_reference} />
                    <Label>Provider reference</Label>
                    <Input type="text" onChange={(e) => setData('provider_reference', e.target.value)} value={data.provider_reference} />
                    <InputError message={errors.provider_reference} />
                    <Label className="font-medium">Provider</Label>
                    <SearchableInput<Provider>
                        required
                        searchUrl={route('api.providers.search')}
                        getKey={(provider) => provider.id}
                        displayValue={data.provider_name}
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
                        <Label htmlFor="start_date">Start date</Label>
                        <Input id="start_date" type="date" value={data.start_date} onChange={(e) => setData('start_date', e.target.value)} />
                        <InputError className="mt-2" message={errors.start_date} />
                        <Label htmlFor="end_date">End date</Label>
                        <Input id="end_date" type="date" value={data.end_date} onChange={(e) => setData('end_date', e.target.value)} />
                        <InputError className="mt-2" message={errors.end_date} />
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
            </div>
        </AppLayout>
    );
}
