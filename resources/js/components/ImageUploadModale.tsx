import { useForm } from '@inertiajs/react';
import axios from 'axios';
import { ImageIcon, Upload, X } from 'lucide-react';
import { useEffect, useRef, useState } from 'react';
import { useToast } from './ToastrContext';
import { Button } from './ui/button';

// Props pour ImageUploadModal
interface ImageUploadModalProps {
    isOpen: boolean;
    onClose: () => void;
    uploadUrl: string;
    title?: string;
    onUploadSuccess?: (result: any) => void;
}

// Props pour Modal
interface ModaleProps {
    isOpen: boolean;
    onClose: () => void;
    title: string;
    children: React.ReactNode;
    uploading?: boolean;
}

interface TypeFormData {
    pictures: FileList | null;
}

const __MAXFILESIZE = 6;

const Modale = ({ isOpen, onClose, children, title, uploading }: ModaleProps) => {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
            {/* Overlay */}
            <div className="bg-opacity-50 fixed inset-0 bg-black/80 transition-opacity" onClick={onClose} onDragOver={(e) => e.preventDefault()} />

            {/* Contenu de la modale */}
            <div className="bg-background relative z-10 mx-4 w-full max-w-lg rounded-lg shadow-xl">
                {/* Header */}

                <div className="border-foreground flex items-center justify-between border-b p-4">
                    <h3 className="text-lg font-semibold">{title}</h3>
                    <Button onClick={onClose} disabled={uploading} className="hover:bg-foreground/80 rounded-full transition-colors" type="button">
                        <X size={20} className="" />
                    </Button>
                </div>

                {/* Contenu */}
                <div className="p-4">{children}</div>
            </div>
        </div>
    );
};

export default function ImageUploadModale({ isOpen, onClose, uploadUrl, onUploadSuccess, title = 'Upload image' }: ImageUploadModalProps) {
    const [previews, setPreviews] = useState<{ url: string; name: string }[] | null>(null);
    const [uploading, setUploading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const { showToast } = useToast();
    const fileInputRef = useRef(null);
    const { data, setData } = useForm<TypeFormData>({
        pictures: [],
    });

    const handleFileSelect = (files: FileList | null) => {
        if (!files) return;

        if (files?.length > 3) {
            setData('pictures', null);
            return setError('Max 3 files');
        }

        if (files?.length > 0) {
            setData('pictures', files);
            setError(null);
            const urls = Array.from(files).map((file) => ({ url: URL.createObjectURL(file), name: file.name }));
            setPreviews(urls);

            // Nettoyage
            return () => urls.forEach((url) => URL.revokeObjectURL(url));
        }
    };

    const handleUpload = async () => {
        // if (!selectedFile) return;

        setUploading(true);
        setError(null);

        try {
            // const formData = new FormData();
            // formData.append('pictures', [selectedFile]);

            // console.log(formData);

            const response = await axios.post(uploadUrl, data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            showToast(response.data.message, response.data.status);

            const result = response.data.message;

            // Notifier le succès au composant parent
            if (onUploadSuccess) {
                onUploadSuccess(result);
            }

            // Réinitialiser et fermer
            handleClose();
        } catch (err) {
            showToast(err.response.data.message, err.response.data.status);
            //   throw new Error(`Erreur d'upload: ${response.status}`);
            setError(err.message || "Erreur lors de l'upload");
        } finally {
            setUploading(false);
        }
    };

    const handleClose = () => {
        setData('pictures', null);
        setPreviews(null);
        setError(null);
        setUploading(false);
        onClose();
    };

    const dropZoneRef = useRef<HTMLDivElement>(null);

    useEffect(() => {
        const element = dropZoneRef.current;
        if (!element) return;

        const handleDragOver = (e: DragEvent) => {
            e.preventDefault();
            e.dataTransfer!.dropEffect = 'copy';
        };

        const handleDrop = (e: DragEvent) => {
            e.preventDefault();
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFileSelect(files);
            }
        };

        element.addEventListener('dragover', handleDragOver);
        element.addEventListener('drop', handleDrop);

        return () => {
            element.removeEventListener('dragover', handleDragOver);
            element.removeEventListener('drop', handleDrop);
        };
    }, []);

    return (
        <Modale isOpen={isOpen} onClose={handleClose} title={title} uploading={uploading}>
            <div className="space-y-4">
                {/* Zone de drop/sélection */}

                <div
                    className="hover:border-foreground border-foreground/30 cursor-pointer rounded-lg border-2 border-dashed p-2 text-center transition-colors"
                    onClick={() => fileInputRef.current?.click()}
                    ref={dropZoneRef}
                >
                    {previews ? (
                        <div className="pointer-events-none flex flex-wrap items-center justify-evenly gap-2">
                            {previews.map((preview, index) => (
                                <div className="max-w-1/4" key={index}>
                                    <img src={preview.url} alt="Aperçu" className="mx-auto aspect-square max-h-40 rounded object-cover" />
                                    <p className="text-xs">{preview?.name}</p>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <div className="pointer-events-none space-y-2">
                            <ImageIcon size={48} className="mx-auto" />
                            <p className="text-gray-600">Click to select or drag & drop a picture</p>
                            <p className="text-sm text-gray-500">PNG, JPG, JPEG up to 5MB</p>
                        </div>
                    )}
                </div>

                {/* Input file caché */}
                <input
                    ref={fileInputRef}
                    type="file"
                    multiple
                    max={3}
                    accept="image/png, image/jpeg, image/jpg"
                    onChange={(e) => handleFileSelect(e.target.files)}
                    className="pointer-events-none hidden"
                />

                {/* Erreur */}
                {error && <div className="rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">{error}</div>}

                {/* Boutons */}
                <div className="flex gap-3 pt-4">
                    <Button onClick={handleClose} disabled={uploading} variant={'secondary'}>
                        Annuler
                    </Button>
                    <Button onClick={handleUpload} disabled={data.pictures === null || data.pictures?.length == 0 || uploading}>
                        {uploading ? (
                            <>
                                <div className="h-4 w-4 animate-spin rounded-full border-2 border-white border-t-transparent" />
                                Upload...
                            </>
                        ) : (
                            <>
                                <Upload size={16} />
                                Uploader
                            </>
                        )}
                    </Button>
                </div>
            </div>
        </Modale>
    );
}
