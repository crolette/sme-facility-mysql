import { cn } from '@/lib/utils';
import { Asset, LocationType, TenantRoom } from '@/types';
import { useForm } from '@inertiajs/react';
import axios from 'axios';
import { FormEventHandler, useEffect, useState } from 'react';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Label } from '../ui/label';

type RelocateRoomFormData = {
    locationType: null | number;
    assets: {
        change: string;
        assetId: number;
        locationType: string;
        locationId: number;
        locationName: null | string;
        locationCode: null | string;
    }[];
};

type SearchedLocation = {
    id: number;
    type: string;
    name: string;
    reference_code: string;
    code: string;
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
                        change: 'follow',
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

    const changeTypes = ['follow', 'change', 'delete'];

    useEffect(() => {
        fetchAssets();
        fetchTypes();
    }, []);

    const { get } = useForm();

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        console.log('submit');
        const response = await axios.patch(route('api.rooms.relocate', room.reference_code), newRoomData);
        console.log(response.data, response.data.data.reference_code);
        onClose();
        get(route('tenant.rooms.show', response.data.data.reference_code));
    };

    console.log(newRoomData);

    const [listIsOpen, setListIsOpen] = useState(false);
    const [isSearching, setIsSearching] = useState(false);
    const [locations, setLocations] = useState<SearchedLocation[]>();
    const [search, setSearch] = useState<string>('');
    const [debouncedSearch, setDebouncedSearch] = useState(search);

    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedSearch(search);
        }, 500);

        return () => {
            clearTimeout(handler);
        };
    }, [search]);

    useEffect(() => {
        if (debouncedSearch.length < 2) {
            setLocations([]);
        }
        if (debouncedSearch.length >= 2) {
            setIsSearching(true);
            setListIsOpen(true);
            const fetchData = async () => {
                try {
                    const response = await axios.get(route('api.locations', { q: debouncedSearch, type: 'room' }));
                    setLocations(response.data.data);
                    setIsSearching(false);
                    setListIsOpen(true);
                } catch (error) {
                    console.error('Erreur lors de la recherche :', error);
                }
            };

            if (debouncedSearch) {
                fetchData();
            }
        }
    }, [debouncedSearch]);

    console.log(locations);

    return (
        <>
            <div className="bg-background/90 absolute inset-0 z-50">
                <div className="flex h-dvh items-center justify-center">
                    <div className="flex w-full items-center justify-center p-4">
                        <div className="bg-background flex w-2/3 flex-col gap-2 p-4">
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
                                <ul>
                                    {assets &&
                                        assets.length > 0 &&
                                        assets.map((asset, index) => (
                                            <li key={index}>
                                                <div className="flex">
                                                    <p>
                                                        {asset.code} - {asset.name} - {asset.description}
                                                    </p>
                                                    <p>
                                                        {asset.location.code} - {asset.location.name}
                                                    </p>
                                                </div>
                                                <select
                                                    name="change"
                                                    required
                                                    value={newRoomData.assets[index].change}
                                                    onChange={(e) => {
                                                        const value = e.target.value;
                                                        setNewRoomData((prev) => {
                                                            const updatedAssets = [...prev.assets];
                                                            updatedAssets[index] = {
                                                                ...updatedAssets[index],
                                                                change: value,
                                                            };

                                                            return {
                                                                ...prev,
                                                                assets: updatedAssets,
                                                            };
                                                        });
                                                    }}
                                                    id=""
                                                    className={cn(
                                                        'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                                        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                                    )}
                                                >
                                                    {changeTypes && (
                                                        <>
                                                            <option value="" disabled className="bg-background text-foreground">
                                                                Select an option
                                                            </option>
                                                            {changeTypes?.map((changeType, index) => (
                                                                <option value={changeType} key={index} className="bg-background text-foreground">
                                                                    {changeType}
                                                                </option>
                                                            ))}
                                                        </>
                                                    )}
                                                </select>
                                                {newRoomData.assets[index].change === 'change' && (
                                                    <>
                                                        <div className="relative">
                                                            <Input
                                                                type="search"
                                                                value={search.length > 0 ? search : newRoomData.assets[index].locationName}
                                                                onChange={(e) => setSearch(e.target.value)}
                                                                placeholder="Search by code or name"
                                                            />
                                                            <ul
                                                                className="bg-background absolute z-10 flex w-full flex-col border"
                                                                aria-autocomplete="list"
                                                                role="listbox"
                                                            >
                                                                {isSearching && (
                                                                    <li value="0" key="" className="">
                                                                        Searching...
                                                                    </li>
                                                                )}
                                                                {listIsOpen &&
                                                                    locations &&
                                                                    locations.length > 0 &&
                                                                    locations?.map((location) => (
                                                                        <li
                                                                            role="option"
                                                                            value={location.reference_code}
                                                                            key={location.reference_code}
                                                                            onClick={() => {
                                                                                setNewRoomData((prev) => {
                                                                                    const updatedAssets = [...prev.assets];
                                                                                    updatedAssets[index] = {
                                                                                        ...updatedAssets[index],
                                                                                        locationId: location.id,
                                                                                        locationType: location.type,
                                                                                        locationName: location.name,
                                                                                        locationCode: location.reference_code,
                                                                                    };

                                                                                    return {
                                                                                        ...prev,
                                                                                        assets: updatedAssets,
                                                                                    };
                                                                                });
                                                                                setSearch('');
                                                                                setListIsOpen(false);
                                                                                setLocations([]);
                                                                            }}
                                                                            // onClick={() => setSelectedLocation(location)}
                                                                            className="hover:bg-foreground hover:text-background cursor-pointer p-2 text-sm"
                                                                        >
                                                                            {location.name + ' (' + location.reference_code + ')'}
                                                                        </li>
                                                                    ))}
                                                            </ul>
                                                        </div>
                                                    </>
                                                )}
                                            </li>
                                        ))}
                                </ul>
                                <Button>Submit</Button>
                                <Button onClick={onClose} type="button">
                                    Cancel
                                </Button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
