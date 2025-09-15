import { cn } from "@/lib/utils";
import { Button } from "../ui/button";
import { Input } from "../ui/input";
import { FormEventHandler, useState } from "react";
import { CentralType } from "@/types";
import axios from "axios";

type TypeFormData = {

    files: {
        file: File;
        name: string;
        description: string;
        typeId: null | number;
        typeSlug: string;
    }[];
};

interface FileManagerProps {
    documents: TypeFormData['files'];
    showModal: boolean;
    onDocumentsChange: (documents: TypeFormData['files']) => void;
    onToggleModal: () => void;
}

export default function FileManager({ documents, showModal, onDocumentsChange, onToggleModal }: FileManagerProps) {
    
    const [newFileName, setNewFileName] = useState('');
    const [newFileDescription, setNewFileDescription] = useState('');
    const [newFile, setNewFile] = useState<File | null>(null);
    const [newDocumentType, setNewDocumentType] = useState<number | null>(null);
    
    const fetchDocumentTypes = async () => {
        try {
            const response = await axios.get(route('api.category-types', { type: 'document' }));
            return await response.data.data;
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
            const errors = error.response.data.errors;
            console.error('Erreur de validation :', errors);
            return []
        }
    };

    
    
    const [documentTypes, setDocumentTypes] = useState<CentralType[]>(fetchDocumentTypes);

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

        documents.push(fileToAdd)
        onDocumentsChange(documents);
        closeFileModal();
        onToggleModal();
    };

    const closeFileModal = () => {
        setNewFileName('');
        setNewFileDescription('');
        setNewDocumentType(null);
        setNewFile(null);
    };


    return (
        showModal && (
            <div className="bg-background/50 fixed inset-0 z-50">
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
                                    <Button type="button" onClick={onToggleModal} variant={'outline'}>
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        )
    );
}