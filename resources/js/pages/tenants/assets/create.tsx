/* eslint-disable @typescript-eslint/no-explicit-any */
import InputError from '@/components/input-error';
import SearchableInput from '@/components/SearchableInput';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Asset, AssetCategory, CentralType, Provider, User, type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { MinusCircleIcon, PlusCircleIcon } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

interface ProviderForm {
    id: number;
    name: string;
}

interface Contract {
    provider_id: number | null;
    provider_name: string | null;
    name: string | null;
    type: string | null;
    notes: string | null;
    internal_reference: string | null;
    provider_reference: string | null;
    start_date: string | null;
    end_date: string | null;
    contract_duration: string;
    notice_period: string;
    renewal_type: string;
    status: string;
}

type TypeFormData = {
    q: string;
    name: string;
    is_mobile?: boolean;
    need_qr_code?: boolean;
    surface: null | number;
    depreciable: boolean;
    depreciation_start_date: null | string;
    depreciation_end_date: null | string;
    depreciation_duration: null | number;
    residual_value: null | number;
    description: string;
    locationId: number;
    locationReference: string;
    locationType: string;
    locationName: string;
    categoryId: string | number;
    purchase_date: string;
    purchase_cost: number | null;
    under_warranty: boolean;
    end_warranty_date: string;
    brand: string;
    model: string;
    serial_number: string;
    maintenance_manager_id: number | null;
    maintenance_manager_name: string;
    need_maintenance: boolean;
    maintenance_frequency: string | null;
    next_maintenance_date: string | null;
    last_maintenance_date: string | null;
    contracts: Contract[];
    files: {
        file: File;
        name: string;
        description: string;
        typeId: null | number;
        typeSlug: string;
    }[];
    pictures: File[];
    providers: ProviderForm[];
};

type SearchedLocation = {
    id: number;
    type: string;
    name: string;
    reference_code: string;
    code: string;
};

export default function CreateAsset({
    asset,
    categories,
    documentTypes,
    frequencies,
    statuses,
    renewalTypes,
    contractDurations,
    noticePeriods,
}: {
    asset?: Asset;
    categories?: AssetCategory[];
    documentTypes: CentralType[];
    frequencies: string[];
    statuses: string[];
    renewalTypes: string[];
    contractDurations?: string[];
    noticePeriods: string[];
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create asset`,
            href: '/assets/create',
        },
    ];
    const [errors, setErrors] = useState<TypeFormData>();
    const [selectedDocuments, setSelectedDocuments] = useState<TypeFormData['files']>([]);
    const { data, setData } = useForm<TypeFormData>({
        q: '',
        name: asset?.maintainable.name ?? '',
        description: asset?.maintainable.description ?? '',
        surface: asset?.surface ?? null,
        depreciable: asset?.depreciable ?? false,
        depreciation_start_date: asset?.depreciation_start_date ?? null,
        depreciation_end_date: asset?.depreciation_end_date ?? null,
        depreciation_duration: asset?.depreciation_duration ?? null,
        contract_end_date: asset?.contract_end_date ?? null,
        residual_value: asset?.residual_value ?? null,
        locationId: asset?.location_id ?? '',
        locationReference: asset?.is_mobile ? '' : (asset?.location.reference_code ?? ''),
        locationType: asset?.is_mobile ? 'user' : (asset?.location.location_type.level ?? ''),
        locationName: asset?.is_mobile ? asset.location.full_name : (asset?.location.maintainable.name ?? ''),
        categoryId: asset?.asset_category.id ?? '',
        maintenance_manager_id: asset?.maintainable?.maintenance_manager_id ?? null,
        maintenance_manager_name: asset?.maintainable?.manager?.full_name ?? '',
        purchase_date: asset?.maintainable.purchase_date ?? '',
        purchase_cost: asset?.maintainable.purchase_cost ?? null,
        under_warranty: asset?.maintainable.under_warranty ?? false,
        end_warranty_date: asset?.maintainable.end_warranty_date ?? '',
        is_mobile: asset?.is_mobile ?? false,
        need_qr_code: false,
        need_maintenance: asset?.maintainable.need_maintenance ?? false,
        maintenance_frequency: asset?.maintainable.maintenance_frequency ?? '',
        next_maintenance_date: asset?.maintainable.next_maintenance_date ?? '',
        last_maintenance_date: asset?.maintainable.last_maintenance_date ?? '',
        brand: asset?.maintainable.brand ?? '',
        model: asset?.maintainable.model ?? '',
        serial_number: asset?.maintainable.serial_number ?? '',
        files: selectedDocuments,
        pictures: [],
        contracts: [],
        providers: asset?.maintainable.providers ?? [],
    });

    const [listIsOpen, setListIsOpen] = useState(false);
    const [isSearching, setIsSearching] = useState(false);
    const [locations, setLocations] = useState<SearchedLocation[]>();
    const [debouncedSearch, setDebouncedSearch] = useState(data.q);
    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedSearch(data.q);
        }, 500);

        return () => {
            clearTimeout(handler);
        };
    }, [data.q]);

    useEffect(() => {
        if (debouncedSearch.length < 2) {
            setLocations([]);
        }
        if (debouncedSearch.length >= 2) {
            setIsSearching(true);
            setListIsOpen(true);
            const fetchData = async () => {
                try {
                    const response = await axios.get(route('api.locations', { q: debouncedSearch }));
                    setLocations(response.data.data);
                    setIsSearching(false);
                    setListIsOpen(true);
                } catch (error) {
                    console.error('Erreur lors de la recherche :', error);
                }
            };

            if (debouncedSearch) {
                fetchData();
            }
        }
    }, [debouncedSearch]);

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();

        if (asset) {
            try {
                const response = await axios.patch(route(`api.assets.update`, asset.reference_code), data);

                if (response.data.status === 'success') {
                    router.visit(route(`tenant.assets.show`, response.data.data.reference_code), {
                        preserveScroll: false,
                    });
                }
            } catch (error) {
                console.log(error);
                setErrors(error.response.data.errors);
            }
        } else {
            try {
                const response = await axios.post(route(`api.assets.store`), data, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                console.log(response.data);
                if (response.data.status === 'success') {
                    router.visit(route(`tenant.assets.index`), {
                        preserveScroll: false,
                    });
                }
            } catch (error) {
                console.log(error);
                setErrors(error.response.data.errors);
            }
        }
    };

    const setSelectedLocation = (location: SearchedLocation) => {
        if (!location) {
            return;
        }

        setData('locationReference', location.reference_code);
        setData('locationId', location.id);
        setData('locationType', location.type);
        setData('locationName', location.name);
        setLocations([]);
        setListIsOpen(!listIsOpen);
        setData('q', '');
    };

    const minEndDateWarranty = new Date().toISOString().split('T')[0];

    const [showFileModal, setShowFileModal] = useState(false);
    const [newFileName, setNewFileName] = useState('');
    const [newFileDescription, setNewFileDescription] = useState('');
    const [newFile, setNewFile] = useState<File | null>(null);
    const [newDocumentType, setNewDocumentType] = useState<number | null>(null);

    const addFile: FormEventHandler = (e) => {
        e.preventDefault();

        if (!newFile) return;

        const typeSlug = documentTypes.find((type) => {
            return type.id === newDocumentType;
        })?.slug;

        const fileToAdd: TypeFormData['files'][number] = {
            file: newFile,
            name: newFileName,
            description: newFileDescription,
            typeId: newDocumentType,
            typeSlug: typeSlug ?? '',
        };

        setSelectedDocuments((prev) => {
            const updated = [...prev, fileToAdd];
            setData('files', updated);
            return updated;
        });

        setShowFileModal(!showFileModal);
    };

    const closeFileModal = () => {
        setNewFileName('');
        setNewFileDescription('');
        setShowFileModal(!showFileModal);
        setNewDocumentType(null);
        setNewFile(null);
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

    const addFileModalForm = () => {
        return (
            <div className="bg-background/50 absolute inset-0 z-50">
                <div className="bg-background/20 flex h-dvh items-center justify-center">
                    <div className="bg-background flex items-center justify-center p-4">
                        <div className="flex flex-col gap-2">
                            <form onSubmit={addFile} className="space-y-2">
                                <p className="text-center">Add new document</p>
                                <select
                                    name="documentType"
                                    required
                                    value={newDocumentType ?? ''}
                                    onChange={(e) => setNewDocumentType(parseInt(e.target.value))}
                                    id=""
                                    className={cn(
                                        'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                    )}
                                >
                                    {documentTypes && documentTypes.length > 0 && (
                                        <>
                                            <option value="" disabled className="bg-background text-foreground">
                                                Select an option
                                            </option>
                                            {documentTypes?.map((documentType) => (
                                                <option value={documentType.id} key={documentType.id} className="bg-background text-foreground">
                                                    {documentType.label}
                                                </option>
                                            ))}
                                        </>
                                    )}
                                </select>
                                <Input
                                    type="file"
                                    name=""
                                    id=""
                                    onChange={(e) => setNewFile(e.target.files ? e.target.files[0] : null)}
                                    required
                                    accept="image/png, image/jpeg, image/jpg, .pdf"
                                />

                                <Input
                                    type="text"
                                    name="name"
                                    required
                                    placeholder="Document name"
                                    onChange={(e) => setNewFileName(e.target.value)}
                                />
                                <p className="text-border text-xs">Servira à la sauvegarde du nom du fichier</p>
                                <Input
                                    type="text"
                                    name="description"
                                    id="description"
                                    required
                                    minLength={10}
                                    maxLength={250}
                                    placeholder="Document description"
                                    onChange={(e) => setNewFileDescription(e.target.value)}
                                />
                                <div className="flex justify-between">
                                    <Button>Submit</Button>
                                    <Button type="button" onClick={closeFileModal} variant={'outline'}>
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        );
    };

    const [countContracts, setCountContracts] = useState(0);

    const handleChangeContracts = (index: number, field: keyof Contract, value: any) => {
        setData((prev) => {
            const updatedContracts = [...prev.contracts];
            updatedContracts[index] = {
                ...updatedContracts[index],
                [field]: value,
            };
            return { ...prev, contracts: updatedContracts };
        });
    };

    const handleRemoveContract = (index: number) => {
        setData((prev) => {
            const updatedContracts = prev.contracts.filter((_, i) => i !== index);

            return { ...prev, contracts: updatedContracts };
        });

        setCountContracts((prev) => prev - 1);
    };

    useEffect(() => {
        console.log(data.depreciation_duration, data.depreciation_end_date);
        console.log(new Date().toLocaleString());
        if (data.depreciation_duration && data.depreciation_duration > 0 && data.depreciation_start_date !== null) {
            const date = new Date(data.depreciation_start_date); // Convertit la chaîne en objet Date
            date.setFullYear(date.getFullYear() + data.depreciation_duration); // Ajoute les années
            setData('depreciation_end_date', date.toISOString().split('T')[0]);
        }
    }, [data.depreciation_duration]);

    console.log(data);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Create asset`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {asset && (
                    <div>
                        <p>Asset Reference: {asset.reference_code}</p>
                        <p>Asset Code: {asset.code} </p>
                        {asset?.is_mobile ? (
                            <p>Asset attached to : {asset.location.full_name}</p>
                        ) : (
                            <p>
                                Asset attached to : {asset.location.maintainable.name} - {asset.location.location_type.label}
                            </p>
                        )}
                    </div>
                )}
                <form onSubmit={submit}>
                    <div>
                        <Label htmlFor="is_mobile">Mobile asset ?</Label>
                        <Checkbox
                            id="is_mobile"
                            name="is_mobile"
                            checked={data.is_mobile ?? true}
                            onClick={() =>
                                setData((prev) => ({
                                    ...prev,
                                    is_mobile: !data.is_mobile,
                                    locationId: '',
                                    locationName: '',
                                    locationReference: '',
                                    locationType: '',
                                }))
                            }
                        />
                        <InputError className="mt-2" message={errors?.is_mobile ?? ''} />
                    </div>
                    {data.is_mobile ? (
                        <>
                            <div>
                                <label className="mb-2 block text-sm font-medium">User of the mobile asset</label>
                                <SearchableInput<User>
                                    searchUrl={route('api.users.search')}
                                    searchParams={{ interns: 1 }}
                                    displayValue={data.locationName}
                                    getDisplayText={(user) => user.full_name}
                                    getKey={(user) => user.id}
                                    onSelect={(user) => {
                                        setData('locationId', user.id);
                                        setData('locationName', user.full_name);
                                        setData('locationType', 'user');
                                    }}
                                    placeholder="Search user..."
                                    className="mb-4"
                                />
                            </div>
                        </>
                    ) : (
                        <>
                            <Label htmlFor="search">Search</Label>
                            <div className="relative">
                                <Input
                                    type="search"
                                    value={data.q}
                                    onChange={(e) => setData('q', e.target.value)}
                                    placeholder="Search by code or name"
                                />
                                <ul className="bg-background absolute z-10 flex w-full flex-col border" aria-autocomplete="list" role="listbox">
                                    {isSearching && (
                                        <li value="0" key="" className="">
                                            Searching...
                                        </li>
                                    )}
                                    {listIsOpen &&
                                        locations &&
                                        locations.length > 0 &&
                                        locations?.map((location) => (
                                            <li
                                                role="option"
                                                value={location.reference_code}
                                                key={location.reference_code}
                                                onClick={() => setSelectedLocation(location)}
                                                className="hover:bg-foreground hover:text-background cursor-pointer p-2 text-sm"
                                            >
                                                {location.name + ' (' + location.reference_code + ')'}
                                            </li>
                                        ))}
                                    {listIsOpen && locations && locations.length == 0 && (
                                        <>
                                            <li value="0" key="">
                                                No results
                                            </li>
                                        </>
                                    )}
                                </ul>
                            </div>
                            {/* {data.locationName && ( */}
                            <>
                                <Label htmlFor="locationType">Level</Label>
                                <Input
                                    required
                                    type="text"
                                    disabled
                                    value={data.locationName ? data.locationName + ' - ' + data.locationReference : 'No location selected'}
                                />
                                <InputError className="mt-2" message={errors?.locationType ?? ''} />
                            </>
                            {/* )} */}
                        </>
                    )}

                    <Label htmlFor="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autoFocus
                        maxLength={100}
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder="Asset name"
                    />
                    <InputError className="mt-2" message={errors?.name ?? ''} />

                    {!asset && (
                        <div>
                            <Label htmlFor="need_qr_code">Need QR Code ?</Label>
                            <Checkbox
                                id="need_qr_code"
                                name="need_qr_code"
                                checked={data.need_qr_code ?? true}
                                onClick={() => setData('need_qr_code', !data.need_qr_code)}
                            />
                            <InputError className="mt-2" message={errors?.need_qr_code ?? ''} />
                        </div>
                    )}

                    <Label htmlFor="category">Category</Label>
                    <select
                        name="category"
                        required
                        value={data.categoryId === '' ? 0 : data.categoryId}
                        onChange={(e) => setData('categoryId', e.target.value)}
                        id="category"
                        className={cn(
                            'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                            'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                            'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                        )}
                    >
                        {categories && categories.length > 0 && (
                            <>
                                <option value="0" disabled className="bg-background text-foreground">
                                    Select an option
                                </option>
                                {categories?.map((category) => (
                                    <option value={category.id} key={category.id} className="bg-background text-foreground">
                                        {category.label}
                                    </option>
                                ))}
                            </>
                        )}
                    </select>
                    <InputError className="mt-2" message={errors?.categoryId ?? ''} />

                    <Label htmlFor="description">Description</Label>
                    <Input
                        id="description"
                        type="text"
                        maxLength={255}
                        required
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder="Asset description"
                    />
                    <InputError className="mt-2" message={errors?.description ?? ''} />

                    <Label htmlFor="surface">Surface</Label>
                    <Input
                        id="surface"
                        type="number"
                        min={0}
                        step="0.01"
                        value={data.surface ?? ''}
                        placeholder="Asset surface"
                        onChange={(e) => setData('surface', parseFloat(e.target.value))}
                    />
                    <InputError className="mt-2" message={errors?.surface ?? ''} />
                    <div className="flex flex-col gap-4 md:flex-row">
                        <div className="w-full">
                            <Label htmlFor="brand">Brand</Label>
                            <Input
                                id="brand"
                                type="text"
                                maxLength={100}
                                value={data.brand}
                                onChange={(e) => setData('brand', e.target.value)}
                                placeholder="Asset brand"
                            />
                            <InputError className="mt-2" message={errors?.brand ?? ''} />
                        </div>
                        <div className="w-full">
                            <Label htmlFor="model">Model</Label>
                            <Input
                                id="model"
                                type="text"
                                maxLength={100}
                                value={data.model}
                                onChange={(e) => setData('model', e.target.value)}
                                placeholder="Asset model"
                            />
                            <InputError className="mt-2" message={errors?.model ?? ''} />
                        </div>
                        <div className="w-full">
                            <Label htmlFor="serial_number">Serial number</Label>
                            <Input
                                id="serial_number"
                                type="text"
                                maxLength={50}
                                value={data.serial_number}
                                onChange={(e) => setData('serial_number', e.target.value)}
                                placeholder="Asset serial number"
                            />
                            <InputError className="mt-2" message={errors?.serial_number ?? ''} />
                        </div>
                    </div>

                    <div>
                        <Label htmlFor="purchase_date">Date of purchase</Label>
                        <Input
                            id="purchase_date"
                            type="date"
                            value={data.purchase_date}
                            max={minEndDateWarranty}
                            onChange={(e) => setData('purchase_date', e.target.value)}
                            placeholder="Date of purchase"
                        />
                        <InputError className="mt-2" message={errors?.purchase_date ?? ''} />
                    </div>
                    <Label htmlFor="purchase_cost">Purchase cost</Label>
                    <Input
                        id="purchase_cost"
                        type="number"
                        min={0}
                        step="0.01"
                        value={data.purchase_cost ?? ''}
                        onChange={(e) => setData('purchase_cost', parseFloat(e.target.value))}
                        placeholder="Purchase cost (max. 2 decimals) : 4236.36"
                    />
                    <InputError className="mt-2" message={errors?.purchase_cost ?? ''} />

                    {/* Depreciation */}
                    <div>
                        <Label htmlFor="depreciable">depreciable ?</Label>
                        <Checkbox
                            id="depreciable"
                            name="depreciable"
                            checked={data.depreciable}
                            onClick={() => setData('depreciable', !data.depreciable)}
                        />
                    </div>
                    <InputError className="mt-2" message={errors?.depreciable ?? ''} />
                    {data.depreciable && (
                        <div className="flex flex-col gap-4 md:flex-row">
                            <div className="w-full">
                                <Label htmlFor="depreciation_start_date">Depreciation start date</Label>
                                <Input
                                    id="depreciation_start_date"
                                    type="date"
                                    value={data.depreciation_start_date ?? ''}
                                    onChange={(e) => setData('depreciation_start_date', e.target.value)}
                                    placeholder="Depreciation start date"
                                />
                                <InputError className="mt-2" message={errors?.depreciation_start_date ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label htmlFor="depreciation_duration">Depreciation duration (in years)</Label>
                                <Input
                                    id="depreciation_duration"
                                    type="number"
                                    min={1}
                                    step="1"
                                    value={data.depreciation_duration ?? ''}
                                    placeholder="Asset depreciation_duration"
                                    onChange={(e) => setData('depreciation_duration', parseFloat(e.target.value))}
                                />
                                <InputError className="mt-2" message={errors?.depreciation_start_date ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label htmlFor="depreciation_end_date">Depreciation end date</Label>
                                <Input
                                    id="depreciation_end_date"
                                    type="date"
                                    value={data.depreciation_end_date ?? ''}
                                    onChange={(e) => setData('depreciation_end_date', e.target.value)}
                                    placeholder="Depreciation end date"
                                />
                                <InputError className="mt-2" message={errors?.depreciation_end_date ?? ''} />
                            </div>
                            <div className="w-full">
                                <Label htmlFor="residual_value">Residual value</Label>
                                <Input
                                    id="residual_value"
                                    type="number"
                                    min={0}
                                    step="0.01"
                                    value={data.residual_value ?? ''}
                                    onChange={(e) => setData('residual_value', parseFloat(e.target.value))}
                                    placeholder="Purchase cost (max. 2 decimals) : 4236.36"
                                />
                                <InputError className="mt-2" message={errors?.residual_value ?? ''} />
                            </div>
                        </div>
                    )}

                    {/* Warranty */}
                    <Label htmlFor="under_warranty">Still under warranty ?</Label>
                    <Checkbox
                        id="under_warranty"
                        name="under_warranty"
                        checked={data.under_warranty}
                        onClick={() => setData('under_warranty', !data.under_warranty)}
                    />
                    <InputError className="mt-2" message={errors?.under_warranty ?? ''} />

                    {data.under_warranty && (
                        <div>
                            <Label htmlFor="end_warranty_date">Date end of warranty</Label>
                            <Input
                                id="end_warranty_date"
                                type="date"
                                value={data.end_warranty_date}
                                min={minEndDateWarranty}
                                onChange={(e) => setData('end_warranty_date', e.target.value)}
                                placeholder="Date end of warranty"
                            />
                            <InputError className="mt-2" message={errors?.end_warranty_date ?? ''} />
                        </div>
                    )}
                    <div>
                        <Label htmlFor="need_maintenance">Need maintenance ?</Label>
                        <Checkbox
                            id="need_maintenance"
                            name="need_maintenance"
                            checked={data.need_maintenance}
                            onClick={() => setData('need_maintenance', !data.need_maintenance)}
                        />
                        <InputError className="mt-2" message={errors?.need_maintenance ?? ''} />
                    </div>

                    {data.need_maintenance && (
                        <>
                            <div className="flex flex-col gap-4 md:flex-row">
                                <div className="w-full">
                                    <Label htmlFor="maintenance_frequency">Maintenance frequency</Label>
                                    <select
                                        name="maintenance_frequency"
                                        value={data.maintenance_frequency ?? ''}
                                        onChange={(e) => setData('maintenance_frequency', e.target.value)}
                                        id=""
                                        required={data.need_maintenance}
                                        className={cn(
                                            'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                            'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                            'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                        )}
                                    >
                                        {frequencies && frequencies.length > 0 && (
                                            <>
                                                <option value="" disabled className="bg-background text-foreground">
                                                    Select an option
                                                </option>
                                                {frequencies?.map((frequency, index) => (
                                                    <option value={frequency} key={index} className="bg-background text-foreground">
                                                        {frequency}
                                                    </option>
                                                ))}
                                            </>
                                        )}
                                    </select>

                                    <InputError className="mt-2" message={errors?.maintenance_frequency ?? ''} />
                                </div>

                                <div className="w-full">
                                    <Label htmlFor="last_maintenance_date">Date last maintenance</Label>
                                    <Input
                                        id="last_maintenance_date"
                                        type="date"
                                        value={data.last_maintenance_date ?? ''}
                                        max={minEndDateWarranty}
                                        onChange={(e) => setData('last_maintenance_date', e.target.value)}
                                        placeholder="Date last maintenance"
                                    />
                                    <InputError className="mt-2" message={errors?.last_maintenance_date ?? ''} />
                                </div>
                                <div className="w-full">
                                    <Label htmlFor="next_maintenance_date">Date next maintenance</Label>
                                    <Input
                                        id="next_maintenance_date"
                                        type="date"
                                        value={data.next_maintenance_date ?? ''}
                                        min={minEndDateWarranty}
                                        onChange={(e) => setData('next_maintenance_date', e.target.value)}
                                        placeholder="Date last maintenance"
                                    />
                                    <InputError className="mt-2" message={errors?.next_maintenance_date ?? ''} />
                                </div>
                            </div>
                        </>
                    )}
                    <div>
                        <label className="mb-2 block text-sm font-medium">Maintenance manager</label>
                        <SearchableInput<User>
                            searchUrl={route('api.users.maintenance')}
                            // searchParams={{ interns: 1 }}
                            displayValue={data.maintenance_manager_name}
                            getDisplayText={(user) => user.full_name}
                            getKey={(user) => user.id}
                            onSelect={(user) => {
                                setData('maintenance_manager_id', user.id);
                                setData('maintenance_manager_name', user.full_name);
                            }}
                            onDelete={() => {
                                setData('maintenance_manager_id', null);
                                setData('maintenance_manager_name', null);
                            }}
                            placeholder="Search maintenance manager..."
                            className="mb-4"
                        />
                    </div>

                    <div>
                        <label className="mb-2 block text-sm font-medium">Providers</label>
                        <SearchableInput<Provider>
                            multiple={true}
                            searchUrl={route('api.providers.search')}
                            selectedItems={data.providers}
                            getDisplayText={(provider) => provider.name}
                            getKey={(provider) => provider.id}
                            onSelect={(providers) => {
                                setData('providers', providers);
                            }}
                            placeholder="Search providers..."
                        />
                    </div>

                    {!asset && (
                        <>
                            {/* Contracts */}
                            <div className="flex items-center gap-2">
                                <h5>Contract</h5>
                                <PlusCircleIcon onClick={() => setCountContracts((prev) => prev + 1)} />
                            </div>

                            {countContracts > 0 &&
                                [...Array(countContracts)].map((_, index) => (
                                    <div key={index} className="flex flex-col gap-2 rounded-md border-2 border-slate-400 p-4">
                                        <div className="flex w-fit gap-2">
                                            <p>Contract {index + 1}</p>
                                            <MinusCircleIcon onClick={() => handleRemoveContract(index)} />
                                        </div>
                                        <div>
                                            <Label className="font-medium">Name</Label>
                                            <Input
                                                type="text"
                                                value={data.contracts[index]?.name ?? ''}
                                                placeholder={`Contract name ${index + 1}`}
                                                className="rounded border px-2 py-1"
                                                minLength={4}
                                                maxLength={100}
                                                required
                                                onChange={(e) => handleChangeContracts(index, 'name', e.target.value)}
                                            />
                                            <InputError className="mt-2" message={errors?.contracts ? errors?.contracts[index]?.name : ''} />
                                            <Label className="font-medium">Type</Label>
                                            <Input
                                                type="text"
                                                // value={data.contracts[index].name ?? ''}
                                                placeholder={`Type ${index + 1}`}
                                                className="rounded border px-2 py-1"
                                                minLength={4}
                                                maxLength={100}
                                                required
                                                onChange={(e) => handleChangeContracts(index, 'type', e.target.value)}
                                            />
                                            <InputError className="mt-2" message={errors?.contracts ? errors?.contracts[index]?.type : ''} />

                                            <Label className="font-medium">Provider</Label>
                                            <SearchableInput<Provider>
                                                searchUrl={route('api.providers.search')}
                                                getKey={(provider) => provider.id}
                                                required
                                                displayValue={data.contracts[index]?.provider_name ?? ''}
                                                getDisplayText={(provider) => provider.name}
                                                onSelect={(provider) => {
                                                    handleChangeContracts(index, 'provider_id', provider.id);
                                                    handleChangeContracts(index, 'provider_name', provider.name);
                                                }}
                                                placeholder="Search provider..."
                                                // className="mb-4"
                                            />

                                            <Label htmlFor="start_date">Start date</Label>
                                            <Input
                                                id="start_date"
                                                type="date"
                                                value={data.contracts[index]?.start_date ?? ''}
                                                onChange={(e) => handleChangeContracts(index, 'start_date', e.target.value)}
                                            />
                                            <InputError className="mt-2" message={errors?.contracts ? errors?.contracts[index]?.start_date : ''} />
                                            <Label htmlFor="contract_duration">Contract duration</Label>
                                            <select
                                                name="contract_duration"
                                                onChange={(e) => handleChangeContracts(index, 'contract_duration', e.target.value)}
                                                id=""
                                                required
                                                value={data.contracts[index]?.contract_duration ?? ''}
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
                                            <InputError
                                                className="mt-2"
                                                message={errors?.contracts ? errors?.contracts[index]?.contract_duration : ''}
                                            />
                                            <Label>Notes</Label>
                                            <Textarea
                                                onChange={(e) => handleChangeContracts(index, 'notes', e.target.value)}
                                                value={data.contracts[index]?.notes ?? ''}
                                                minLength={4}
                                                maxLength={250}
                                            />
                                            <InputError message={errors?.contracts ? errors?.contracts[index]?.notes : ''} />
                                            <Label>Internal reference</Label>
                                            <Input
                                                type="text"
                                                onChange={(e) => handleChangeContracts(index, 'internal_reference', e.target.value)}
                                                value={data.contracts[index]?.internal_reference ?? ''}
                                                maxLength={50}
                                            />
                                            <InputError message={errors?.contracts ? errors?.contracts[index]?.internal_reference : ''} />
                                            <Label>Provider reference</Label>
                                            <Input
                                                type="text"
                                                onChange={(e) => handleChangeContracts(index, 'provider_reference', e.target.value)}
                                                value={data.contracts[index]?.provider_reference ?? ''}
                                                maxLength={50}
                                            />
                                            <InputError message={errors?.contracts ? errors?.contracts[index]?.provider_reference : ''} />
                                            <Label htmlFor="notice_period">Notice period</Label>
                                            <select
                                                name="notice_period"
                                                onChange={(e) => handleChangeContracts(index, 'notice_period', e.target.value)}
                                                id=""
                                                // required
                                                value={data.contracts[index]?.notice_period ?? ''}
                                                className={cn(
                                                    'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                                    'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                                    'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                                )}
                                            >
                                                {noticePeriods && noticePeriods.length > 0 && (
                                                    <>
                                                        {/* <option value="" disabled className="bg-background text-foreground">
                                                                            Select a duration
                                                                        </option> */}
                                                        {noticePeriods?.map((type, index) => (
                                                            <option value={type} key={index} className="bg-background text-foreground">
                                                                {type}
                                                            </option>
                                                        ))}
                                                    </>
                                                )}
                                            </select>
                                            <InputError className="mt-2" message={errors?.notice_period ?? ''} />
                                            <Label htmlFor="renewal_type">Renewal type</Label>
                                            <select
                                                name="renewal_type"
                                                // value={data.renewal_type ?? ''}
                                                required
                                                onChange={(e) => handleChangeContracts(index, 'renewal_type', e.target.value)}
                                                id=""
                                                defaultValue={''}
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
                                                    // value={data.status ?? ''}
                                                    required
                                                    defaultValue={''}
                                                    onChange={(e) => handleChangeContracts(index, 'status', e.target.value)}
                                                    id=""
                                                    // required={data.need_maintenance}
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
                                    </div>
                                ))}
                        </>
                    )}

                    {!asset && (
                        <div>
                            <Label>Pictures</Label>
                            <Input
                                type="file"
                                multiple
                                onChange={(e) => setData('pictures', e.target.files)}
                                accept="image/png, image/jpeg, image/jpg"
                            />
                        </div>
                    )}
                    {!asset && (
                        <div id="files">
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
                                                <p>
                                                    {
                                                        documentTypes.find((type) => {
                                                            return type.id === document.type;
                                                        })?.label
                                                    }
                                                </p>
                                                {isImage && <img src={fileURL} alt="preview" className="mx-auto h-40 w-40 rounded object-cover" />}
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

                    <br />
                    <Button type="submit">{asset ? 'Update' : 'Submit'}</Button>
                    <a href={asset ? route('tenant.assets.show', asset.reference_code) : route('tenant.assets.index')}>
                        <Button type="button" tabIndex={6} variant={'secondary'}>
                            Cancel
                        </Button>
                    </a>
                </form>
                {showFileModal && addFileModalForm()}
            </div>
        </AppLayout>
    );
}
