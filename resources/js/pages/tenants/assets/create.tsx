/* eslint-disable @typescript-eslint/no-explicit-any */
import InputError from '@/components/input-error';
import SearchableInput from '@/components/SearchableInput';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Asset, AssetCategory, CentralType, Provider, User, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { FormEventHandler, useEffect, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

interface Provider {
    id: number;
    name: string;
}

type TypeFormData = {
    q: string;
    name: string;
    surface: null | number;
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
    files: {
        file: File;
        name: string;
        description: string;
        typeId: null | number;
        typeSlug: string;
    }[];
    pictures: File[];
    providers: Provider[];
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
}: {
    asset?: Asset;
    categories?: AssetCategory[];
    documentTypes: CentralType[];
    frequencies: string[];
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create asset`,
            href: '/assets/create',
        },
    ];

    const [selectedDocuments, setSelectedDocuments] = useState<TypeFormData['files']>([]);
    const { data, setData, post, errors } = useForm<TypeFormData>({
        q: '',
        name: asset?.maintainable.name ?? '',
        description: asset?.maintainable.description ?? '',
        surface: asset?.surface ?? null,
        locationId: asset?.location_id ?? '',
        locationReference: asset?.location.reference_code ?? '',
        locationType: asset?.location.location_type.level ?? '',
        locationName: asset?.location.maintainable.name ?? '',
        categoryId: asset?.asset_category.id ?? '',
        maintenance_manager_id: asset?.maintainable?.maintenance_manager_id ?? null,
        maintenance_manager_name: asset?.maintainable?.manager?.full_name ?? '',
        purchase_date: asset?.maintainable.purchase_date ?? '',
        purchase_cost: asset?.maintainable.purchase_cost ?? null,
        under_warranty: asset?.maintainable.under_warranty ?? false,
        end_warranty_date: asset?.maintainable.end_warranty_date ?? '',
        need_maintenance: asset?.maintainable.need_maintenance ?? '',
        maintenance_frequency: asset?.maintainable.maintenance_frequency ?? '',
        next_maintenance_date: asset?.maintainable.next_maintenance_date ?? '',
        last_maintenance_date: asset?.maintainable.last_maintenance_date ?? '',
        brand: asset?.maintainable.brand ?? '',
        model: asset?.maintainable.model ?? '',
        serial_number: asset?.maintainable.serial_number ?? '',
        files: selectedDocuments,
        pictures: [],
        providers: [],
    });

    console.log(data);
    console.log(asset);

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

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (asset) {
            post(route(`tenant.assets.update`, asset.reference_code), {
                headers: {
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': 'PATCH',
                    Accept: 'application/json',
                },
            });
        } else {
            post(route(`tenant.assets.store`));
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

    console.log(locations);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Create asset`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {asset && (
                    <div>
                        <p>Asset Reference: {asset.reference_code}</p>
                        <p>Asset Code: {asset.code} </p>
                        <p>
                            Asset attached to : {asset.location.maintainable.name} - {asset.location.location_type.label}
                        </p>
                    </div>
                )}
                <form onSubmit={submit}>
                    <Label htmlFor="search">Search</Label>
                    <div className="relative">
                        <Input type="search" value={data.q} onChange={(e) => setData('q', e.target.value)} placeholder="Search by code or name" />
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
                            type="text"
                            disabled
                            value={data.locationName ? data.locationName + ' - ' + data.locationReference : 'No location selected'}
                        />
                        <InputError className="mt-2" message={errors.locationType} />
                    </>
                    {/* )} */}

                    <Label htmlFor="name">Category</Label>
                    <select
                        name="level"
                        required
                        value={data.categoryId === '' ? 0 : data.categoryId}
                        onChange={(e) => setData('categoryId', e.target.value)}
                        id=""
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
                    <InputError className="mt-2" message={errors.categoryId} />

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
                    <InputError className="mt-2" message={errors.name} />

                    <Label htmlFor="description">Description</Label>
                    <Input
                        id="description"
                        type="text"
                        maxLength={255}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder="Asset description"
                    />
                    <InputError className="mt-2" message={errors.description} />

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
                    <InputError className="mt-2" message={errors.surface} />
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
                            <InputError className="mt-2" message={errors.brand} />
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
                            <InputError className="mt-2" message={errors.model} />
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
                            <InputError className="mt-2" message={errors.serial_number} />
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
                        <InputError className="mt-2" message={errors.purchase_date} />
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
                    <InputError className="mt-2" message={errors.purchase_cost} />

                    <Label htmlFor="under_warranty">Still under warranty ?</Label>
                    <Checkbox
                        id="under_warranty"
                        name="under_warranty"
                        checked={data.under_warranty}
                        onClick={() => setData('under_warranty', !data.under_warranty)}
                    />
                    <InputError className="mt-2" message={errors.under_warranty} />

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
                            <InputError className="mt-2" message={errors.end_warranty_date} />
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
                        <InputError className="mt-2" message={errors.need_maintenance} />
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

                                    <InputError className="mt-2" message={errors.maintenance_frequency} />
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
                                    <InputError className="mt-2" message={errors.last_maintenance_date} />
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
                                    <InputError className="mt-2" message={errors.next_maintenance_date} />
                                </div>
                            </div>
                        </>
                    )}
                    <div>
                        <label className="mb-2 block text-sm font-medium">Maintenance manager</label>
                        <SearchableInput<User>
                            searchUrl={route('api.users.search')}
                            displayValue={data.maintenance_manager_name}
                            getDisplayText={(user) => user.full_name}
                            getKey={(user) => user.id}
                            onSelect={(user) => {
                                setData('maintenance_manager_id', user.id);
                                setData('maintenance_manager_name', user.full_name);
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
