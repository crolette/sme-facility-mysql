import { Picture } from '@/types';
import axios from 'axios';
import { FormEventHandler, useEffect, useState } from 'react';
import { Button } from '../ui/button';
import { useToast } from '../ToastrContext';
import { PlusCircle, Trash2 } from 'lucide-react';

interface PictureManagerProps {
    itemCodeId: number | string;
    getPicturesUrl: string;
    uploadRoute: string;
    deleteRoute: string;
    showRoute: string;
    canAdd?: boolean;
}

export const PictureManager = ({ itemCodeId, getPicturesUrl, uploadRoute, deleteRoute, showRoute, canAdd = true }: PictureManagerProps) => {
    const { showToast } = useToast();
    const [pictures, setPictures] = useState<Picture[]>([]);
    const [newPictures, setNewPictures] = useState<{ pictures: File[] } | null>(null);
    const [addPictures, setAddPictures] = useState(false);

    useEffect(() => {
        fetchPictures();
    }, []);

    const fetchPictures = async () => {
        try {
            const response = await axios.get(route(getPicturesUrl, itemCodeId));
            setPictures(response.data.data);
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

    const postNewPictures: FormEventHandler = async (e) => {
        e.preventDefault();
        try {
            const response = await axios.post(route(uploadRoute, itemCodeId), newPictures, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            if (response.data.status === 'success') {
                fetchPictures();
                setNewPictures(null);
                setAddPictures(!addPictures);
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    return (
        <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
            <div className="flex items-center justify-between">
                <h2 className="inline">Pictures ({pictures?.length})</h2>
                {canAdd && (
                    <Button onClick={() => setAddPictures(!addPictures)} type="button">
                        <PlusCircle />
                        Add pictures
                    </Button>
                )}
            </div>
            <div className="flex flex-wrap gap-4">
                {pictures &&
                    pictures.length > 0 &&
                    pictures.map((picture, index) => {
                        return (
                            <div key={index} className="w-32 relative">
                                <a href={route('api.file.download', { path: picture.path })} download className="w-fit cursor-pointer">
                                    <img src={route(showRoute, picture.id)} className="aspect-square object-cover" alt={picture.filename} />
                                </a>
                                <Button className='absolute top-2 right-2' variant={'destructive'} onClick={() => deletePicture(picture.id)}>
                                    <Trash2 />
                                </Button>
                            </div>
                        );
                    })}
            </div>

            {addPictures && (
                <div className="bg-background/50 absolute inset-0 z-50">
                    <div className="bg-background/20 flex h-dvh items-center justify-center">
                        <div className="bg-background flex flex-col gap-4 items-center justify-center p-4">
                        <p className='font-semibold'>Add new pictures</p>
                            <form onSubmit={postNewPictures} className="flex flex-col gap-4">
                                <input
                                    type="file"
                                    multiple
                                    onChange={(e) => {
                                        if (e.target.files) {
                                            setNewPictures({ pictures: Array.from(e.target.files) });
                                        }
                                    }}
                                    accept="image/png, image/jpeg, image/jpg"
                                />
                                <div className='space-x-4 flex justify-between'>
                                    <Button disabled={newPictures == null}>Add new pictures</Button>
                                    <Button
                                        onClick={() => {
                                            setNewPictures(null);
                                            setAddPictures(false);
                                        }}
                                        type="button"
                                        variant={'secondary'}
                                    >
                                        Cancel
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};
