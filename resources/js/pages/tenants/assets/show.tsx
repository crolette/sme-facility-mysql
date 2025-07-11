import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Asset, CentralType, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { FormEventHandler, useEffect, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

type TypeFormData = {
    documentId: number;
    name: string;
    description: string;
    typeId: null | number;
    typeSlug: string;
};

export default function ShowAsset({ asset }: { asset: Asset }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${asset.reference_code} - ${asset.maintainable.name}`,
            href: ``,
        },
    ];

    const [documents, setDocuments] = useState(asset.documents);

    const { delete: destroy } = useForm();

    const deleteAsset = (asset: Asset) => {
        destroy(route(`tenant.assets.destroy`, asset.code));
    };

    const deleteDocument = async (id: number) => {
        try {
            await axios.delete(route('api.documents.delete', id));
            fetchDocuments();
        } catch (error) {
            console.error('Erreur lors de la suppression', error);
        }
    };

    const fetchDocuments = async () => {
        try {
            const response = await axios.get(`/api/v1/assets/${asset.code}/documents`);
            setDocuments(await response.data);
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
        }
    };

    const [showFileModal, setShowFileModal] = useState(false);
    const [documentTypes, setDocumentTypes] = useState<CentralType[]>([]);

    const updateDocumentData = {
        documentId: 0,
        name: '',
        description: '',
        typeId: 0,
        typeSlug: '',
    };

    const [newFileData, setNewFileData] = useState<TypeFormData>(updateDocumentData);

    const closeFileModal = () => {
        setNewFileData(updateDocumentData);
        setShowFileModal(!showFileModal);
        setDocumentTypes([]);
        fetchDocuments();
        setSubmitType('edit');
    };

    const fetchDocumentTypes = async () => {
        try {
            const response = await axios.get(`/api/v1/category-types/?type=document`);
            setDocumentTypes(await response.data);
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
            const errors = error.response.data.errors;
            console.error('Erreur de validation :', errors);
        }
    };

    const editFile = (id: number) => {
        fetchDocumentTypes();

        const document = documents.find((document) => {
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
        }));

        setShowFileModal(!showFileModal);
    };

    const [submitType, setSubmitType] = useState<'edit' | 'new'>('edit');
    const addNewFile = () => {
        console.log('addNewFile');
        fetchDocumentTypes();
        setSubmitType('new');
        setShowFileModal(!showFileModal);
    };

    useEffect(() => {
        if (documentTypes.length === 0) return;

        const found = documentTypes.find((documentType) => documentType.id === newFileData.typeId);

        if (found) {
            setNewFileData((prev) => ({
                ...prev,
                typeSlug: found.slug,
            }));
        }
    }, [documentTypes, newFileData.typeId]);

    const submitEditFile: FormEventHandler = async (e) => {
        e.preventDefault();

        try {
            const response = await axios.patch(route('api.documents.update', newFileData.documentId), newFileData);
            if (response.data.status === 'success') {
                closeFileModal();
            }
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
        }
    };

    const submitNewFile: FormEventHandler = async (e) => {
        e.preventDefault();
        console.log('submitNewFile');
        const newFile = {
            files: [newFileData],
        };
        console.log(newFile);
        try {
            await axios.post(route('api.assets.documents.post', asset.code), newFile, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            closeFileModal();
        } catch (error) {
            console.log(error);
        }
    };

    console.log(newFileData);

    const addFileModalForm = () => {
        return (
            <div className="bg-background/50 absolute inset-0 z-50">
                <div className="bg-background/20 flex h-dvh items-center justify-center">
                    <div className="bg-background flex items-center justify-center p-4">
                        <div className="flex flex-col gap-2">
                            <form onSubmit={submitType === 'edit' ? submitEditFile : submitNewFile} className="space-y-2">
                                <p className="text-center">Edit document</p>
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
                                    {documentTypes && documentTypes.length > 0 && (
                                        <>
                                            <option value={0} disabled className="bg-background text-foreground">
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
                                {submitType === 'new' && (
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
                                )}

                                <Input
                                    type="text"
                                    name="name"
                                    value={newFileData.name}
                                    required
                                    placeholder="Document name"
                                    onChange={(e) =>
                                        setNewFileData((prev) => ({
                                            ...prev,
                                            name: e.target.value,
                                        }))
                                    }
                                />

                                <Input
                                    type="text"
                                    name="description"
                                    id="description"
                                    value={newFileData.description}
                                    required
                                    minLength={10}
                                    maxLength={250}
                                    placeholder="Document description"
                                    onChange={(e) =>
                                        setNewFileData((prev) => ({
                                            ...prev,
                                            description: e.target.value,
                                        }))
                                    }
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
            <Head title={`Asset ${asset.maintainable.name}`} />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div>
                    <a href={route(`tenant.assets.edit`, asset.code)}>
                        <Button>Edit</Button>
                    </a>
                    <Button onClick={() => deleteAsset(asset)} variant={'destructive'}>
                        Delete
                    </Button>
                    <Button onClick={() => addNewFile()}>Add new file</Button>
                </div>
                <p>Code : {asset.code}</p>
                <p>Reference code : {asset.reference_code}</p>
                <p>Location : {asset.location.maintainable.description}</p>
                <p>Category : {asset.category}</p>
                <p>Name : {asset.maintainable?.name}</p>
                <p>Description : {asset.maintainable?.description}</p>
                <p>Purchase date : {asset.maintainable?.purchase_date}</p>
                <p>Purchase cost : {asset.maintainable?.purchase_cost}</p>
                <p>End warranty date : {asset.maintainable?.end_warranty_date}</p>
                <p>Brand : {asset.brand}</p>
                <p>Model : {asset.model}</p>
                <p>Serial number : {asset.serial_number}</p>

                <details>
                    <summary>
                        <h3 className="inline">Documents ({documents.length})</h3>
                    </summary>
                    {documents.length > 0 && (
                        <Table>
                            <TableHead>
                                <TableHeadRow>
                                    <TableHeadData>File</TableHeadData>
                                    <TableHeadData>Size</TableHeadData>
                                    <TableHeadData>Filename</TableHeadData>
                                    <TableHeadData>Name</TableHeadData>
                                    <TableHeadData>Description</TableHeadData>
                                    <TableHeadData>Created at</TableHeadData>
                                    <TableHeadData>Category</TableHeadData>
                                    <TableHeadData></TableHeadData>
                                </TableHeadRow>
                            </TableHead>
                            <TableBody>
                                {documents.map((document, index) => {
                                    const isImage = document.mime_type.startsWith('image/');
                                    const isPdf = document.mime_type === 'application/pdf';
                                    return (
                                        <TableBodyRow key={index}>
                                            <TableBodyData>
                                                <a href={route('documents.show', document.id)}>
                                                    {isImage && (
                                                        <img
                                                            src={route('documents.show', document.id)}
                                                            alt="preview"
                                                            className="mx-auto h-20 w-20 rounded object-cover"
                                                        />
                                                    )}
                                                    {isPdf && <BiSolidFilePdf size={'80px'} className="mx-auto" />}
                                                </a>
                                            </TableBodyData>

                                            <TableBodyData>{document.sizeMo} Mo</TableBodyData>
                                            <TableBodyData>{document.filename}</TableBodyData>
                                            <TableBodyData>{document.name}</TableBodyData>
                                            <TableBodyData>{document.description}</TableBodyData>
                                            <TableBodyData>{document.created_at}</TableBodyData>
                                            <TableBodyData>{document.category}</TableBodyData>
                                            <TableBodyData>
                                                <Button variant={'destructive'} onClick={() => deleteDocument(document.id)}>
                                                    Delete
                                                </Button>
                                                <Button onClick={() => editFile(document.id)}>Edit</Button>
                                            </TableBodyData>
                                        </TableBodyRow>
                                    );
                                })}
                            </TableBody>
                        </Table>
                    )}
                </details>
            </div>

            {showFileModal && addFileModalForm()}
        </AppLayout>
    );
}
