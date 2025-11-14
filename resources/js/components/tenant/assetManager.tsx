import { Asset } from '@/types';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useEffect, useState } from 'react';

interface AssetManagerProps {
    itemCode: string | number;
    type: string;
}

export const AssetManager = ({ itemCode, type }: AssetManagerProps) => {
    const { t, tChoice } = useLaravelReactI18n();
    const [assets, setAssets] = useState<Asset[]>();

    const fetchAssets = async () => {
        try {
            const response = await axios.get(route(`api.${type}.assets`, itemCode));
            setAssets(response.data.data.data);
        } catch (error) {
            console.log(error);
        }
    };

    useEffect(() => {
        fetchAssets();
    }, []);

    return (
        <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
            <h2>{tChoice('assets.title', 2)}</h2>
            <ul>
                {assets &&
                    assets.map((asset, index) => (
                        <li key={index}>
                            <a href={route('tenant.assets.show', asset.reference_code ?? asset.maintainable_id)}>
                                {asset.code} - {asset.maintainable.name}
                            </a>
                        </li>
                    ))}
            </ul>
        </div>
    );
};
