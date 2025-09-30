import { cn } from "@/lib/utils";
import { TenantBuilding, TenantFloor, TenantRoom, TenantSite } from "@/types";
import axios from "axios";
import { useEffect, useState } from "react";


type SearchLocationForAssetProps = {
    onSelect: (location: TenantBuilding | TenantFloor | TenantSite | TenantRoom, type?: string) => void;
};

export default function SearchLocationForAsset({ onSelect }: SearchLocationForAssetProps) {
    const [buildings, setBuildings] = useState<Record<number, TenantBuilding[]> | null>(null);
    const [floors, setFloors] = useState<Record<number, TenantFloor[]> | null>(null);
    const [rooms, setRooms] = useState<Record<number, TenantRoom[]> | null>(null);

    const fetchSites = async () => {
        try {
            const response = await axios.get(route('api.sites.index'));
            setSites(response.data.data);
        } catch (error) {
            console.log(error);
        }
    };

    const handleClick = (location, type) => {
        onSelect(location, type);
    };

    const [sites, setSites] = useState<TenantSite[] | null>(null);

    useEffect(() => {
        fetchSites();
    }, []);

    const fetchBuildings = async (id: number) => {
        try {
            const response = await axios.get(route('api.buildings.index', { site: id }));
            setBuildings({[id]: response.data.data});
        } catch (error) {
            console.log(error);
        }
    };

    const fetchFloors = async (id: number) => {
        try {
            const response = await axios.get(route('api.floors.index', { building: id }));
            setFloors({ [id]: response.data.data });
        } catch (error) {
            console.log(error);
        }
    };

    const fetchRooms = async (id: number) => {
        try {
            const response = await axios.get(route('api.rooms.index', { floor: id }));
            setRooms({ [id]: response.data.data });
        } catch (error) {
            console.log(error);
        }
    };

    return (
        <div className="text-sm">
            <ul>
                {sites?.map((site) => (
                    <li
                        key={site.id}
                        onClick={(e) => {
                            e.stopPropagation();
                            fetchBuildings(site.id);
                            handleClick(site, 'site');
                        }}
                        id={`site-${site.id}`}
                        className={cn(
                            parseInt(Object.keys({ ...buildings })[0]) === site.id
                                ? 'bg-accent/15 first-line:font-bold'
                                : 'bg-secondary hover:bg-accent',
                            'cursor-pointer border-b p-2 first:border-t',
                        )}
                    >
                        {site.name} - {site.reference_code}
                        {buildings && buildings[site.id] && (
                            <ul>
                                {buildings[site.id].map((building) => (
                                    <li
                                        key={building.id}
                                        className={cn(
                                            parseInt(Object.keys({ ...floors })[0]) === building.id
                                                ? 'bg-accent/35 first-line:font-bold'
                                                : 'hover:bg-accent',
                                            'cursor-pointer border-b p-2 pl-3 last-of-type:border-none',
                                        )}
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            fetchFloors(building.id);
                                            handleClick(building, 'building');
                                        }}
                                    >
                                        {building.name} - {building.reference_code}
                                        {floors && floors[building.id] && (
                                            <ul>
                                                {floors[building.id].map((floor) => (
                                                    <li
                                                        key={floor.id}
                                                        className={cn(
                                                            parseInt(Object.keys({ ...rooms })[0]) === floor.id
                                                                ? 'bg-accent/55 first-line:font-bold'
                                                                : 'hover:bg-accent',
                                                            'cursor-pointer border-b p-2 pl-4 last-of-type:border-none',
                                                        )}
                                                        onClick={(e) => {
                                                            e.stopPropagation();
                                                            fetchRooms(floor.id);
                                                            handleClick(floor, 'floor');
                                                        }}
                                                    >
                                                        {floor.name} - {floor.reference_code}
                                                        {rooms && rooms[floor.id] && (
                                                            <ul>
                                                                {rooms[floor.id].map((room) => (
                                                                    <li
                                                                        key={room.id}
                                                                        onClick={(e) => {
                                                                            e.stopPropagation();
                                                                            handleClick(room, 'room');
                                                                        }}
                                                                        className="bg-accent/90 hover:bg-secondary cursor-pointer border-b p-2 pl-5 last-of-type:border-none"
                                                                    >
                                                                        {room.name} - {room.reference_code}
                                                                    </li>
                                                                ))}
                                                            </ul>
                                                        )}
                                                    </li>
                                                ))}
                                            </ul>
                                        )}
                                    </li>
                                ))}
                            </ul>
                        )}
                    </li>
                ))}
            </ul>
        </div>
    );
}