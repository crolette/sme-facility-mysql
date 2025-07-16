import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { Textarea } from '@/components/ui/textarea';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Asset, CentralType, Ticket, type BreadcrumbItem } from '@/types';
import { Head, useForm, usePage } from '@inertiajs/react';
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

type FormDataTicket = {
    ticket_id: number;
    location_type: string;
    location_id: number;
    description: string;
    reported_by: number;
    reporter_email: string;
    being_notified: boolean;
    pictures: File[];
};

export default function ShowAsset({ asset }: { asset: Asset }) {
    const auth = usePage().props.auth.user;
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${asset.reference_code} - ${asset.maintainable.name}`,
            href: ``,
        },
    ];

    const [documents, setDocuments] = useState(asset.documents);
    const [pictures, setPictures] = useState(asset.pictures);

    const { post, delete: destroy } = useForm();

    const deleteAsset = (asset: Asset) => {
        destroy(route(`tenant.assets.destroy`, asset.code));
    };

    const deleteDocument = async (id: number) => {
        try {
            const response = await axios.delete(route('api.documents.delete', id));
            if (response.data.status === 'success') {
                fetchDocuments();
            }
        } catch (error) {
            console.error('Erreur lors de la suppression', error);
        }
    };

    const deletePicture = async (id: number) => {
        try {
            const response = await axios.delete(route('api.picture.delete', id));
            if (response.data.status === 'success') {
                fetchPictures();
            }
        } catch (error) {
            console.error('Erreur lors de la suppression', error);
        }
    };

    const fetchPictures = async () => {
        try {
            const response = await axios.get(`/api/v1/assets/${asset.code}/pictures`);
            setPictures(await response.data);
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
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

        const newFile = {
            files: [newFileData],
        };
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

    const [addPictures, setAddPictures] = useState(false);
    const [newPictures, setNewPictures] = useState<File[] | null>(null);

    const postNewPictures: FormEventHandler = async (e) => {
        e.preventDefault();
        try {
            const response = await axios.post(route('api.assets.pictures.post', asset.code), newPictures, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            if (response.data.status === 'success') {
                fetchPictures();
                setNewPictures(null);
                setAddPictures(!addPictures);
            }
        } catch (error) {
            console.log(error);
        }
    };

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

    const addNewPicturesModal = () => {
        return (
            <div className="bg-background/50 absolute inset-0 z-50">
                <div className="bg-background/20 flex h-dvh items-center justify-center">
                    <div className="bg-background flex items-center justify-center p-4">
                        <form onSubmit={postNewPictures}>
                            <input
                                type="file"
                                multiple
                                onChange={(e) => setNewPictures({ pictures: e.target.files })}
                                accept="image/png, image/jpeg, image/jpg"
                            />

                            <Button disabled={newPictures == null}>Add new pictures</Button>
                            <Button onClick={() => setAddPictures(!addPictures)} type="button" variant={'secondary'}>
                                Cancel
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        );
    };

    const closeTicket = async (id: number) => {
        try {
            const response = await axios.patch(route('api.tickets.close', id));
            if (response.data.status === 'success') {
                fetchTickets();
            }
        } catch (error) {
            console.error('Erreur lors de la suppression', error);
        }
    };

    const fetchTickets = async () => {
        try {
            const response = await axios.get(`/api/v1/assets/${asset.code}/tickets`);
            setTickets(await response.data);
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
        }
    };

    const [addTicketModal, setAddTicketModal] = useState<boolean>(false);
    const [submitTypeTicket, setSubmitTypeTicket] = useState<'edit' | 'new'>('edit');
    const updateTicketData = {
        ticket_id: 0,
        location_type: 'assets',
        location_id: asset.id,
        being_notified: false,
        description: '',
        reported_by: auth.id,
        reporter_email: auth.email,
        pictures: [],
    };

    const submitEditTicket: FormEventHandler = async (e) => {
        e.preventDefault();
        try {
            const response = await axios.patch(route('api.tickets.update', newTicketData.ticket_id), newTicketData);
            fetchTickets();
            closeModalTicket();
            // }
        } catch (error) {
            console.log(error);
        }
    };

    const submitNewTicket: FormEventHandler = async (e) => {
        e.preventDefault();
        try {
            const response = await axios.post(route('api.tickets.store'), newTicketData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            if (response.data.status === 'success') {
                fetchTickets();
                closeModalTicket();
            }
        } catch (error) {
            console.log(error);
        }
    };

    const [newTicketData, setNewTicketData] = useState<FormDataTicket>(updateTicketData);

    const closeModalTicket = () => {
        setAddTicketModal(false);
        setNewTicketData(updateTicketData);
        setSubmitTypeTicket('edit');
    };

    const [tickets, setTickets] = useState<Ticket[]>(asset.tickets);

    const editTicket = async (id: number) => {
        setSubmitTypeTicket('edit');
        try {
            const response = await axios.get(route('api.tickets.get', id), {});
            setNewTicketData((prev) => ({
                ...prev,
                ticket_id: response.data.data.id,
                description: response.data.data.description,
                being_notified: response.data?.data.being_notified,
            }));

            setAddTicketModal(true);

            // }
        } catch (error) {
            console.log(error);
        }
    };

    const addTicket = () => {
        return (
            <div className="bg-background/50 absolute inset-0 z-50">
                <div className="bg-background/20 flex h-dvh items-center justify-center">
                    <div className="bg-background flex items-center justify-center p-10">
                        <form onSubmit={submitTypeTicket === 'edit' ? submitEditTicket : submitNewTicket} className="flex flex-col gap-4">
                            <Input type="text" name="email" value={newTicketData.reporter_email} required disabled placeholder="Reporter email" />
                            <Textarea
                                name="description"
                                id="description"
                                required
                                minLength={10}
                                maxLength={250}
                                placeholder="Ticket description"
                                onChange={(e) =>
                                    setNewTicketData((prev) => ({
                                        ...prev,
                                        description: e.target.value,
                                    }))
                                }
                                value={newTicketData.description}
                            />
                            <Input
                                type="file"
                                multiple
                                accept="image/png, image/jpeg, image/jpg"
                                onChange={(e) => {
                                    // const pictures = { pictures: };
                                    setNewTicketData((prev) => ({
                                        ...prev,
                                        pictures: e.target.files,
                                    }));
                                }}
                            />
                            <div className="flex items-center gap-4">
                                <Label htmlFor="notified">Do you want to be notified of changes ? </Label>
                                <Checkbox
                                    id="notified"
                                    checked={newTicketData.being_notified}
                                    onClick={() => {
                                        setNewTicketData((prev) => ({
                                            ...prev,
                                            being_notified: !newTicketData.being_notified,
                                        }));
                                    }}
                                />
                            </div>
                            <Button>Add new ticket</Button>
                            <Button onClick={closeModalTicket} type="button" variant={'secondary'}>
                                Cancel
                            </Button>
                        </form>
                    </div>
                </div>
            </div>
        );
    };

    const restoreAsset = (asset: Asset) => {
        post(route('api.tenant.assets.restore', asset.id));
    };

    const deleteDefinitelyAsset = (asset: Asset) => {
        destroy(route(`api.tenant.assets.force`, asset.id));
    };

    console.log(asset);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Asset ${asset.maintainable.name}`} />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div>
                    {asset.deleted_at ? (
                        <>
                            <Button onClick={() => restoreAsset(asset)} variant={'green'}>
                                Restore
                            </Button>
                            <Button onClick={() => deleteDefinitelyAsset(asset)} variant={'destructive'}>
                                Delete definitely
                            </Button>
                        </>
                    ) : (
                        <>
                            <a href={route(`tenant.assets.edit`, asset.code)}>
                                <Button>Edit</Button>
                            </a>
                            <Button onClick={() => deleteAsset(asset)} variant={'destructive'}>
                                Delete
                            </Button>
                            <Button
                                onClick={() => {
                                    setSubmitTypeTicket('new');
                                    setAddTicketModal(!addTicketModal);
                                }}
                            >
                                Add new ticket
                            </Button>
                        </>
                    )}

                    {/* <a href={route(`tenant.tickets.create`)}> */}

                    {/* </a> */}
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
                    <summary className="">
                        <h3 className="inline">Tickets ({tickets?.length ?? 0})</h3>
                    </summary>
                    {tickets.length > 0 && (
                        <Table>
                            <TableHead>
                                <TableHeadRow>
                                    <TableHeadData>Code</TableHeadData>
                                    <TableHeadData>Status</TableHeadData>
                                    <TableHeadData>Reporter</TableHeadData>
                                    <TableHeadData>Description</TableHeadData>
                                    <TableHeadData>Created at</TableHeadData>
                                    <TableHeadData>Updated at</TableHeadData>
                                    <TableHeadData></TableHeadData>
                                </TableHeadRow>
                            </TableHead>
                            <TableBody>
                                {tickets.map((ticket, index) => {
                                    return (
                                        <TableBodyRow key={index}>
                                            <TableBodyData>
                                                <a href={route('tenant.tickets.show', ticket.id)}>{ticket.code}</a>
                                            </TableBodyData>
                                            <TableBodyData>{ticket.status}</TableBodyData>
                                            <TableBodyData>{ticket.code}</TableBodyData>
                                            <TableBodyData>{ticket.description}</TableBodyData>
                                            <TableBodyData>{ticket.created_at}</TableBodyData>
                                            <TableBodyData>{ticket.updated_at !== ticket.created_at ? ticket.updated_at : '-'}</TableBodyData>
                                            <TableBodyData>PICTURES</TableBodyData>

                                            <TableBodyData>
                                                {ticket.status !== 'closed' && (
                                                    <>
                                                        <Button variant={'destructive'} onClick={() => closeTicket(ticket.id)}>
                                                            Close
                                                        </Button>

                                                        <Button onClick={() => editTicket(ticket.id)}>Edit</Button>
                                                    </>
                                                )}
                                            </TableBodyData>
                                        </TableBodyRow>
                                    );
                                })}
                            </TableBody>
                        </Table>
                    )}
                </details>

                <details>
                    <summary className="">
                        <h3 className="inline">Documents ({documents?.length ?? 0})</h3>
                        <Button onClick={() => addNewFile()}>Add new file</Button>
                    </summary>
                    {documents && documents.length > 0 && (
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
                <div className="flex">
                    <h3 className="inline">Pictures ({pictures?.length})</h3>
                    {!addPictures && (
                        <Button onClick={() => setAddPictures(!addPictures)} type="button">
                            Add pictures
                        </Button>
                    )}
                </div>
                <div className="flex flex-wrap gap-4">
                    {pictures &&
                        pictures.length > 0 &&
                        pictures.map((picture, index) => {
                            return (
                                <div key={index} className="w-32">
                                    <a href={route('pictures.show', picture.id)} download className="w cursor-pointer">
                                        <img src={route('pictures.show', picture.id)} className="aspect-square object-cover" />
                                    </a>
                                    <Button variant={'destructive'} onClick={() => deletePicture(picture.id)}>
                                        Delete
                                    </Button>
                                </div>
                            );
                        })}
                </div>
            </div>
            {addPictures && addNewPicturesModal()}
            {showFileModal && addFileModalForm()}
            {addTicketModal && addTicket()}
        </AppLayout>
    );
}
