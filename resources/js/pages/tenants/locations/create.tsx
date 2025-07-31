/* eslint-disable @typescript-eslint/no-explicit-any */
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { CentralType, LocationType, TenantBuilding, TenantFloor, TenantRoom, TenantSite, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

type TypeFormData = {
    name: string;
    description: string;
    surface_floor: null | number;
    surface_walls: null | number;
    levelType: string | number;
    locationType: string | number;
    files: {
        file: File;
        name: string;
        description: string;
        typeId: null | number;
        typeSlug: string;
    }[];
};

export default function CreateLocation({
    location,
    levelTypes,
    locationTypes,
    routeName,
    documentTypes,
}: {
    location?: TenantSite | TenantBuilding | TenantFloor | TenantRoom;
    levelTypes: LocationType[] | TenantSite[] | TenantFloor[];
    locationTypes: LocationType[];
    documentTypes: CentralType[];
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
        levelType: (location?.level_id ?? levelTypes?.length == 1) ? levelTypes[0].id : '',
        locationType: (location?.location_type?.id ?? locationTypes.length == 1) ? locationTypes[0].id : '',
        files: selectedDocuments,
    });

    console.log(data);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (location) {
            post(route(`tenant.${routeName}.update`, location.reference_code), {
                headers: {
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': 'PATCH',
                    Accept: 'application/json',
                },
            });
        } else {
            post(route(`tenant.${routeName}.store`));
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
