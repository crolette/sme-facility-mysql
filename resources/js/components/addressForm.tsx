import { Address } from '@/types';
import { Input } from './ui/input';
import { Label } from './ui/label';

type AddressFormProps = {
    idPrefix: string; // ex: "company" ou "invoice"
    address: Address;
    onChange: (updated: Address) => void;
};

export const AddressForm = ({ idPrefix, address, onChange }: AddressFormProps) => {
    const updateField = (field: keyof Address) => (e: React.ChangeEvent<HTMLInputElement>) => {
        onChange({
            ...address,
            [field]: e.target.value,
        });
    };

    return (
        <>
            <Label htmlFor={`${idPrefix}_street`}>Street</Label>
            <Input id={`${idPrefix}_street`} value={address?.street ?? ''} onChange={updateField('street')} placeholder="Rue de la facilité" />

            <Label htmlFor={`${idPrefix}_house_number`}>House Number</Label>
            <Input id={`${idPrefix}_house_number`} value={address?.house_number ?? ''} onChange={updateField('house_number')} placeholder="14" />

            <Label htmlFor={`${idPrefix}_zip_code`}>Zip Code</Label>
            <Input id={`${idPrefix}_zip_code`} value={address?.zip_code ?? ''} onChange={updateField('zip_code')} placeholder="1234" />

            <Label htmlFor={`${idPrefix}_city`}>City</Label>
            <Input id={`${idPrefix}_city`} value={address?.city ?? ''} onChange={updateField('city')} placeholder="Web City" />

            <Label htmlFor={`${idPrefix}_country`}>Country</Label>
            <Input id={`${idPrefix}_country`} value={address?.country ?? ''} onChange={updateField('country')} placeholder="Magic Land" />
        </>
    );
};
