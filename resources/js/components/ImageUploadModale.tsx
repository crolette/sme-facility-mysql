import axios from 'axios';
import { ImageIcon, Upload, X } from 'lucide-react';
import { useRef, useState } from 'react';
import { Button } from './ui/button';
import { useToast } from './ToastrContext';

// Props pour ImageUploadModal
interface ImageUploadModalProps {
    isOpen: boolean;
    onClose: () => void;
    uploadUrl: string;
    title?: string;
    onUploadSuccess?: (result: any) => void; // ou un type plus spécifique selon votre API
}

// Props pour Modal
interface ModaleProps {
    isOpen: boolean;
    onClose: () => void;
    title: string;
    children: React.ReactNode;
}

const Modale = ({ isOpen, onClose, children, title }: ModaleProps) => {
    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 flex items-center justify-center">
            {/* Overlay */}
            <div className="bg-opacity-50 fixed inset-0 bg-black/80 transition-opacity" onClick={onClose} />

            {/* Contenu de la modale */}
            <div className="bg-background relative z-10 mx-4 w-full max-w-md rounded-lg shadow-xl">
                {/* Header */}
                <div className="border-foreground flex items-center justify-between border-b p-4">
                    <h3 className="text-lg font-semibold">{title}</h3>
                    <Button onClick={onClose} className="hover:bg-foreground/80 rounded-full transition-colors" type="button">
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
    const [selectedFile, setSelectedFile] = useState<File | null>(null);
    const [preview, setPreview] = useState(null);
    const [uploading, setUploading] = useState(false);
    const [error, setError] = useState<string | null>(null);
    const { showToast } = useToast();
    const fileInputRef = useRef(null);

    const handleFileSelect = (files: FileList | null) => {
        const file = files?.[0];
        if (file) {
            const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];

            if (!allowedTypes.includes(file.type)) {
                setError('Select a picture (JPG, JPEG, PNG).');
                return;
            }

            // Vérifier la taille (par exemple, max 4MB)
            if (file.size > 4 * 1024 * 1024) {
                setError('File is too big (max 4MB).');
                return;
            }

            setSelectedFile(file);
            setError(null);

            // Créer un aperçu
            const reader = new FileReader();
            reader.onload = (e) => setPreview(e.target.result);
            reader.readAsDataURL(file);
        }
    };

    const handleUpload = async () => {
        if (!selectedFile) return;

        setUploading(true);
        setError(null);

        try {
            const formData = new FormData();
            formData.append('image', selectedFile);

            const response = await axios.post(uploadUrl, formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            showToast(response.data.message, response.data.status);

            if (response.data.status == 'error') {
                 showToast(response.data.message, response.data.status);
                throw new Error(`Erreur d'upload: ${response.status}`);
            }

            const result = await response.data.message;

            // Notifier le succès au composant parent
            if (onUploadSuccess) {
                onUploadSuccess(result);
            }

            // Réinitialiser et fermer
            handleClose();
        } catch (err) {
            setError(err.message || "Erreur lors de l'upload");
        } finally {
            setUploading(false);
        }
    };

    const handleClose = () => {
        setSelectedFile(null);
        setPreview(null);
        setError(null);
        setUploading(false);
        onClose();
    };

    const handleDragOver = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
    };

    const handleDrop = (e: React.DragEvent<HTMLDivElement>) => {
        e.preventDefault();
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            const event = { target: { files } };
            handleFileSelect(event);
        }
    };

    return (
        <Modale isOpen={isOpen} onClose={handleClose} title={title}>
            <div className="space-y-4">
                {/* Zone de drop/sélection */}
                <div
                    className="hover:border-foreground border-foreground/30 cursor-pointer rounded-lg border-2 border-dashed p-6 text-center transition-colors"
                    onClick={() => fileInputRef.current?.click()}
                    onDragOver={handleDragOver}
                    onDrop={handleDrop}
                >
                    {preview ? (
                        <div className="space-y-2">
                            <img src={preview} alt="Aperçu" className="mx-auto max-h-40 rounded" />
                            <p className="text-sm">{selectedFile?.name}</p>
                        </div>
                    ) : (
                        <div className="space-y-2">
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
                    accept="image/png, image/jpeg, image/jpg"
                    onChange={(e) => handleFileSelect(e.target.files)}
                    className="hidden"
                />

                {/* Erreur */}
                {error && <div className="rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">{error}</div>}

                {/* Boutons */}
                <div className="flex gap-3 pt-4">
                    <Button onClick={handleClose} disabled={uploading} variant={'secondary'}>
                        Annuler
                    </Button>
                    <Button onClick={handleUpload} disabled={!selectedFile || uploading}>
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
