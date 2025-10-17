import { cn } from '@/lib/utils';
import { CentralType } from '@/types';
import axios from 'axios';
import { FormEventHandler, useEffect, useState } from 'react';
import ModaleForm from '../ModaleForm';
import { Button } from '../ui/button';
import { Input } from '../ui/input';

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
            setDocumentTypes(response.data.data);
        } catch (error) {
            console.error('Erreur lors de la recherche :', error);
            const errors = error.response.data.errors;
            console.error('Erreur de validation :', errors);
            return [];
        }
    };

    useEffect(() => {
        fetchDocumentTypes();
    }, []);

    const [documentTypes, setDocumentTypes] = useState<CentralType[]>();

    const addFile: FormEventHandler = (e) => {
        e.preventDefault();

        if (!newFile) return;

        const typeSlug = documentTypes?.find((type) => {
            return type.id === newDocumentType;
        })?.slug;

        const fileToAdd: TypeFormData['files'][number] = {
            file: newFile,
            name: newFileName,
            description: newFileDescription,
            typeId: newDocumentType,
            typeSlug: typeSlug ?? '',
        };

        documents.push(fileToAdd);
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
            <ModaleForm title={'Add new document'}>
                <div className="flex flex-col gap-2">
                    <form onSubmit={addFile} className="space-y-2">
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

                        <Input type="text" name="name" required placeholder="Document name" onChange={(e) => setNewFileName(e.target.value)} />
                        <p className="text-border text-xs">Servira à la sauvegarde du nom du fichier</p>
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
                            <Button>Submit</Button>
                            <Button type="button" onClick={onToggleModal} variant={'outline'}>
                                Cancel
                            </Button>
                        </div>
                    </form>
                </div>
            </ModaleForm>
        )
    );
}
