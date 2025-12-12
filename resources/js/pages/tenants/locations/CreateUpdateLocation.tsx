/* eslint-disable @typescript-eslint/no-explicit-any */
import InputError from '@/components/input-error';
import Modale from '@/components/Modale';
import ModaleForm from '@/components/ModaleForm';
import SearchableInput from '@/components/SearchableInput';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { CentralType, LocationType, TenantBuilding, TenantFloor, TenantRoom, TenantSite, User, type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { MinusCircleIcon, PlusCircleIcon } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

interface Provider {
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
    name: string;
    description: string;
    need_qr_code?: boolean;
    surface_floor: null | number;
    floor_material_id: number | string | null;
    floor_material_other: string;
    surface_walls: null | number;
    wall_material_id: number | string | null;
    wall_material_other: string;
    surface_outdoor?: null | number;
    outdoor_material_id?: number | string | null;
    outdoor_material_other?: string;
    height?: number | null;
    levelType: string | number;
    locationType: string | number;
    locationTypeName: string;
    existing_contracts: [];
    existing_documents: [];
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
    providers: Provider[];
    address: string;
};

export default function CreateUpdateLocation({
    location,
    levelTypes,
    locationTypes,
    routeName,
    documentTypes,
    frequencies,
    wallMaterials,
    floorMaterials,
    outdoorMaterials,
    statuses,
    renewalTypes,
    contractTypes,
    contractDurations,
    noticePeriods,
}: {
    location?: TenantSite | TenantBuilding | TenantFloor | TenantRoom;
    levelTypes: LocationType[] | TenantSite[] | TenantFloor[];
    locationTypes: LocationType[];
    documentTypes: CentralType[];
    wallMaterials: CentralType[];
    floorMaterials: CentralType[];
    outdoorMaterials: CentralType[];
    frequencies: string[];
    routeName: string;
    statuses: string[];
    renewalTypes: string[];
    contractTypes?: string[];
    contractDurations?: string[];
    noticePeriods?: string[];
}) {
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: location ? `${t('actions.update-type', { type: location.name })}` : `${t('actions.create-type', { type: routeName })}`,
            href: '/locations/create',
        },
    ];
    const [isProcessing, setIsProcessing] = useState<boolean>(false);
    const [existingContracts, setExistingContracts] = useState<Contract[]>([]);
    const [existingDocuments, setExistingDocuments] = useState<Document[]>([]);
    const [selectedDocuments, setSelectedDocuments] = useState<TypeFormData['files']>([]);
    const { data, setData } = useForm<TypeFormData>({
        name: location?.maintainable?.name ?? '',

        description: location?.maintainable?.description ?? '',
        surface_floor: location?.surface_floor ?? null,
        floor_material_id: location?.floor_material_other != null ? 'other' : (location?.floor_material_id ?? null),
        floor_material_other: location?.floor_material_other ?? '',
        surface_outdoor: location?.surface_outdoor ?? null,
        outdoor_material_id: location?.outdoor_material_other != null ? 'other' : (location?.outdoor_material_id ?? null),
        outdoor_material_other: location?.outdoor_material_other ?? '',
        surface_walls: location?.surface_walls ?? null,
        wall_material_id: location?.wall_material_other != null ? 'other' : (location?.wall_material_id ?? null),
        wall_material_other: location?.wall_material_other ?? '',
        height: location?.height ?? '',
        levelType: location?.level_id ?? '',
        need_qr_code: true,
        locationType: locationTypes.length == 1 ? locationTypes[0].id : (location?.location_type?.id ?? ''),
        locationTypeName: locationTypes.find((type) => type.id === location?.location_type.id)?.slug ?? '',
        files: selectedDocuments,
        maintenance_manager_id: location?.maintainable?.maintenance_manager_id ?? null,
        maintenance_manager_name: location?.maintainable?.manager?.full_name ?? '',
        need_maintenance: location?.maintainable.need_maintenance ?? false,
        maintenance_frequency: location?.maintainable.maintenance_frequency ?? '',
        next_maintenance_date: location?.maintainable.next_maintenance_date ?? '',
        last_maintenance_date: location?.maintainable.last_maintenance_date ?? '',
        providers: [],
        contracts: [],
        existing_contracts: [],
        existing_documents: [],
        address: location?.address ?? '',
    });
    const [errors, setErrors] = useState({});
    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);
        if (location) {
            try {
                const response = await axios.patch(route(`api.${routeName}.update`, location.reference_code), data);
                if (response.data.status === 'success') {
                    setIsProcessing(false);
                    router.visit(route(`tenant.${routeName}.show`, location.reference_code), {
                        preserveScroll: false,
                    });
                }
            } catch (error) {
                setIsProcessing(false);
                setErrors(error.response.data.errors);
            }
        } else {
            try {
                const response = await axios.post(route(`api.${routeName}.store`), data, {
                    headers: {
                        'Content-Type': 'multipart/form-data',
                    },
                });
                if (response.data.status === 'success') {
                    setIsProcessing(false);
                    router.visit(route(`tenant.${routeName}.index`), {
                        preserveScroll: false,
                    });
                }
            } catch (error) {
                setIsProcessing(false);
                setErrors(error.response.data.errors);
            }
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
    const todayDate = location ? '' : new Date().toISOString().split('T')[0];

    const addFileModalForm = () => {
        return (
            <ModaleForm title={t('actions.add-type', { type: tChoice('documents.title', 1) })}>
                <div className="flex flex-col gap-2">
                    <form onSubmit={addFile} className="space-y-2">
                        <Label htmlFor={`type`}>{t('common.type')}</Label>
                        <select
                            name="documentType"
                            required
                            value={newDocumentType ?? ''}
                            onChange={(e) => setNewDocumentType(parseInt(e.target.value))}
                            id="type"
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
                        <Label htmlFor={`file`}>{t('documents.file')}</Label>
                        <Input
                            type="file"
                            id="file"
                            onChange={(e) => setNewFile(e.target.files ? e.target.files[0] : null)}
                            required
                            accept="image/png, image/jpeg, image/jpg, .pdf"
                        />

                        <Label htmlFor={`name`}>{t('common.name')}</Label>
                        <Input
                            type="text"
                            name="name"
                            id="name"
                            required
                            minLength={4}
                            maxLength={100}
                            placeholder="Document name"
                            onChange={(e) => setNewFileName(e.target.value)}
                        />
                        <p className="text-border text-xs dark:text-white">{t('documents.filename_description')}</p>
                        <Label htmlFor={`description`}>{t('common.description')}</Label>
                        <Input
                            type="text"
                            name="description"
                            id="description"
                            minLength={10}
                            maxLength={250}
                            placeholder="Document description"
                            onChange={(e) => setNewFileDescription(e.target.value)}
                        />
                        <div className="flex justify-between">
                            <Button>{t('actions.add-type', { type: tChoice('documents.title', 1) })}</Button>
                            <Button type="button" onClick={closeFileModal} variant={'outline'}>
                                {t('actions.cancel')}
                            </Button>
                        </div>
                    </form>
                </div>
            </ModaleForm>
        );
    };

    useEffect(() => {
        if (routeName === 'buildings') {
            if (data.locationTypeName === 'outdoor') {
                setData((prev) => ({
                    ...prev,
                    surface_floor: null,
                    floor_material_id: '',
                    floor_material_other: '',
                    surface_walls: null,
                    wall_material_id: '',
                    wall_material_other: '',
                }));
            } else if (
                data.locationTypeName !== 'outdoor' &&
                (data.surface_outdoor !== null || data.outdoor_material_id !== '' || data.outdoor_material_other !== '')
            ) {
                setData((prev) => ({
                    ...prev,
                    surface_outdoor: null,
                    outdoor_material_id: '',
                    outdoor_material_other: '',
                }));
            }
        }
    }, [data.locationTypeName]);

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

    const minEndDateWarranty = new Date().toISOString().split('T')[0];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={location ? `${t('actions.update-type', { type: location.name })}` : `${t('actions.create-type', { type: routeName })}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {location && (
                    <div>
                        <p>
                            {t('common.reference_code')}: {location.reference_code}
                        </p>
                        <p>
                            {t('common.code')}: {location.code}{' '}
                        </p>
                    </div>
                )}
                <form onSubmit={submit} className="flex flex-col gap-4">
                    <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                        <h5>{tChoice('locations.location', 1)}</h5>
                        {levelTypes && (
                            <div>
                                <Label htmlFor="level">{t('locations.level')}</Label>
                                <select
                                    name="level"
                                    required
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
                                        {t('actions.select-type', { type: t('locations.level') })}
                                    </option>
                                    {levelTypes?.map((type) => (
                                        <option value={type.id} key={type.id}>
                                            {type.label ?? type.maintainable.name + ' (' + type.reference_code + ')'}
                                        </option>
                                    ))}
                                </select>
                                <InputError className="mt-2" message={errors.levelType} />
                            </div>
                        )}
                        {locationTypes && (
                            <>
                                <Label htmlFor="location-type">{t('common.type')}</Label>
                                <select
                                    name="location-type"
                                    required
                                    value={data.locationType}
                                    onChange={(e) => {
                                        setData('locationType', e.target.value);
                                        setData('locationTypeName', locationTypes.find((type) => type.id === parseInt(e.target.value))?.slug ?? '');
                                    }}
                                    disabled={location ? true : false}
                                    id="location-type"
                                    className={cn(
                                        'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                    )}
                                >
                                    <option value="" disabled>
                                        {t('actions.select-type', { type: t('common.type') })}
                                    </option>
                                    {locationTypes.map((type) => (
                                        <option value={type.id} key={type.id}>
                                            {type.label}
                                        </option>
                                    ))}
                                </select>
                                <InputError className="mt-2" message={errors.locationType ?? ''} />
                            </>
                        )}
                    </div>
                    <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                        <h5>{t('common.information')}</h5>

                        <Label htmlFor="name">{t('common.name')}</Label>
                        <Input
                            id="name"
                            type="text"
                            required
                            autoFocus
                            minLength={4}
                            maxLength={100}
                            value={data.name}
                            onChange={(e) => setData('name', e.target.value)}
                            placeholder="Name"
                        />
                        <InputError className="mt-2" message={errors.name ?? ''} />

                        <Label htmlFor="name">{t('common.description')}</Label>
                        <Input
                            id="description"
                            type="text"
                            required
                            minLength={10}
                            maxLength={255}
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            placeholder="Description"
                        />
                        <InputError className="mt-2" message={errors.description ?? ''} />

                        {!location && (
                            <div>
                                <Label htmlFor="need_qr_code">{t('assets.need_qr_code')}</Label>
                                <Checkbox
                                    id="need_qr_code"
                                    name="need_qr_code"
                                    checked={data.need_qr_code ?? true}
                                    onClick={() => setData('need_qr_code', !data.need_qr_code)}
                                />
                                <InputError className="mt-2" message={errors.need_qr_code ?? ''} />
                            </div>
                        )}

                        {routeName === 'sites' && (
                            <>
                                <Label htmlFor="address">{t('common.address')}</Label>
                                <Input
                                    id="address"
                                    type="text"
                                    required
                                    autoFocus
                                    minLength={10}
                                    maxLength={100}
                                    value={data.address}
                                    onChange={(e) => setData('address', e.target.value)}
                                    placeholder="address"
                                />
                                <InputError className="mt-2" message={errors.address ?? ''} />
                            </>
                        )}

                        {data.locationTypeName === 'outdoor' && (
                            <div className="flex">
                                <div className="w-full">
                                    <Label htmlFor="surface_outdoor">{t('common.surface')} (m²)</Label>
                                    <Input
                                        id="surface_outdoor"
                                        type="number"
                                        fef
                                        min={0}
                                        step="0.01"
                                        value={data.surface_outdoor ?? ''}
                                        placeholder={t('locations.surface_outdoor_placeholder')}
                                        onChange={(e) => setData('surface_outdoor', parseFloat(e.target.value))}
                                    />
                                    <InputError className="mt-2" message={errors.surface_outdoor ?? ''} />
                                </div>
                                {outdoorMaterials && (
                                    <div className="w-full">
                                        <Label htmlFor="outdoor_material_id">{t('locations.material_outdoor')}</Label>
                                        <select
                                            name="outdoor_material_id-type"
                                            value={data.outdoor_material_id ?? ''}
                                            onChange={(e) => {
                                                if (e.target.value !== 'other') {
                                                    setData('outdoor_material_other', '');
                                                }
                                                setData('outdoor_material_id', e.target.value);
                                            }}
                                            id="outdoor_material_id"
                                            className={cn(
                                                'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                                'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                                'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                            )}
                                        >
                                            <option value="" disabled>
                                                {t('actions.select-type', { type: t('locations.material_outdoor') })}
                                            </option>

                                            {outdoorMaterials.map((type) => (
                                                <option value={type.id} key={type.id}>
                                                    {type.label}
                                                </option>
                                            ))}
                                            <option value="other">{t('common.other')}</option>
                                        </select>
                                        <InputError className="mt-2" message={errors.locationType ?? ''} />
                                        {data.outdoor_material_id === 'other' && (
                                            <div>
                                                <Input
                                                    type="text"
                                                    value={data.outdoor_material_other}
                                                    placeholder="other outdoor material"
                                                    onChange={(e) => setData('outdoor_material_other', e.target.value)}
                                                />
                                            </div>
                                        )}
                                    </div>
                                )}
                            </div>
                        )}

                        {data.locationTypeName !== 'outdoor' && (
                            <>
                                <div className="flex">
                                    <div className="w-full">
                                        <Label htmlFor="surface_floor">{t('locations.surface_floor')} (m²)</Label>
                                        <Input
                                            id="surface_floor"
                                            type="number"
                                            min={0}
                                            step="0.01"
                                            value={data.surface_floor ?? ''}
                                            placeholder={t('locations.surface_floor_placeholder')}
                                            onChange={(e) => setData('surface_floor', parseFloat(e.target.value))}
                                        />
                                        <InputError className="mt-2" message={errors.surface_floor ?? ''} />
                                    </div>
                                    {floorMaterials && (
                                        <div className="w-full">
                                            <Label htmlFor="floor_material_id">{t('locations.material_floor')}</Label>
                                            <select
                                                name="floor_material_id-type"
                                                value={data.floor_material_id ?? ''}
                                                onChange={(e) => {
                                                    if (e.target.value !== 'other') {
                                                        setData('floor_material_other', '');
                                                    }
                                                    setData('floor_material_id', e.target.value);
                                                }}
                                                id="floor_material_id"
                                                className={cn(
                                                    'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                                    'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                                    'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                                )}
                                            >
                                                <option value="" disabled>
                                                    {t('actions.select-type', { type: t('locations.material_floor') })}
                                                </option>

                                                {floorMaterials.map((type) => (
                                                    <option value={type.id} key={type.id}>
                                                        {type.label}
                                                    </option>
                                                ))}
                                                <option value="other">{t('common.other')}</option>
                                            </select>
                                            <InputError className="mt-2" message={errors.locationType ?? ''} />
                                            {data.floor_material_id === 'other' && (
                                                <div>
                                                    <Input
                                                        type="text"
                                                        value={data.floor_material_other}
                                                        placeholder="other floor material"
                                                        onChange={(e) => setData('floor_material_other', e.target.value)}
                                                    />
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>
                                <div className="flex">
                                    <div className="w-full">
                                        <Label htmlFor="surface_walls">{t('locations.surface_wall')} (m²)</Label>
                                        <Input
                                            id="surface_walls"
                                            type="number"
                                            min={0}
                                            step="0.01"
                                            value={data.surface_walls ?? ''}
                                            onChange={(e) => setData('surface_walls', parseFloat(e.target.value))}
                                            placeholder={t('locations.surface_wall_placeholder')}
                                        />
                                        <InputError className="mt-2" message={errors.surface_walls ?? ''} />
                                    </div>
                                    {wallMaterials && (
                                        <div className="w-full">
                                            <Label htmlFor="wall_material_id">{t('locations.material_wall')}</Label>
                                            <select
                                                name="wall_material_id"
                                                value={data.wall_material_id ?? ''}
                                                onChange={(e) => {
                                                    if (e.target.value !== 'other') {
                                                        setData('wall_material_other', '');
                                                    }
                                                    setData('wall_material_id', e.target.value);
                                                }}
                                                id="wall_material_id"
                                                className={cn(
                                                    'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                                    'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                                    'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                                )}
                                            >
                                                <option value="" disabled>
                                                    {t('actions.select-type', { type: t('locations.material_wall') })}
                                                </option>

                                                {wallMaterials.map((type) => (
                                                    <option value={type.id} key={type.id}>
                                                        {type.label}
                                                    </option>
                                                ))}
                                                <option value="other">{t('common.other')}</option>
                                            </select>
                                            <InputError className="mt-2" message={errors.locationType ?? ''} />
                                            {data.wall_material_id === 'other' && (
                                                <div>
                                                    <Input
                                                        type="text"
                                                        value={data.wall_material_other}
                                                        placeholder="other wall material"
                                                        onChange={(e) => setData('wall_material_other', e.target.value)}
                                                    />
                                                </div>
                                            )}
                                        </div>
                                    )}
                                </div>
                                {routeName === 'rooms' && (
                                    <div className="w-full">
                                        <Label htmlFor="height">{t('locations.height')} (m)</Label>
                                        <Input
                                            id="height"
                                            type="number"
                                            min={0}
                                            step="0.01"
                                            value={data.height ?? ''}
                                            placeholder={t('locations.height_placeholder')}
                                            onChange={(e) => setData('height', parseFloat(e.target.value))}
                                        />
                                        <InputError className="mt-2" message={errors.height ?? ''} />
                                    </div>
                                )}
                            </>
                        )}
                    </div>

                    <div className="border-sidebar-border bg-sidebar space-y-2 rounded-md border p-4 shadow-xl">
                        <h5>{tChoice('maintenances.title', 1)}</h5>

                        <div className="flex items-center gap-2">
                            <Label htmlFor="need_maintenance">{t('maintenances.need_maintenance')} ?</Label>
                            <Checkbox
                                id="need_maintenance"
                                name="need_maintenance"
                                checked={data.need_maintenance}
                                onClick={() => setData('need_maintenance', !data.need_maintenance)}
                            />
                            <InputError className="mt-2" message={errors.need_maintenance ?? ''} />
                        </div>

                        {data.need_maintenance && (
                            <>
                                <div className="flex flex-col gap-4 md:flex-row">
                                    <div className="w-full">
                                        <Label htmlFor="maintenance_frequency">{t('maintenances.frequency.title')}</Label>
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
                                                        {t('actions.select-type', { type: t('maintenances.frequency.title') })}
                                                    </option>
                                                    {frequencies?.map((frequency, index) => (
                                                        <option value={frequency} key={index} className="bg-background text-foreground">
                                                            {t(`maintenances.frequency.${frequency}`)}
                                                        </option>
                                                    ))}
                                                </>
                                            )}
                                        </select>

                                        <InputError className="mt-2" message={errors?.maintenance_frequency ?? ''} />
                                    </div>

                                    <div className="w-full">
                                        <Label htmlFor="last_maintenance_date">{t('maintenances.last_maintenance_date')}</Label>
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
                                        <Label htmlFor="next_maintenance_date">{t('maintenances.next_maintenance_date')}</Label>
                                        <Input
                                            id="next_maintenance_date"
                                            type="date"
                                            value={data.next_maintenance_date ?? ''}
                                            min={location ? '' : minEndDateWarranty}
                                            onChange={(e) => setData('next_maintenance_date', e.target.value)}
                                        />
                                        <InputError className="mt-2" message={errors?.next_maintenance_date ?? ''} />
                                    </div>
                                </div>
                                <p className="mx-auto mt-1 text-center text-sm">{t('maintenances.next_maintenance_date_default')}</p>
                            </>
                        )}
                        <div>
                            <Label className="mb-2 block text-sm font-medium">{t('maintenances.maintenance_manager')}</Label>
                            <SearchableInput<User>
                                searchUrl={route('api.users.search')}
                                displayValue={data.maintenance_manager_name}
                                getDisplayText={(user) => user.full_name}
                                getKey={(user) => user.id}
                                onSelect={(user) => {
                                    setData('maintenance_manager_id', user.id);
                                    setData('maintenance_manager_name', user.full_name);
                                }}
                                placeholder={t('actions.search-type', { type: t('maintenances.maintenance_manager') })}
                                className="mb-4"
                            />
                        </div>
                    </div>
                    <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                        <h5>{tChoice('providers.title', 2)}</h5>
                        <div>
                            <SearchableInput<Provider>
                                multiple={true}
                                searchUrl={route('api.providers.search')}
                                selectedItems={data.providers}
                                getDisplayText={(provider) => provider.name}
                                getKey={(provider) => provider.id}
                                onSelect={(providers) => {
                                    setData('providers', providers);
                                }}
                                placeholder={t('actions.search-type', { type: tChoice('providers.title', 2) })}
                            />
                        </div>
                    </div>

                    {!location && (
                        <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                            {/* Contracts */}
                            <div className="flex items-center gap-2">
                                <h5>{tChoice('contracts.title', 1)}</h5>
                                <p className="">
                                    {t('contracts.add_new_contract')}{' '}
                                    <PlusCircleIcon className="inline-block" onClick={() => setCountContracts((prev) => prev + 1)} />
                                </p>
                            </div>
                            <SearchableInput<Contract>
                                multiple={true}
                                searchUrl={route('api.contracts.search')}
                                selectedItems={existingContracts}
                                getDisplayText={(contract) => contract.name}
                                getKey={(contract) => contract.id}
                                onSelect={(contracts) => {
                                    setExistingContracts(contracts);
                                    setData(
                                        'existing_contracts',
                                        contracts.map((elem) => elem.id),
                                    );
                                }}
                                placeholder={t('contracts.add_existing_contract')}
                            />

                            {countContracts > 0 &&
                                [...Array(countContracts)].map((_, index) => (
                                    <details key={index} className="flex flex-col rounded-md border-2 border-slate-400 p-4" open>
                                        <summary>
                                            <div className="flex w-fit gap-2">
                                                <p>
                                                    {tChoice('contracts.title', 1)} {index + 1}{' '}
                                                    {data.contracts[index]?.name ? `- ${data.contracts[index]?.name}` : ''}
                                                </p>
                                                <MinusCircleIcon onClick={() => handleRemoveContract(index)} />
                                            </div>
                                        </summary>
                                        <div>
                                            <Label className="font-medium" htmlFor={`contract.name.` + index}>
                                                {t('common.name')}
                                            </Label>
                                            <Input
                                                type="text"
                                                id={`contract.name.` + index}
                                                value={data.contracts[index]?.name ?? ''}
                                                placeholder={`Contract name ${index + 1}`}
                                                minLength={4}
                                                maxLength={100}
                                                required
                                                onChange={(e) => handleChangeContracts(index, 'name', e.target.value)}
                                            />
                                            <InputError className="mt-2" message={errors?.contracts ? errors?.contracts[index]?.name : ''} />
                                            <Label className="font-medium" htmlFor={`contract.type.` + index}>
                                                {t('common.type')}
                                            </Label>
                                            <select
                                                name="type"
                                                onChange={(e) => handleChangeContracts(index, 'type', e.target.value)}
                                                id="type"
                                                required
                                                value={data.contracts[index]?.type ?? ''}
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

                                            <InputError className="mt-2" message={errors?.contracts ? errors?.contracts[index]?.type : ''} />

                                            <Label className="font-medium">{tChoice('providers.title', 2)}</Label>
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
                                                placeholder={t('actions.search-type', { type: tChoice('providers.title', 1) })}
                                                // className="mb-4"
                                            />
                                            <InputError className="mt-2" message={errors?.contracts ? errors?.contracts[index]?.provider_id : ''} />

                                            <Label htmlFor="start_date">{t('contracts.start_date')}</Label>
                                            <Input
                                                id="start_date"
                                                type="date"
                                                value={data.contracts[index]?.start_date ?? ''}
                                                onChange={(e) => handleChangeContracts(index, 'start_date', e.target.value)}
                                            />
                                            <InputError className="mt-2" message={errors?.contracts ? errors?.contracts[index]?.start_date : ''} />
                                            <Label htmlFor="contract_duration">{t('contracts.duration_contract')}</Label>
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
                                                            {t('actions.select-type', { type: t('contracts.duration_contract') })}
                                                        </option>
                                                        {contractDurations?.map((type, index) => (
                                                            <option value={type} key={index} className="bg-background text-foreground">
                                                                {t('contracts.duration.' + type)}
                                                            </option>
                                                        ))}
                                                    </>
                                                )}
                                            </select>
                                            <InputError
                                                className="mt-2"
                                                message={errors?.contracts ? errors?.contracts[index]?.contract_duration : ''}
                                            />
                                            <Label htmlFor={`contract.notes.` + index}>Notes</Label>
                                            <Textarea
                                                id={`contract.notes.` + index}
                                                onChange={(e) => handleChangeContracts(index, 'notes', e.target.value)}
                                                value={data.contracts[index]?.notes ?? ''}
                                                minLength={4}
                                                maxLength={250}
                                            />
                                            <InputError message={errors?.contracts ? errors?.contracts[index]?.notes : ''} />
                                            <Label htmlFor={`contract.internal_ref.` + index}>{t('contracts.internal_ref')}</Label>
                                            <Input
                                                id={`contract.internal_ref.` + index}
                                                type="text"
                                                onChange={(e) => handleChangeContracts(index, 'internal_reference', e.target.value)}
                                                value={data.contracts[index]?.internal_reference ?? ''}
                                                maxLength={50}
                                            />
                                            <InputError message={errors?.contracts ? errors?.contracts[index]?.internal_reference : ''} />
                                            <Label htmlFor={`contract.provider_ref.` + index}>{t('contracts.provider_ref')}</Label>
                                            <Input
                                                id={`contract.provider_ref.` + index}
                                                type="text"
                                                onChange={(e) => handleChangeContracts(index, 'provider_reference', e.target.value)}
                                                value={data.contracts[index]?.provider_reference ?? ''}
                                                maxLength={50}
                                            />
                                            <InputError message={errors?.contracts ? errors?.contracts[index]?.provider_reference : ''} />
                                            <Label htmlFor="notice_period">{t('contracts.notice_period.title')}</Label>
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
                                                        <option value="" disabled className="bg-background text-foreground">
                                                            {t('actions.select-type', { type: t('contracts.notice_period.title') })}
                                                        </option>
                                                        {noticePeriods?.map((type, index) => (
                                                            <option value={type} key={index} className="bg-background text-foreground">
                                                                {t('contracts.notice_period.' + type)}
                                                            </option>
                                                        ))}
                                                    </>
                                                )}
                                            </select>
                                            <InputError className="mt-2" message={errors?.notice_period ?? ''} />
                                            <Label htmlFor={`contract.renewal_type.` + index}>{t('contracts.renewal_type.title')}</Label>
                                            <select
                                                name="renewal_type"
                                                // value={data.renewal_type ?? ''}
                                                required
                                                onChange={(e) => handleChangeContracts(index, 'renewal_type', e.target.value)}
                                                id={`contract.renewal_type.` + index}
                                                defaultValue={''}
                                                // required={data.need_maintenance}
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
                                                        {renewalTypes?.map((type, index) => (
                                                            <option value={type} key={index} className="bg-background text-foreground">
                                                                {t(`contracts.renewal_type.${type}`)}
                                                            </option>
                                                        ))}
                                                    </>
                                                )}
                                            </select>
                                            <InputError className="mt-2" message={errors?.contracts ? errors?.contracts[index]?.renewal_type : ''} />
                                            <div className="w-full">
                                                <Label htmlFor={`contract.status.` + index}>{t('common.status.title')} </Label>
                                                <select
                                                    name="status"
                                                    // value={data.status ?? ''}
                                                    required
                                                    defaultValue={''}
                                                    onChange={(e) => handleChangeContracts(index, 'status', e.target.value)}
                                                    id={`contract.status.` + index}
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
                                                                {t('actions.select-type', { type: t('common.status.title') })}
                                                            </option>
                                                            {statuses?.map((status, index) => (
                                                                <option value={status} key={index} className="bg-background text-foreground">
                                                                    {t(`common.status.${status}`)}
                                                                </option>
                                                            ))}
                                                        </>
                                                    )}
                                                </select>
                                                <InputError className="mt-2" message={errors?.contracts ? errors?.contracts[index]?.status : ''} />
                                            </div>
                                        </div>
                                    </details>
                                ))}
                        </div>
                    )}

                    {!location && (
                        <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                            <h5>{tChoice('documents.title', 2)}</h5>
                            <SearchableInput<Document>
                                multiple={true}
                                searchUrl={route('api.documents.search')}
                                selectedItems={existingDocuments}
                                getDisplayText={(document) => document.name + '-' + document.mime_type}
                                getKey={(document) => document.id}
                                onSelect={(documents) => {
                                    setExistingDocuments(documents);
                                    setData(
                                        'existing_documents',
                                        documents.map((elem) => elem.id),
                                    );
                                }}
                                placeholder="Add existing documents..."
                            />
                            <Button onClick={() => setShowFileModal(!showFileModal)} type="button">
                                {t('actions.add-type', { type: t('documents.file') })}
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
                                                    {t('actions.remove')}
                                                </Button>
                                            </li>
                                        );
                                    })}
                                </ul>
                            )}
                        </div>
                    )}
                    <div className="mt-2 flex gap-2">
                        <Button type="submit" disabled={isProcessing}>
                            {location ? t('actions.update') : t('actions.submit')}
                        </Button>
                        <a href={location ? route(`tenant.${routeName}.show`, location.reference_code) : route(`tenant.${routeName}.index`)}>
                            <Button type="button" disabled={isProcessing} tabIndex={6} variant={'secondary'}>
                                {t('actions.cancel')}
                            </Button>
                        </a>
                    </div>
                </form>
                {showFileModal && addFileModalForm()}

                {isProcessing && (
                    <Modale
                        message={
                            location
                                ? t('actions.type-being-updated', { type: tChoice(`locations.${location}`, 1) })
                                : t('actions.type-being-submitted', { type: tChoice(`locations.${location}`, 1) })
                        }
                        isOpen={isProcessing}
                        isProcessing={isProcessing}
                    />
                )}
            </div>
        </AppLayout>
    );
}
