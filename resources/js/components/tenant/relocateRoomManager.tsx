import { cn } from '@/lib/utils';
import { Asset, LocationType, TenantRoom } from '@/types';
import axios from 'axios';
import { FormEventHandler, useEffect, useState } from 'react';
import { Button } from '../ui/button';
import { Label } from '../ui/label';

type RelocateRoomFormData = {
    locationType: null | number;
    assets: {
        assetId: number;
        locationType: string;
        locationId: number;
    }[];
};

export default function RealocateRoomManager({ room, itemCode, onClose }: { room: TenantRoom; itemCode: string; onClose: () => void }) {
    const [assets, setAssets] = useState<Asset[]>();
    const relocateRoomData = {
        locationType: room.location_type.id ?? null,
        assets: [],
    };
    const [newRoomData, setNewRoomData] = useState<RelocateRoomFormData>(relocateRoomData);

    const [locationTypes, setLocationTypes] = useState<LocationType[]>();
    const fetchAssets = async () => {
        try {
            const response = await axios.get(route(`api.rooms.assets`, itemCode));
            setAssets(response.data.data);
            setNewRoomData((prev) => ({
                ...prev,
                assets: response.data.data.map((asset) => {
                    const data = {
                        assetId: asset.id,
                        locationType: 'room',
                        locationId: asset.location_id,
                    };
                    return data;
                }),
            }));
        } catch (error) {
            console.log(error);
        }
    };

    const fetchTypes = async () => {
        try {
            const response = await axios.get(route(`api.location-types`, { level: 'room' }));
            setLocationTypes(response.data.data);
        } catch (error) {
            console.log(error);
        }
    };

    useEffect(() => {
        fetchAssets();
        fetchTypes();
    }, []);

    if (assets)
        console.log(
            assets.map((asset) => {
                const data = {
                    assetId: asset.id,
                    locationType: 'room',
                    locationId: asset.location_id,
                };
                return data;
            }),
        );

    console.log(room);

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        console.log('submit');
        const response = await axios.patch(route('api.rooms.relocate', room.code), newRoomData);
        console.log(response);
    };

    console.log(newRoomData);

    return (
        <>
            <div className="bg-background/50 absolute inset-0 z-50">
                <div className="bg-background/20 flex h-dvh items-center justify-center">
                    <div className="flex w-full items-center justify-center p-4">
                        <div className="flex w-2/3 flex-col gap-2">
                            <Button onClick={onClose} type="button">
                                Close
                            </Button>
                            <form onSubmit={submit}>
                                <Label>Location type</Label>
                                <select
                                    name="documentType"
                                    required
                                    value={newRoomData.locationType ?? room.location_type.id}
                                    onChange={(e) =>
                                        setNewRoomData((prev) => ({
                                            ...prev,
                                            locationType: parseInt(e.target.value),
                                        }))
                                    }
                                    id=""
                                    className={cn(
                                        'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                    )}
                                >
                                    {locationTypes && locationTypes.length > 0 && (
                                        <>
                                            <option value="" disabled className="bg-background text-foreground">
                                                Select an option
                                            </option>
                                            {locationTypes?.map((locationType) => (
                                                <option value={locationType.id} key={locationType.id} className="bg-background text-foreground">
                                                    {locationType.label}
                                                </option>
                                            ))}
                                        </>
                                    )}
                                </select>
                                <div></div>
                                <Button>Submit</Button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
