import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { NotificationPreference, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';

import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Toggle } from '@/components/ui/toggle';
import { cn } from '@/lib/utils';
import axios from 'axios';
import { Check, Pen, X } from 'lucide-react';
import { FormEventHandler, useEffect, useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Notification preferences',
        href: '/settings/notification-preferences',
    },
];

export default function NotificationPreferences({ items }: { items: NotificationPreference[] }) {
    const [preferenceDayToChange, setPreferenceDayToChange] = useState<NotificationPreference | null>(null);
    const [preferences, setPreferences] = useState(items);
    const [errors, setErrors] = useState(null);

    const { data, setData } = useForm({
        asset_type: null,
        notification_type: null,
        notification_delay_days: null,
        enabled: null,
    });

    useEffect(() => {
        if (preferenceDayToChange === null)
            setData({
                asset_type: null,
                notification_type: null,
                notification_delay_days: null,
                enabled: null,
            });
        else {
            setData({
                asset_type: preferenceDayToChange.asset_type,
                notification_type: preferenceDayToChange.notification_type,
                notification_delay_days: preferenceDayToChange.notification_delay_days,
                enabled: preferenceDayToChange.enabled,
            });
        }
    }, [preferenceDayToChange]);

    console.log(preferences);
    const submitPreference: FormEventHandler = async (e) => {
        e.preventDefault();
        console.log('submitPreference');
        if (!preferenceDayToChange) return;

        try {
            const response = await axios.patch(route('api.notifications.update', preferenceDayToChange.id), data);
            if (response.data.status === 'success') {
                setPreferenceDayToChange(null);
                fetchPreferences();
            }
        } catch (error) {
            setErrors(error.response.data.errors);
        }
    };

    const fetchPreferences = async () => {
        try {
            const response = await axios.get(route('api.users.notifications'));
            if (response.data.status === 'success') {
                setPreferences(response.data.data);
            }
        } catch (error) {
            setErrors(error.response.data.errors);
        }
    };

    const updateEnabledNotification = async (preference: NotificationPreference) => {
        // if (!preferenceEnabledToChange) return;
        try {
            const response = await axios.patch(route('api.notifications.update', preference.id), { ...preference, enabled: !preference.enabled });
            if (response.data.status === 'success') {
                console.log('updatedEnabledNotification');
                fetchPreferences();
            }
        } catch (error) {
            setErrors(error.response.data.errors);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Notification preferences" />

            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Notification preferences" description="Change your notification preferences" />
                    <ul>
                        {Object.keys(preferences).map((key: string) => (
                            <div key={key}>
                                <h4 className="first-letter:uppercase">{key}</h4>
                                <ul>
                                    {preferences[key].map((preference: NotificationPreference) => (
                                        <div key={preference.id}>
                                            <li className="grid w-full grid-cols-3 grid-rows-1 items-center gap-4">
                                                <Label>{preference.notification_type}</Label>
                                                {preferenceDayToChange?.id !== preference.id ? (
                                                    <div className="inline-flex items-center gap-2">
                                                        <Label></Label> <span>{preference.notification_delay_days} days </span>
                                                        <Pen onClick={() => setPreferenceDayToChange(preference)} size={16} />
                                                    </div>
                                                ) : (
                                                    <form onSubmit={submitPreference} className="flex items-center">
                                                        <Input
                                                            type="number"
                                                            step="1"
                                                            min="1"
                                                            max="30"
                                                            className="w-fit"
                                                            value={data.notification_delay_days ?? 0}
                                                            onChange={(e) => setData('notification_delay_days', e.target.valueAsNumber)}
                                                        />
                                                        <Button variant={'green'}>
                                                            <Check />
                                                        </Button>
                                                        <Button onClick={() => setPreferenceDayToChange(null)} variant={'outline'}>
                                                            <X />
                                                        </Button>
                                                    </form>
                                                )}
                                                <div className={cn('inline-flex w-fit gap-1 rounded-lg bg-neutral-100 p-1 dark:bg-neutral-800')}>
                                                    <button
                                                        key={'yes'}
                                                        onClick={() => {
                                                            updateEnabledNotification(preference);
                                                        }}
                                                        className={cn(
                                                            'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                                                            preference.enabled === true
                                                                ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                                                                : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
                                                        )}
                                                    >
                                                        <span className="ml-1.5 text-sm">Yes</span>
                                                    </button>
                                                    <button
                                                        key={'no'}
                                                        onClick={() => {
                                                            updateEnabledNotification(preference);
                                                        }}
                                                        className={cn(
                                                            'flex items-center rounded-md px-3.5 py-1.5 transition-colors',
                                                            preference.enabled === false
                                                                ? 'bg-white shadow-xs dark:bg-neutral-700 dark:text-neutral-100'
                                                                : 'text-neutral-500 hover:bg-neutral-200/60 hover:text-black dark:text-neutral-400 dark:hover:bg-neutral-700/60',
                                                        )}
                                                    >
                                                        <span className="ml-1.5 text-sm">No</span>
                                                    </button>
                                                </div>
                                            </li>
                                        </div>
                                    ))}
                                </ul>
                            </div>
                        ))}
                    </ul>
                </div>
                <Toggle className="" />
            </SettingsLayout>
        </AppLayout>
    );
}
