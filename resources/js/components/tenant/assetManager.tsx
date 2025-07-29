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
        <>
            <p>Assets</p>
            <ul>
                {assets &&
                    assets.map((asset) => (
                        <li key={asset.code}>
                            <a href={route('tenant.assets.show', asset.code)}>
                                {asset.code} - {asset.maintainable.name}
                            </a>
                        </li>
                    ))}
            </ul>
        </>
    );
};
