import { MeterReadings } from '@/types';
import { useForm } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Pencil, PlusCircleIcon, Trash2 } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';
import InputError from '../input-error';
import ModaleForm from '../ModaleForm';
import { useToast } from '../ToastrContext';
import { Button } from '../ui/button';
import { Input } from '../ui/input';
import { Label } from '../ui/label';

interface MeterReadingsProps {
    items: MeterReadings[] | undefined;
    unit: string;
    assetCode: string;
}

type MeterReadingsFormData = {
    meter: number | string;
    meter_date: string;
    notes: string;
};

export const MeterReadingsManager = ({ items, unit, assetCode }: MeterReadingsProps) => {
    const { t, tChoice } = useLaravelReactI18n();
    const { showToast } = useToast();

    const [readings, setReadings] = useState(items);

    const [addUpdateMeter, setAddUpdateMeter] = useState<boolean>(false);
    const [meterToUpdate, setMeterToUpdate] = useState<MeterReadings | null>(null);
    const [meterToDelete, setMeterToDelete] = useState<MeterReadings | null>(null);
    const [deleteMeterModale, setDeleteMeterModale] = useState<boolean>(false);

    const [isProcessing, setIsProcessing] = useState<boolean>(false);

    const { data, setData, errors, reset } = useForm<MeterReadingsFormData>({
        meter: meterToUpdate?.meter ?? '',
        meter_date: meterToUpdate?.meter_date ?? new Date().toISOString().split('T')[0],
        notes: meterToUpdate?.notes ?? '',
    });

    const fetchMeterReadings = async () => {
        try {
            const response = await axios.get(route('api.meter-readings.index', assetCode));
            setReadings(response.data.data);
        } catch (error) {
            showToast(error.response.data.message);
        }
    };

    const handleDeleteMeterReadings = async () => {
        if (meterToDelete)
            try {
                const response = await axios.delete(route('api.meter-readings.delete', meterToDelete));
                showToast(response.data.message);
                setMeterToDelete(null);
            } catch (error) {
                console.log(error);
                showToast(error.response.data.message);
                // showToast(error.response.data.message, error.response.data.status);
            } finally {
                setIsProcessing(false);
                setDeleteMeterModale(false);
                setMeterToDelete(null);

                fetchMeterReadings();
            }
    };

    const handleSubmitMeterReadings: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);
        try {
            if (meterToUpdate) {
                const response = await axios.patch(route('api.meter-readings.patch', meterToUpdate.id), data);
                showToast(response.data.message);
            } else {
                const response = await axios.post(route('api.meter-readings.store', assetCode), data);
                showToast(response.data.message);
            }
            setAddUpdateMeter(false);
            setMeterToUpdate(null);
            reset();
            fetchMeterReadings();
        } catch (error) {
            showToast(error.response.data.message);
            // showToast(error.response.data.message, error.response.data.status);
        } finally {
            setIsProcessing(false);
        }
    };

    useEffect(() => {
        if (meterToUpdate)
            setData((prev) => ({ ...prev, meter: meterToUpdate?.meter, meter_date: meterToUpdate?.meter_date, notes: meterToUpdate?.notes }));
    }, [meterToUpdate]);

    return (
        <div>
            <div className="flex items-center gap-4">
                <Label>{t('assets.meter_readings.title')}</Label>
                <Button onClick={() => setAddUpdateMeter(!addUpdateMeter)} className="text-xs" size={'xs'}>
                    <PlusCircleIcon />
                    {/* {t('actions.add-type', { type: t('assets.meter_readings.meter') })} */}
                </Button>
            </div>
            <ul className="w-full">
                {readings?.map((item: MeterReadings, index) => (
                    <li key={item.id} className="odd:bg-accent flex items-center justify-between gap-10 p-2">
                        <p>
                            {item.meter} {unit} - {item.meter_date}
                        </p>
                        <div className="space-x-4">
                            {index === readings.length - 1 && (
                                <Button
                                    onClick={() => {
                                        setMeterToUpdate(item);
                                        setAddUpdateMeter(!addUpdateMeter);
                                    }}
                                    size="xs"
                                >
                                    <Pencil />
                                </Button>
                            )}
                            <Button
                                size="xs"
                                variant={'destructive'}
                                onClick={() => {
                                    setMeterToDelete(item);
                                    setDeleteMeterModale(!addUpdateMeter);
                                }}
                            >
                                <Trash2 />
                            </Button>
                        </div>
                    </li>
                ))}
            </ul>

            {addUpdateMeter && (
                <ModaleForm
                    isUpdating={isProcessing}
                    title={
                        meterToUpdate
                            ? t('actions.update-type', { type: t('assets.meter_readings.meter') })
                            : t('actions.add-type', { type: t('assets.meter_readings.meter') })
                    }
                >
                    <form onSubmit={handleSubmitMeterReadings} className="space-y-2">
                        <Label htmlFor="meter">{t('assets.meter_readings.meter')}</Label>
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

                        <Label htmlFor="meter_date">{t('common.date')}</Label>
                        <Input id="meter_date" required type="date" value={data.meter_date} onChange={(e) => setData('meter_date', e.target.value)} />
                        <InputError className="mt-2" message={errors?.meter_date ?? ''} />

                        <Label htmlFor="notes">{t('common.notes')}</Label>
                        <Input id="notes" type="text" value={data.notes ?? ''} onChange={(e) => setData('notes', e.target.value)} />
                        <InputError className="mt-2" message={errors?.notes ?? ''} />

                        <div className="space-x-2">
                            <Button type="submit" onClick={handleSubmitMeterReadings} disabled={isProcessing}>
                                {t('actions.submit')}
                            </Button>
                            <Button
                                onClick={() => {
                                    setAddUpdateMeter(!addUpdateMeter);
                                    reset();
                                }}
                                disabled={isProcessing}
                                variant={'secondary'}
                            >
                                {t('actions.cancel')}
                            </Button>
                        </div>
                    </form>
                </ModaleForm>
            )}
            {deleteMeterModale && (
                <ModaleForm isUpdating={isProcessing} title={''}>
                    <p>Are you sure ?</p>
                    <Button type="submit" onClick={handleDeleteMeterReadings} disabled={isProcessing}>
                        {t('actions.submit')}
                    </Button>
                    <Button
                        disabled={isProcessing}
                        onClick={() => {
                            setMeterToDelete(null);
                            setDeleteMeterModale(false);
                        }}
                        variant={'secondary'}
                    >
                        {t('actions.cancel')}
                    </Button>
                </ModaleForm>
            )}
        </div>
    );
};
