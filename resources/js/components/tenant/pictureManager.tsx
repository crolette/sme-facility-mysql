import { Picture } from '@/types';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Loader, PlusCircle, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import ImageUploadModale from '../ImageUploadModale';
import { useToast } from '../ToastrContext';
import { Button } from '../ui/button';

interface PictureManagerProps {
    itemCodeId: number | string;
    getPicturesUrl: string;
    uploadRoute: string;
    deleteRoute: string;
    showRoute: string;
    canAdd?: boolean;
}

export const PictureManager = ({ itemCodeId, getPicturesUrl, uploadRoute, deleteRoute, showRoute, canAdd = true }: PictureManagerProps) => {
    const { t, tChoice } = useLaravelReactI18n();
    const { showToast } = useToast();
    const [pictures, setPictures] = useState<Picture[] | null>(null);
    const [isLoading, setIsLoading] = useState(true);
    const [newPictures, setNewPictures] = useState<{ pictures: File[] } | null>(null);
    const [addPictures, setAddPictures] = useState(false);

    useEffect(() => {
        fetchPictures();
    }, []);

    const fetchPictures = async () => {
        try {
            const response = await axios.get(route(getPicturesUrl, itemCodeId));
            setPictures(response.data.data);
            setIsLoading(false);
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const deletePicture = async (id: number) => {
        try {
            const response = await axios.delete(route(deleteRoute, id));
            if (response.data.status === 'success') {
                fetchPictures();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    // const postNewPictures: FormEventHandler = async (e) => {
    //     e.preventDefault();
    //     try {
    //         const response = await axios.post(route(uploadRoute, itemCodeId), newPictures, {
    //             headers: {
    //                 'Content-Type': 'multipart/form-data',
    //             },
    //         });
    //         if (response.data.status === 'success') {
    //             fetchPictures();
    //             setNewPictures(null);
    //             setAddPictures(!addPictures);
    //             showToast(response.data.message, response.data.status);
    //         }
    //     } catch (error) {
    //         showToast(error.response.data.message, error.response.data.status);
    //     }
    // };

    return (
        <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
            <div className="flex items-center justify-between">
                <h2 className="inline">
                    {tChoice('common.pictures', 2)} ({pictures?.length})
                </h2>
                {canAdd && (
                    <Button onClick={() => setAddPictures(!addPictures)} type="button">
                        <PlusCircle />
                        {t('actions.add-type', { type: tChoice('common.pictures', 1) })}
                    </Button>
                )}
            </div>
            <div className="flex flex-wrap gap-4">
                {isLoading ? (
                    <p className="flex animate-pulse gap-2">
                        <Loader />
                        {t('actions.loading')}
                    </p>
                ) : pictures !== null && pictures.length > 0 ? (
                    pictures.map((picture, index) => {
                        return (
                            <div key={index} className="relative w-32">
                                <a href={route('api.file.download', { path: picture.path })} download className="w-fit cursor-pointer">
                                    <img src={route(showRoute, picture.id)} className="aspect-square object-cover" alt={picture.filename} />
                                </a>
                                <Button className="absolute top-2 right-2" variant={'destructive'} onClick={() => deletePicture(picture.id)}>
                                    <Trash2 />
                                </Button>
                            </div>
                        );
                    })
                ) : (
                    <p>No pictures</p>
                )}
            </div>

            <ImageUploadModale
                isOpen={addPictures}
                onClose={() => {
                    setNewPictures(null);
                    setAddPictures(false);
                }}
                uploadUrl={route(uploadRoute, itemCodeId)}
                onUploadSuccess={fetchPictures}
            />
        </div>
    );
};
