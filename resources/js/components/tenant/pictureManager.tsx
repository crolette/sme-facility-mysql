import { Picture } from '@/types';
import axios from 'axios';
import { FormEventHandler, useEffect, useState } from 'react';
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
            console.error('Erreur lors de la recherche :', error);
        }
    };

    const deletePicture = async (id: number) => {
        try {
            const response = await axios.delete(route(deleteRoute, id));
            if (response.data.status === 'success') {
                fetchPictures();
            }
        } catch (error) {
            console.error('Erreur lors de la suppression', error);
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
            }
        } catch (error) {
            console.log(error);
        }
    };

    return (
        <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
            <h2 className="inline">Pictures ({pictures?.length})</h2>
            {canAdd && (
                <Button onClick={() => setAddPictures(!addPictures)} type="button">
                    Add pictures
                </Button>
            )}
            <div className="flex flex-wrap gap-4">
                {pictures &&
                    pictures.length > 0 &&
                    pictures.map((picture, index) => {
                        return (
                            <div key={index} className="w-32">
                                <a href={route(showRoute, picture.id)} download className="w cursor-pointer">
                                    <img src={route(showRoute, picture.id)} className="aspect-square object-cover" alt={picture.filename} />
                                </a>
                                <Button variant={'destructive'} onClick={() => deletePicture(picture.id)}>
                                    Delete
                                </Button>
                            </div>
                        );
                    })}
            </div>

            {addPictures && (
                <div className="bg-background/50 absolute inset-0 z-50">
                    <div className="bg-background/20 flex h-dvh items-center justify-center">
                        <div className="bg-background flex items-center justify-center p-4">
                            <form onSubmit={postNewPictures}>
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
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};
