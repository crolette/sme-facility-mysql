import axios from 'axios';
import { useEffect, useState } from 'react';

interface LocationListProps {
    itemCode: string | number;
    type: string;
    getUrl: string;
}

export const LocationList = ({ itemCode, type, getUrl }: LocationListProps) => {
    const [locations, setLocations] = useState<[]>();

    const fetchLocations = async () => {
        try {
            const response = await axios.get(route(getUrl, itemCode));
            console.log(response);
            setLocations(response.data.data.data);
        } catch (error) {
            console.log(error);
        }
    };

    useEffect(() => {
        fetchLocations();
    }, []);

    console.log(locations);

    return (
        <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
            <h2>Locations</h2>
            <ul>
                {locations &&
                    locations.map((location, index) => (
                        <li key={index}>
                            {/* <a href={route('tenant.assets.show', asset.reference_code ?? asset.maintainable_id)}> */}
                            {location.code ?? location.maintainable.code} - {location.maintainable.name ?? location.name}
                            {/* </a> */}
                        </li>
                    ))}
            </ul>
        </div>
    );
};
