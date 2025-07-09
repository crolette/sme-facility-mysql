/* eslint-disable @typescript-eslint/no-explicit-any */
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { LocationType, TenantBuilding, TenantFloor, TenantRoom, TenantSite, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

type TypeFormData = {
    name: string;
    description: string;
    levelType: string | number;
    locationType: string | number;
};

export default function CreateLocation({
    location,
    levelTypes,
    locationTypes,
    routeName,
}: {
    location?: TenantSite | TenantBuilding | TenantFloor | TenantRoom;
    levelTypes?: LocationType[] | TenantSite[] | TenantFloor[];
    locationTypes: LocationType[];
    routeName: string;
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create ${routeName} type`,
            href: '/locations/create',
        },
    ];

    console.log(levelTypes);

    const { data, setData, post, errors } = useForm<TypeFormData>({
        name: location?.maintainable?.name ?? '',
        description: location?.maintainable?.description ?? '',
        levelType: location?.level_id ?? '',
        locationType: location?.location_type?.id ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (location) {
            post(route(`tenant.${routeName}.update`, location.id), {
                headers: {
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': 'PATCH',
                    Accept: 'application/json',
                },
            });
        } else {
            post(route(`tenant.${routeName}.store`));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Create location type`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {location && (
                    <div>
                        <p>Location Reference: {location.reference_code}</p>
                        <p>Location Code: {location.code} </p>
                    </div>
                )}
                <form onSubmit={submit}>
                    <div>
                        {levelTypes && (
                            <>
                                <Label htmlFor="level">Level</Label>
                                <select
                                    name="level"
                                    value={data.levelType}
                                    onChange={(e) => setData('levelType', e.target.value)}
                                    disabled={location ? true : false}
                                    id=""
                                    className={cn(
                                        'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                        'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                    )}
                                >
                                    <option value="" disabled>
                                        -- Select a level --
                                    </option>
                                    {levelTypes?.map((type) => (
                                        <option value={type.id} key={type.id}>
                                            {type.label ?? type.maintainable.name + ' (' + type.reference_code + ')'}
                                        </option>
                                    ))}
                                </select>
                                <InputError className="mt-2" message={errors.levelType} />
                            </>
                        )}
                    </div>
                    {locationTypes && (
                        <div>
                            <Label htmlFor="location-type">Location type</Label>
                            <select
                                name="location-type"
                                value={data.locationType}
                                onChange={(e) => setData('locationType', e.target.value)}
                                disabled={location ? true : false}
                                id="location-type"
                                className={cn(
                                    'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                    'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                    'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                )}
                            >
                                <option value="" disabled>
                                    -- Select a location type --
                                </option>
                                {locationTypes.map((type) => (
                                    <option value={type.id} key={type.id}>
                                        {type.label}
                                    </option>
                                ))}
                            </select>
                            <InputError className="mt-2" message={errors.locationType} />
                        </div>
                    )}

                    <Label htmlFor="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        // disabled={type?.prefix ? true : false}
                        autoFocus
                        maxLength={100}
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder="Site name"
                    />
                    <InputError className="mt-2" message={errors.name} />

                    <Label htmlFor="name">Description</Label>
                    <Input
                        id="description"
                        type="text"
                        required
                        // disabled={type?.prefix ? true : false}
                        maxLength={255}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder="Site description"
                    />
                    <InputError className="mt-2" message={errors.description} />

                    <Button type="submit">Submit</Button>
                </form>
            </div>
        </AppLayout>
    );
}
