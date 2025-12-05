import { MeterReadings } from '@/types';
import { useForm } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { FormEventHandler, useState } from 'react';
import InputError from '../input-error';
import ModaleForm from '../ModaleForm';
import { useToast } from '../ToastrContext';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Label } from '../ui/label';

interface MeterReadingsProps {
    items: MeterReadings[] | undefined;
    assetCode: string;
}

export const MeterReadingsManager = ({ items, assetCode }: MeterReadingsProps) => {
    const { t, tChoice } = useLaravelReactI18n();
    const { showToast } = useToast();
    const [addUpdateMeter, setAddUpdateMeter] = useState<boolean>(false);
    const [meterToUpdate, setMeterToUpdate] = useState<MeterReadings | null>(null);
    const [isProcessing, setIsProcessing] = useState<boolean>(false);

    const { data, setData, errors, reset } = useForm<MeterReadings>({
        meter: meterToUpdate?.meter ?? '',
        meter_date: meterToUpdate?.meter_date ?? '',
        notes: meterToUpdate?.notes ?? '',
    });

    const handleSubmitMeterReadings: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);
        // if (meterToUpdate) {
        //     try {
        //         const response = await axios.post(route('api.assets.meter-readings.update', meterToUpdate.id), data);
        //         showToast(response.data.message);
        //     } catch (error) {
        //         showToast(error.response.data.message, error.response.data.status);
        //     }
        // } else {
        try {
            const response = await axios.post(route('api.assets.meter-readings.store', assetCode), data);
            showToast(response.data.message);
            reset();
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        } finally {
            setAddUpdateMeter(false);
            setIsProcessing(false);
        }
        // }
    };

    return (
        <div>
            Meter Readings
            <ul>
                {items?.map((item: MeterReadings) => (
                    <li key={item.id}>
                        {item.meter} - {item.meter_date}
                    </li>
                ))}
            </ul>
            <Button onClick={() => setAddUpdateMeter(!addUpdateMeter)}>Add meter</Button>
            {addUpdateMeter && (
                <ModaleForm isUpdating={isProcessing}>
                    <p>Add meter</p>
                    <Label htmlFor="meter">{t('assets.selected_location')}</Label>
                    <Input
                        id="meter"
                        required
                        type="number"
                        value={data.meter ?? ''}
                        min={0}
                        step="0.01"
                        onChange={(e) => setData('meter', e.target.value)}
                    />
                    <InputError className="mt-2" message={errors?.meter ?? ''} />

                    <Label htmlFor="meter_date">{t('assets.selected_location')}</Label>
                    <Input
                        id="meter_date"
                        required
                        type="date"
                        defaultValue={Date.now()}
                        value={data.meter_date ?? ''}
                        onChange={(e) => setData('meter_date', e.target.value)}
                    />
                    <InputError className="mt-2" message={errors?.meter_date ?? ''} />

                    <Label htmlFor="notes">{t('assets.selected_location')}</Label>
                    <Input id="notes" type="text" value={data.notes ?? ''} onChange={(e) => setData('notes', e.target.value)} />
                    <InputError className="mt-2" message={errors?.notes ?? ''} />

                    <Button onClick={handleSubmitMeterReadings}>Submit</Button>
                    <Button onClick={() => setAddUpdateMeter(!addUpdateMeter)}>Cancel</Button>
                </ModaleForm>
            )}
        </div>
    );
};
