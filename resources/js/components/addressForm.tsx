import { Address, CentralCountry } from '@/types';
import { Input } from './ui/input';
import { Label } from './ui/label';

type AddressFormProps = {
    idPrefix: string; // ex: "company" ou "invoice"
    address: Address;
    countries: CentralCountry[];
    onChange: (updated: Address) => void;
};

export const AddressForm = ({ idPrefix, address, onChange, countries }: AddressFormProps) => {
    const updateField = (field: keyof Address) => (e: React.ChangeEvent<HTMLInputElement>) => {
        onChange({
            ...address,
            [field]: e.target.value,
        });
    };

    return (
        <>
            <Label htmlFor={`${idPrefix}_street`}>Street</Label>
            <Input
                id={`${idPrefix}_street`}
                value={address?.street ?? ''}
                required
                onChange={updateField('street')}
                placeholder="Rue de la facilité"
            />

            <Label htmlFor={`${idPrefix}_house_number`}>House Number</Label>
            <Input
                id={`${idPrefix}_house_number`}
                value={address?.house_number ?? ''}
                required
                onChange={updateField('house_number')}
                placeholder="14"
            />

            <Label htmlFor={`${idPrefix}_zip_code`}>Zip Code</Label>
            <Input id={`${idPrefix}_zip_code`} value={address?.zip_code ?? ''} required onChange={updateField('zip_code')} placeholder="1234" />

            <Label htmlFor={`${idPrefix}_city`}>City</Label>
            <Input id={`${idPrefix}_city`} value={address?.city ?? ''} required onChange={updateField('city')} placeholder="Web City" />

            <div className="flex flex-col">
                <Label htmlFor={`${idPrefix}_country`}>Country</Label>
                <select name="country" id="country" value={address.country} onChange={updateField('country')}>
                    <option value="">Select country</option>
                    {countries.map((country: CentralCountry) => (
                        <option key={country.iso_code_a2} value={country.iso_code_a2}>
                            {country.name}
                        </option>
                    ))}
                </select>
            </div>
            {/* <Input id={`${idPrefix}_country`} value={address?.country ?? ''} required onChange={updateField('country')} placeholder="Magic Land" /> */}
        </>
    );
};
