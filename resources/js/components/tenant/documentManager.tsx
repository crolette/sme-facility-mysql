import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { cn } from '@/lib/utils';
import { CentralType, Documents } from '@/types';
import axios from 'axios';
import { FormEventHandler, useEffect, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Label } from '../ui/label';
import { Loader2, Pencil, PlusCircle, Trash2, Unlink } from 'lucide-react';
import Modale from '../Modale';
import SearchableInput from '../SearchableInput';
import { useToast } from '../ToastrContext';

interface DocumentManagerProps {
    itemCodeId: number | string;
    getDocumentsUrl: string;
    uploadRoute: string;
    editRoute: string;
    deleteRoute: string;
    showRoute: string;
    removableRoute?: string;
    canAdd?: boolean;
}

type DocumentFormData = {
    documentId: number;
    name: string;
    description: string;
    typeId: null | number;
    typeSlug: string;
};

export const DocumentManager = ({
    itemCodeId,
    getDocumentsUrl,
    editRoute,
    uploadRoute,
    deleteRoute,
    showRoute,
    removableRoute,
    canAdd = true,
}: DocumentManagerProps) => {
    const { showToast } = useToast();
    const [documents, setDocuments] = useState<Documents[]>();
    const [isUpdating, setIsUpdating] = useState<boolean>(false);

    useEffect(() => {
        fetchDocuments();
    }, []);

    const removeDocument = async (id: number) => {
        if (!removableRoute || !id)
            return;
        
        setIsUpdating(true);

          try {
              const response = await axios.patch(route(removableRoute, itemCodeId), { document_id: id });
              if (response.data.status === 'success') {
                  fetchDocuments();
              }
          } catch (error) {
              showToast(error.response.data.message, error.response.data.status);
          }
        
            setIsUpdating(false);
      };

     const [documentToDelete, setDocumentToDelete] = useState(null);
    const deleteDocument = async () => {
        if (!documentToDelete)
            return;
            
            setIsUpdating(true);
        try {
            const response = await axios.delete(route(deleteRoute, documentToDelete));
            if (response.data.status === 'success') {
                fetchDocuments();
                setShowDeleteModale(false);
                 setIsUpdating(false);
                showToast(response.data.message, response.data.status);
                
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
            setIsUpdating(false);
        }
    };

    const fetchDocuments = async () => {
        try {
            const response = await axios.get(route(getDocumentsUrl, itemCodeId));
            setDocuments(response.data.data);
            setIsUpdating(false);
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
        }
    };


    const [showFileModal, setShowFileModal] = useState(false);
    const [documentTypes, setDocumentTypes] = useState<CentralType[]>([]);
        const [existingDocuments, setExistingDocuments] = useState<Documents[] | [] >([]);
        const [addExistingDocumentsModale, setAddExistingDocumentsModale] = useState<boolean>(false);

    const updateDocumentData = {
        documentId: 0,
        name: '',
        description: '',
        typeId: 0,
        typeSlug: '',
    };

    const [newFileData, setNewFileData] = useState<DocumentFormData>(updateDocumentData);

    const closeFileModal = () => {
        setNewFileData(updateDocumentData);
        setShowFileModal(!showFileModal);
        setDocumentTypes([]);
        fetchDocuments();
        setSubmitType('edit');
    };

        const addExistingDocumentsToAsset = async () => {
            const documents = {
                existing_documents: existingDocuments.map((elem) => elem.id),
            };

            setIsUpdating(true);

            try {
                const response = await axios.post(route(uploadRoute, itemCodeId), documents);
                if (response.data.status === 'success') {
                    setAddExistingDocumentsModale(false);
                    fetchDocuments();
                    setExistingDocuments([])
                    showToast(response.data.message, response.data.status);
                }
            } catch (error) {
                showToast(error.response.data.message, error.response.data.status);
            }

             setIsUpdating(false);
        };

    const fetchDocumentTypes = async () => {
        try {
            const response = await axios.get(route('api.category-types', { type: 'document' }));
            setDocumentTypes(await response.data.data);
        } catch (error) {
            const errors = error.response.data.errors;
        }
    };

    const editFile = (id: number) => {
        fetchDocumentTypes();

        const document = documents?.find((document) => {
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
        setIsUpdating(true);

        try {
            const response = await axios.patch(route(editRoute, newFileData.documentId), newFileData);
            if (response.data.status === 'success') {
                closeFileModal();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
        setIsUpdating(false);
    };

    const submitNewFile: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsUpdating(true);

        const newFile = {
            files: [newFileData],
        };
        try {
            const response = await axios.post(route(uploadRoute, itemCodeId), newFile, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            if (response.data.status === 'success') {
                closeFileModal();
                 showToast(response.data.message, response.data.status);
                }
            } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };
    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
   

    return (
        <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
            <div className="flex items-center justify-between">
                <h2 className="inline">Documents ({documents?.length ?? 0})</h2>
                <div className='space-x-4'>
            {canAdd && (
                <>
                    <Button onClick={() => setAddExistingDocumentsModale(true)}> <PlusCircle /> Add existing document</Button>
                    <Button onClick={() => addNewFile()}> <PlusCircle />Add new file</Button>
                </>
                    )}
                    </div>
                </div>
            {documents && documents.length > 0 && (
                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>File</TableHeadData>
                            <TableHeadData>Size</TableHeadData>
                            <TableHeadData>Name</TableHeadData>
                            <TableHeadData>Description</TableHeadData>
                            <TableHeadData>Category</TableHeadData>
                            <TableHeadData>Created at</TableHeadData>
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
                                        <a href={route('api.file.download', { path: document.path })} download className="w-fit cursor-pointer">
                                            {isImage && (
                                                <img
                                                    src={route(showRoute, document.id)}
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
                                        {canAdd && (
                                            <>
                                                <Button variant={'outline'} onClick={() => removeDocument(document.id)}>
                                                    <Unlink />
                                                    Remove
                                                </Button>
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
                                        )}
                                    </TableBodyData>
                                </TableBodyRow>
                            );
                        })}
                    </TableBody>
                </Table>
            )}
            {isUpdating && (
                <div className="bg-background/50 fixed inset-0 z-50">
                    <div className="bg-background/20 flex h-dvh items-center justify-center">
                        <div className="bg-background flex flex-col items-center justify-center p-4">
                            <Loader2 size={36} className="animate-spin" />
                            <p className="animate-pulse">Updating...</p>
                        </div>
                    </div>
                </div>
            )}
            {showFileModal && (
                <div className="bg-background/50 fixed inset-0 z-40">
                    <div className="bg-background/20 flex h-dvh items-center justify-center">
                        <div className="bg-background flex items-center justify-center p-4">
                            <div className="flex flex-col gap-2">
                                <form onSubmit={submitType === 'edit' ? submitEditFile : submitNewFile} className="space-y-2">
                                    <p className="text-center font-semibold">{submitType === 'edit' ? 'Edit document' : 'Add document'}</p>
                                    <Label>Document category</Label>
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
                                        <>
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
                                            <p className="text-xs">Accepted files: png, jpg, pdf. - Maximum file size: 4MB</p>
                                        </>
                                    )}
                                    <Label>Document name</Label>
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
                                    <Label>Document description</Label>
                                    <Input
                                        type="text"
                                        name="description"
                                        id="description"
                                        value={newFileData.description}
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
            )}
            <Modale
                title={'Delete document'}
                message={`Are you sure you want to delete this document ?`}
                isOpen={showDeleteModale}
                onConfirm={deleteDocument}
                onCancel={() => {
                    setShowDeleteModale(false);
                }}
            />
            {addExistingDocumentsModale && (
                <div className="bg-background/50 fixed inset-0 z-50">
                    <div className="bg-background/20 flex h-dvh items-center justify-center">
                        <div className="bg-background flex flex-col items-center justify-center p-4 text-center md:max-w-1/3 gap-4">
                            <p className='font-semibold'>Add existing document</p>
                            <SearchableInput<Documents>
                                multiple={true}
                                searchUrl={route('api.documents.search')}
                                selectedItems={existingDocuments}
                                getDisplayText={(document) => document.name}
                                getKey={(document) => document.id}
                                onSelect={(documents) => {
                                    setExistingDocuments(documents);
                                }}
                                placeholder="Search documents..."
                            />
                            <div className='space-x-4'>
                                <Button onClick={addExistingDocumentsToAsset}>Add document</Button>
                                <Button
                                variant="secondary"
                                onClick={() => {
                                    setAddExistingDocumentsModale(false);
                                    setExistingDocuments([]);
                                }}
                            >
                                Cancel
                            </Button>
                                </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};
