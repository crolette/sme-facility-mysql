/* eslint-disable @typescript-eslint/no-explicit-any */
import InputError from '@/components/input-error';
import SearchableInput from '@/components/SearchableInput';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { CentralType, LocationType, TenantBuilding, TenantFloor, TenantRoom, TenantSite, User, type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

interface Provider {
    id: number;
    name: string;
}

type TypeFormData = {
    name: string;
    description: string;
    surface_floor: null | number;
    surface_walls: null | number;
    levelType: string | number;
    locationType: string | number;
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
    providers: Provider[];
};

export default function CreateLocation({
    location,
    levelTypes,
    locationTypes,
    routeName,
    documentTypes,
    frequencies,
}: {
    location?: TenantSite | TenantBuilding | TenantFloor | TenantRoom;
    levelTypes: LocationType[] | TenantSite[] | TenantFloor[];
    locationTypes: LocationType[];
    documentTypes: CentralType[];
    frequencies: string[];
    routeName: string;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create ${routeName} type`,
            href: '/locations/create',
        },
    ];

    const [selectedDocuments, setSelectedDocuments] = useState<TypeFormData['files']>([]);
    const { data, setData, post, errors } = useForm<TypeFormData>({
        name: location?.maintainable?.name ?? '',
        description: location?.maintainable?.description ?? '',
        surface_floor: location?.surface_floor ?? null,
        surface_walls: location?.surface_walls ?? null,
        levelType: location?.level_id ?? '',
        locationType: location?.location_type?.id ?? '',
        files: selectedDocuments,
        maintenance_manager_id: location?.maintainable?.maintenance_manager_id ?? null,
        maintenance_manager_name: location?.maintainable?.manager?.full_name ?? '',
        need_maintenance: location?.maintainable.need_maintenance ?? '',
        maintenance_frequency: location?.maintainable.maintenance_frequency ?? '',
        next_maintenance_date: location?.maintainable.next_maintenance_date ?? '',
        last_maintenance_date: location?.maintainable.last_maintenance_date ?? '',
        providers: [],
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (location) {
            post(route(`api.${routeName}.update`, location.reference_code), {
                headers: {
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': 'PATCH',
                    Accept: 'application/json',
                },
            });
        } else {
            post(route(`api.${routeName}.store`));
            router.visit(route(`tenant.${routeName}.index`), {
                preserveScroll: false,
            });
        }
    };

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

    const todayDate = new Date().toISOString().split('T')[0];

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
    console.log(location);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Create location type`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {location && (
                    <div>
                        <p>Location Reference: {location.reference_code}</p>
                        <p>Location Code: {location.code} </p>
                    </div>
                )}
                <form onSubmit={submit}>
                    <div>
                        {levelTypes && (
                            <>
                                <Label htmlFor="level">Level</Label>
                                <select
                                    name="level"
                                    value={data.levelType}
                                    onChange={(e) => setData('levelType', e.target.value)}
                                    disabled={location ? true : false}
                                    id=""
                                    className={cn(
                                        'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                    )}
                                >
                                    <option value="" disabled>
                                        -- Select a level --
                                    </option>
                                    {levelTypes?.map((type) => (
                                        <option value={type.id} key={type.id}>
                                            {type.label ?? type.maintainable.name + ' (' + type.reference_code + ')'}
                                        </option>
                                    ))}
                                </select>
                                <InputError className="mt-2" message={errors.levelType} />
                            </>
                        )}
                    </div>
                    {locationTypes && (
                        <div>
                            <Label htmlFor="location-type">Location type</Label>
                            <select
                                name="location-type"
                                value={data.locationType}
                                onChange={(e) => setData('locationType', e.target.value)}
                                disabled={location ? true : false}
                                id="location-type"
                                className={cn(
                                    'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                    'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                    'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                )}
                            >
                                <option value="" disabled>
                                    -- Select a location type --
                                </option>
                                {locationTypes.map((type) => (
                                    <option value={type.id} key={type.id}>
                                        {type.label}
                                    </option>
                                ))}
                            </select>
                            <InputError className="mt-2" message={errors.locationType} />
                        </div>
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
                        placeholder="Name"
                    />
                    <InputError className="mt-2" message={errors.name} />

                    <Label htmlFor="surface_floor">Surface floor</Label>
                    <Input
                        id="surface_floor"
                        type="number"
                        min={0}
                        step="0.01"
                        value={data.surface_floor ?? ''}
                        placeholder="Surface floor (max. 2 decimals) : 4236.3"
                        onChange={(e) => setData('surface_floor', parseFloat(e.target.value))}
                    />
                    <InputError className="mt-2" message={errors.surface_floor} />

                    <Label htmlFor="surface_walls">Surface walls</Label>
                    <Input
                        id="surface_walls"
                        type="number"
                        min={0}
                        step="0.01"
                        value={data.surface_walls ?? ''}
                        onChange={(e) => setData('surface_walls', parseFloat(e.target.value))}
                        placeholder="Surface walls (max. 2 decimals) : 4236.3"
                    />
                    <InputError className="mt-2" message={errors.surface_walls} />

                    <Label htmlFor="name">Description</Label>
                    <Input
                        id="description"
                        type="text"
                        required
                        // disabled={type?.prefix ? true : false}
                        maxLength={255}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder="Description"
                    />
                    <InputError className="mt-2" message={errors.description} />
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
                                        max={todayDate}
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
                                        min={todayDate}
                                        onChange={(e) => setData('next_maintenance_date', e.target.value)}
                                        placeholder="Date last maintenance"
                                    />
                                    <InputError className="mt-2" message={errors.next_maintenance_date} />
                                </div>
                            </div>
                        </>
                    )}
                    <div></div>
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
                            placeholder="Rechercher un manager..."
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
                    {!location && (
                        <div id="files">
                            <Button onClick={() => setShowFileModal(!showFileModal)} type="button">
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

                    <Button type="submit">{location ? 'Update' : 'Submit'}</Button>
                    <a href={location ? route(`tenant.${routeName}.show`, location.reference_code) : route(`tenant.${routeName}.index`)}>
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
