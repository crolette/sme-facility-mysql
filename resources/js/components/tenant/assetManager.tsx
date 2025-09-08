import { Asset } from '@/types';
import axios from 'axios';
import { useEffect, useState } from 'react';

interface AssetManagerProps {
    itemCode: string;
    type: string;
}

export const AssetManager = ({ itemCode, type }: AssetManagerProps) => {
    const [assets, setAssets] = useState<Asset[]>();

    const fetchAssets = async () => {
        try {
            const response = await axios.get(route(`api.${type}.assets`, itemCode));
            setAssets(response.data.data);
        } catch (error) {
            console.log(error);
        }
    };

    useEffect(() => {
        fetchAssets();
    }, []);
    return (
        <div className="border-sidebar-border rounded-md border p-4">
            <h2>Assets</h2>
            <ul>
                {assets &&
                    assets.map((asset) => (
                        <li key={asset.reference_code}>
                            <a href={route('tenant.assets.show', asset.reference_code)}>
                                {asset.code} - {asset.maintainable.name}
                            </a>
                        </li>
                    ))}
            </ul>
        </div>
    );
};
