/* eslint-disable @typescript-eslint/no-explicit-any */
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { cn } from '@/lib/utils';
import { Asset, AssetCategory, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler, useEffect, useState } from 'react';

type TypeFormData = {
    q: string;
    name: string;
    description: string;
    locationId: number;
    locationReference: string;
    locationType: string;
    locationName: string;
    categoryId: string | number;
    purchase_date: string;
    purchase_cost: number | null;
    under_warranty: boolean;
    end_warranty_date: string;
    brand: string;
    model: string;
    serial_number: string;
};

type SearchedLocation = {
    id: number;
    type: string;
    name: string;
    reference_code: string;
    code: string;
};

export default function CreateAsset({ asset, categories }: { asset?: Asset; categories?: AssetCategory[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create asset`,
            href: '/assets/create',
        },
    ];

    console.log(asset);

    const { data, setData, post, errors } = useForm<TypeFormData>({
        q: '',
        name: asset?.maintainable.name ?? '',
        description: asset?.maintainable.description ?? '',
        locationId: asset?.location_id ?? '',
        locationReference: asset?.location.code ?? '',
        locationType: asset?.location.location_type.label ?? '',
        locationName: asset?.location.maintainable.name ?? '',
        categoryId: asset?.asset_category.id ?? '',
        purchase_date: asset?.maintainable.purchase_date ?? '',
        purchase_cost: asset?.maintainable.purchase_cost ?? null,
        under_warranty: asset?.maintainable.under_warranty ?? false,
        end_warranty_date: asset?.maintainable.end_warranty_date ?? '',
        brand: asset?.maintainable.brand ?? '',
        model: asset?.maintainable.model ?? '',
        serial_number: asset?.maintainable.serial_number ?? '',
    });

    console.log(data.q);

    const [listIsOpen, setListIsOpen] = useState(false);
    const [isSearching, setIsSearching] = useState(false);
    const [locations, setLocations] = useState<SearchedLocation[]>();
    const [debouncedSearch, setDebouncedSearch] = useState(data.q);
    useEffect(() => {
        const handler = setTimeout(() => {
            setDebouncedSearch(data.q);
        }, 500);

        return () => {
            clearTimeout(handler);
        };
    }, [data.q]);

    useEffect(() => {
        console.log('debounce', debouncedSearch);
        if (debouncedSearch.length < 2) {
            setLocations([]);
        }
        if (debouncedSearch.length >= 2) {
            setIsSearching(true);
            setListIsOpen(true);
            const fetchData = async () => {
                try {
                    const response = await fetch(`/api/v1/locations?q=${debouncedSearch}`);
                    setLocations(await response.json());
                    setIsSearching(false);
                    setListIsOpen(true);
                } catch (error) {
                    console.error('Erreur lors de la recherche :', error);
                }
            };

            if (debouncedSearch) {
                fetchData();
            }
        }
    }, [debouncedSearch]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        console.log(data);
        if (asset) {
            post(route(`tenant.assets.update`, asset.code), {
                headers: {
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': 'PATCH',
                    Accept: 'application/json',
                },
            });
        } else {
            post(route(`tenant.assets.store`));
        }
    };

    const setSelectedLocation = (location: SearchedLocation) => {
        if (!location) {
            return;
        }

        setData('locationReference', location.reference_code);
        setData('locationId', location.id);
        setData('locationType', location.type);
        setData('locationName', location.name);
        setLocations([]);
        setListIsOpen(!listIsOpen);
        setData('q', '');
    };

    const minEndDateWarranty = new Date().toISOString().split('T')[0];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Create asset`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                {asset && (
                    <div>
                        <p>Asset Reference: {asset.reference_code}</p>
                        <p>Asset Code: {asset.code} </p>
                        <p>
                            Asset attached to : {asset.location.maintainable.name} - {asset.location.location_type.label}
                        </p>
                    </div>
                )}
                <form onSubmit={submit}>
                    <Label htmlFor="search">Search</Label>
                    <div className="relative">
                        <Input type="search" value={data.q} onChange={(e) => setData('q', e.target.value)} placeholder="Search by code or name" />
                        <ul className="bg-background absolute z-50 flex w-full flex-col border" aria-autocomplete="list" role="listbox">
                            {isSearching && (
                                <li value="0" key="" className="">
                                    Searching...
                                </li>
                            )}
                            {listIsOpen &&
                                locations &&
                                locations.length > 0 &&
                                locations?.map((location) => (
                                    <li
                                        role="option"
                                        value={location.reference_code}
                                        key={location.reference_code}
                                        onClick={() => setSelectedLocation(location)}
                                        className="hover:bg-foreground hover:text-background cursor-pointer p-2 text-sm"
                                    >
                                        {location.name + ' (' + location.reference_code + ')'}
                                    </li>
                                ))}
                            {listIsOpen && locations && locations.length == 0 && (
                                <>
                                    <li value="0" key="">
                                        No results
                                    </li>
                                </>
                            )}
                        </ul>
                    </div>
                    {/* {data.locationName && ( */}
                    <>
                        <Label htmlFor="locationType">Level</Label>
                        <Input
                            type="text"
                            disabled
                            value={data.locationName ? data.locationName + ' - ' + data.locationReference : 'No location selected'}
                        />
                        <InputError className="mt-2" message={errors.locationType} />
                    </>
                    {/* )} */}

                    <Label htmlFor="name">Category</Label>
                    <select
                        name="level"
                        required
                        value={data.categoryId === '' ? 0 : data.categoryId}
                        onChange={(e) => setData('categoryId', e.target.value)}
                        id=""
                        className={cn(
                            'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                            'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                            'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                        )}
                    >
                        {categories && categories.length > 0 && (
                            <>
                                <option value="0" disabled className="bg-background text-foreground">
                                    Select an option
                                </option>
                                {categories?.map((category) => (
                                    <option value={category.id} key={category.id} className="bg-background text-foreground">
                                        {category.label}
                                    </option>
                                ))}
                            </>
                        )}
                    </select>
                    <InputError className="mt-2" message={errors.categoryId} />

                    <Label htmlFor="name">Name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autoFocus
                        maxLength={100}
                        value={data.name}
                        onChange={(e) => setData('name', e.target.value)}
                        placeholder="Asset name"
                    />
                    <InputError className="mt-2" message={errors.name} />

                    <Label htmlFor="description">Description</Label>
                    <Input
                        id="description"
                        type="text"
                        maxLength={255}
                        value={data.description}
                        onChange={(e) => setData('description', e.target.value)}
                        placeholder="Asset description"
                    />
                    <InputError className="mt-2" message={errors.description} />

                    <Label htmlFor="brand">Brand</Label>
                    <Input
                        id="brand"
                        type="text"
                        maxLength={100}
                        value={data.brand}
                        onChange={(e) => setData('brand', e.target.value)}
                        placeholder="Asset brand"
                    />
                    <InputError className="mt-2" message={errors.brand} />

                    <Label htmlFor="model">Model</Label>
                    <Input
                        id="model"
                        type="text"
                        maxLength={100}
                        value={data.model}
                        onChange={(e) => setData('model', e.target.value)}
                        placeholder="Asset model"
                    />
                    <InputError className="mt-2" message={errors.model} />

                    <Label htmlFor="serial_number">Serial number</Label>
                    <Input
                        id="serial_number"
                        type="text"
                        maxLength={50}
                        value={data.serial_number}
                        onChange={(e) => setData('serial_number', e.target.value)}
                        placeholder="Asset serial number"
                    />
                    <InputError className="mt-2" message={errors.serial_number} />

                    <div>
                        <Label htmlFor="purchase_date">Date of purchase</Label>
                        <Input
                            id="purchase_date"
                            type="date"
                            value={data.purchase_date}
                            max={minEndDateWarranty}
                            onChange={(e) => setData('purchase_date', e.target.value)}
                            placeholder="Date of purchase"
                        />
                        <InputError className="mt-2" message={errors.purchase_date} />
                    </div>
                    <Label htmlFor="purchase_cost">Purchase cost</Label>
                    <Input
                        id="purchase_cost"
                        type="number"
                        min={0}
                        step="0.01"
                        value={data.purchase_cost ?? ''}
                        onChange={(e) => setData('purchase_cost', parseFloat(e.target.value))}
                        placeholder="Purchase cost (max. 2 decimals) : 4236.36"
                    />
                    <InputError className="mt-2" message={errors.purchase_cost} />

                    <Label htmlFor="under_warranty">Still under warranty ?</Label>
                    <Checkbox
                        id="under_warranty"
                        name="under_warranty"
                        checked={data.under_warranty}
                        onClick={() => setData('under_warranty', !data.under_warranty)}
                    />
                    <InputError className="mt-2" message={errors.under_warranty} />

                    {data.under_warranty && (
                        <div>
                            <Label htmlFor="end_warranty_date">Date end of warranty</Label>
                            <Input
                                id="end_warranty_date"
                                type="date"
                                value={data.end_warranty_date}
                                min={minEndDateWarranty}
                                onChange={(e) => setData('end_warranty_date', e.target.value)}
                                placeholder="Date end of warranty"
                            />
                            <InputError className="mt-2" message={errors.end_warranty_date} />
                        </div>
                    )}

                    <br />
                    <Button type="submit">{asset ? 'Update' : 'Submit'}</Button>
                </form>
            </div>
        </AppLayout>
    );
}
