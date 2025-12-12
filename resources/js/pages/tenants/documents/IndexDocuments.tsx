import Modale from '@/components/Modale';
import ModaleForm from '@/components/ModaleForm';
import { Pagination } from '@/components/pagination';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { BreadcrumbItem, CentralType, Documents, PaginatedData } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Loader, Pencil, PlusCircle, Trash2, X } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

export interface SearchParams {
    q: string | null;
    sortBy: string | null;
    orderBy: string | null;
    type: string | null;
}

type DocumentFormData = {
    documentId: number;
    name: string;
    description: string;
    typeId: null | number;
    typeSlug: string;
};

export default function IndexDocuments({ items, filters, types }: { items: PaginatedData; filters: SearchParams; types: CentralType[] }) {
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index documents`,
            href: `/documents`,
        },
    ];

    const [debouncedSearch, setDebouncedSearch] = useState<string>('');
    const { showToast } = useToast();
    const [isLoading, setIsLoading] = useState<boolean>(false);

    const [query, setQuery] = useState<SearchParams>({
        q: filters.q ?? null,
        sortBy: filters.sortBy ?? null,
        orderBy: filters.orderBy ?? null,
        type: filters.type ?? null,
    });
    const [search, setSearch] = useState(query.q);
    const [prevQuery, setPrevQuery] = useState(query);
    const [isUpdating, setIsUpdating] = useState<boolean>(false);
    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
    const [documentToDelete, setDocumentToDelete] = useState(null);

    useEffect(() => {
        if (!search) return;

        const handler = setTimeout(() => {
            setDebouncedSearch(search);
        }, 500);

        return () => {
            clearTimeout(handler);
        };
    }, [search]);

    useEffect(() => {
        if (query.q !== debouncedSearch && debouncedSearch?.length > 2) {
            router.visit(route('tenant.documents.index', { ...query, q: debouncedSearch }), {
                onStart: () => {
                    setIsLoading(true);
                },
                onFinish: () => {
                    setIsLoading(false);
                },
            });
        }
    }, [debouncedSearch]);

    const clearSearch = () => {
        router.visit(route('tenant.documents.index'), {
            onStart: () => {
                setIsLoading(true);
            },
            onFinish: () => {
                setIsLoading(false);
            },
        });
    };

    useEffect(() => {
        if (query !== prevQuery)
            router.visit(route('tenant.documents.index', { ...query }), {
                onStart: () => {
                    setIsLoading(true);
                },
                onFinish: () => {
                    setIsLoading(false);
                },
            });
    }, [query]);

    const deleteDocument = async () => {
        if (!documentToDelete) return;

        setIsUpdating(true);
        try {
            const response = await axios.delete(route('api.documents.delete', documentToDelete));
            if (response.data.status === 'success') {
                setShowDeleteModale(false);
                setIsUpdating(false);
                showToast(response.data.message, response.data.status);
                router.visit(route('tenant.documents.index', { ...query }));
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
            setIsUpdating(false);
        }
    };

    const updateDocumentData = {
        documentId: 0,
        name: '',
        description: '',
        typeId: 0,
        typeSlug: '',
    };

    const [showFileModal, setShowFileModal] = useState(false);
    const [newFileData, setNewFileData] = useState<DocumentFormData>(updateDocumentData);
    const [submitType, setSubmitType] = useState<'edit' | 'new'>('edit');

    const closeFileModal = () => {
        setNewFileData(updateDocumentData);
        setShowFileModal(!showFileModal);
        setSubmitType('edit');
    };

    const editFile = (id: number) => {
        const document = items.data?.find((document) => {
            return document.id === id;
        });

        if (!document) {
            closeFileModal();
            return;
        }

        setNewFileData((prev) => ({
            ...prev,
            documentId: document.id,
            name: document?.name,
            description: document?.description,
            typeId: document?.category_type_id,
            typeSlug: document?.document_category.slug,
        }));

        setShowFileModal(!showFileModal);
    };

    const submitEditFile: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsUpdating(true);

        try {
            const response = await axios.patch(route(`api.documents.update`, newFileData.documentId), newFileData);
            if (response.data.status === 'success') {
                closeFileModal();
                showToast(response.data.message, response.data.status);
                router.visit(route('tenant.documents.index', { ...query }));
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
        setIsUpdating(false);
    };

    const addNewFile = () => {
        setSubmitType('new');
        setShowFileModal(!showFileModal);
    };

    const submitNewFile: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsUpdating(true);

        try {
            const response = await axios.post(route('api.documents.store'), newFileData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });

            if (response.data.status === 'success') {
                closeFileModal();
                showToast(response.data.message, response.data.status);
                router.visit(route('tenant.documents.index'));
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Index documents" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="border-accent flex flex-col gap-2 border-b-2 pb-2 sm:flex-row sm:gap-10">
                    <details
                        className="border-border relative z-10 w-full cursor-pointer rounded-md border-2 p-2"
                        open={isLoading ? false : undefined}
                    >
                        <summary>{t('common.search_filter')}</summary>

                        <div className="bg-border border-border text-background dark:text-foreground absolute top-full flex flex-col items-center gap-4 rounded-b-md border-2 p-2 sm:flex-row">
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="status">{t('common.type')}</Label>
                                <select
                                    name="type"
                                    id="type"
                                    value={query.type ?? ''}
                                    onChange={(e) => setQuery((prev) => ({ ...prev, type: e.target.value }))}
                                >
                                    <option value={''} aria-readonly>
                                        Select a type
                                    </option>
                                    {types.map((type) => (
                                        <option key={type.id} value={type.id}>
                                            {type.label}
                                        </option>
                                    ))}
                                </select>
                            </div>
                            <div className="flex flex-col items-center gap-2">
                                <Label htmlFor="category">{t('actions.search')}</Label>
                                <div className="relative text-black dark:text-white">
                                    <Input type="text" value={search ?? ''} onChange={(e) => setSearch(e.target.value)} />
                                    <X
                                        onClick={() => setQuery((prev) => ({ ...prev, q: null }))}
                                        className={'absolute top-1/2 right-0 -translate-1/2'}
                                    />
                                </div>
                            </div>

                            <Button onClick={clearSearch} size={'sm'}>
                                {t('actions.search-clear')}
                            </Button>
                        </div>
                    </details>
                    <Button onClick={() => addNewFile()}>
                        {' '}
                        <PlusCircle />
                        {t('actions.add-type', { type: tChoice('documents.title', 1) })}
                    </Button>
                </div>
                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>{t(`documents.file`)}</TableHeadData>
                            <TableHeadData>{t(`documents.size`)}</TableHeadData>
                            <TableHeadData>{t(`common.name`)}</TableHeadData>
                            <TableHeadData>{t(`common.description`)}</TableHeadData>
                            <TableHeadData>{t(`common.category`)}</TableHeadData>
                            <TableHeadData>{t(`common.created_at`)}</TableHeadData>
                            <TableHeadData></TableHeadData>
                        </TableHeadRow>
                    </TableHead>
                    <TableBody>
                        {isLoading ? (
                            <TableBodyRow>
                                <TableBodyData>
                                    <p className="flex animate-pulse gap-2">
                                        <Loader />
                                        {t(`actions.searching`)}
                                    </p>
                                </TableBodyData>
                            </TableBodyRow>
                        ) : (
                            items.data.map((document: Documents, index) => {
                                const isImage = document.mime_type.startsWith('image/');
                                const isPdf = document.mime_type === 'application/pdf';
                                return (
                                    <TableBodyRow key={index}>
                                        <TableBodyData>
                                            <a href={route('api.file.download', { path: document.path })} download className="w-fit cursor-pointer">
                                                {isImage && (
                                                    <img
                                                        src={route('api.documents.show', document.id)}
                                                        alt="preview"
                                                        className="mx-auto h-10 w-10 rounded object-cover"
                                                    />
                                                )}
                                                {isPdf && <BiSolidFilePdf size={'40px'} className="mx-auto" />}
                                            </a>
                                        </TableBodyData>

                                        <TableBodyData>{document.sizeMo} Mo</TableBodyData>
                                        <TableBodyData>{document.name}</TableBodyData>
                                        <TableBodyData>{document.description}</TableBodyData>
                                        <TableBodyData>{document.category}</TableBodyData>
                                        <TableBodyData>{document.created_at}</TableBodyData>
                                        <TableBodyData className="flex space-x-2">
                                            <>
                                                <Button onClick={() => editFile(document.id)}>
                                                    <Pencil />
                                                </Button>

                                                <Button
                                                    variant={'destructive'}
                                                    onClick={() => {
                                                        setDocumentToDelete(document.id);
                                                        setShowDeleteModale(!showDeleteModale);
                                                    }}
                                                >
                                                    <Trash2 />
                                                </Button>
                                            </>
                                        </TableBodyData>
                                    </TableBodyRow>
                                );
                            })
                        )}
                    </TableBody>
                </Table>
                <Pagination items={items} />
            </div>
            <Modale
                title={t('actions.delete-type', { type: tChoice('documents.title', 1) })}
                message={t('documents.delete_description')}
                isOpen={showDeleteModale}
                isUpdating={isUpdating}
                onConfirm={deleteDocument}
                onCancel={() => {
                    setShowDeleteModale(false);
                }}
            />
            {showFileModal && (
                <ModaleForm title={submitType === 'edit' ? 'Edit document' : 'Add document'} isUpdating={isUpdating}>
                    <div className="flex flex-col gap-2">
                        <form onSubmit={submitType === 'edit' ? submitEditFile : submitNewFile} className="space-y-4">
                            <div>
                                <Label>{t(`common.category`)}</Label>
                                <select
                                    name="documentType"
                                    required
                                    value={newFileData.typeId ?? ''}
                                    onChange={(e) =>
                                        setNewFileData((prev) => ({
                                            ...prev,
                                            typeId: parseInt(e.target.value),
                                        }))
                                    }
                                    id=""
                                    className={cn(
                                        'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                    )}
                                >
                                    {types && types.length > 0 && (
                                        <>
                                            <option value={0} disabled className="bg-background text-foreground">
                                                {t(`actions.select-type`, { type: t(`common.category`) })}
                                            </option>
                                            {types?.map((documentType) => (
                                                <option value={documentType.id} key={documentType.id} className="bg-background text-foreground">
                                                    {documentType.label}
                                                </option>
                                            ))}
                                        </>
                                    )}
                                </select>
                            </div>

                            {submitType === 'new' && (
                                <>
                                    <Label>{t(`documents.file`)}</Label>
                                    <Input
                                        type="file"
                                        name=""
                                        id=""
                                        onChange={(e) =>
                                            setNewFileData((prev) => ({
                                                ...prev,
                                                file: e.target.files ? e.target.files[0] : null,
                                            }))
                                        }
                                        required
                                        accept="image/png, image/jpeg, image/jpg, .pdf"
                                    />
                                    <p className="text-xs">{t('documents.accepted_files_with_size', { size: 4 })}</p>
                                </>
                            )}

                            <Label>{t(`common.name`)}</Label>
                            <Input
                                type="text"
                                name="name"
                                value={newFileData.name}
                                required
                                minLength={10}
                                maxLength={255}
                                placeholder={t(`documents.name_placeholder`)}
                                onChange={(e) =>
                                    setNewFileData((prev) => ({
                                        ...prev,
                                        name: e.target.value,
                                    }))
                                }
                            />
                            <Label>{t(`common.description`)}</Label>
                            <Input
                                type="text"
                                name="description"
                                id="description"
                                value={newFileData.description}
                                maxLength={250}
                                placeholder={t(`documents.description_placeholder`)}
                                onChange={(e) =>
                                    setNewFileData((prev) => ({
                                        ...prev,
                                        description: e.target.value,
                                    }))
                                }
                            />
                            <div className="flex justify-between">
                                <Button>{t('actions.submit')}</Button>
                                <Button type="button" onClick={closeFileModal} variant={'outline'}>
                                    {t('actions.cancel')}
                                </Button>
                            </div>
                        </form>
                    </div>
                </ModaleForm>
            )}
        </AppLayout>
    );
}
