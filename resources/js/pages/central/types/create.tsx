/* eslint-disable @typescript-eslint/no-explicit-any */
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/central/app-layout';
import { cn } from '@/lib/utils';
import { LocationLevel, LocationType, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

type TypeFormData = {
    prefix: string;
    level: string;
    translations: {
        en: string;
        fr: string;
        nl: string;
        de: string;
    };
};

export default function CreateSiteType({ type, types, routeName }: { type?: LocationType; types: LocationLevel[]; routeName: string }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create location type`,
            href: '/locations/create',
        },
    ];

    const { data, setData, post, errors } = useForm<TypeFormData>({
        prefix: type?.prefix ?? '',
        level: type?.level ?? '',
        translations: {
            fr: type?.translations.find((t) => t.locale === 'fr')?.label ?? '',
            en: type?.translations.find((t) => t.locale === 'en')?.label ?? '',
            nl: type?.translations.find((t) => t.locale === 'nl')?.label ?? '',
            de: type?.translations.find((t) => t.locale === 'de')?.label ?? '',
        },
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (type) {
            post(route(`central.${routeName}.update`, type.slug), {
                headers: {
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': 'PATCH',
                    Accept: 'application/json',
                },
            });
        } else {
            post(route(`central.${routeName}.store`));
        }
    };

    const updateTranslation = (locale: string, value: string) => {
        setData({
            ...data,
            translations: {
                ...data.translations,
                [locale]: value,
            },
        });
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Create location type`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <form onSubmit={submit}>
                    <Label htmlFor="name">Level</Label>

                    <select
                        name=""
                        value={data.level}
                        onChange={(e) => setData('level', e.target.value)}
                        disabled={type?.level ? true : false}
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
                        {types.map((type) => (
                            <option value={type} key={type}>
                                {type}
                            </option>
                        ))}
                    </select>
                    <InputError className="mt-2" message={errors.level} />

                    <Label htmlFor="name">Prefix</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        disabled={type?.prefix ? true : false}
                        autoFocus
                        tabIndex={1}
                        minLength={1}
                        maxLength={2}
                        value={data.prefix}
                        onChange={(e) => setData('prefix', e.target.value)}
                        placeholder="Site prefix"
                    />
                    <InputError className="mt-2" message={errors.prefix} />

                    <Label htmlFor="domain">Label EN</Label>
                    <Input
                        id="slug"
                        type="text"
                        required
                        autoFocus
                        tabIndex={2}
                        value={data.translations.en}
                        onChange={(e) => updateTranslation('en', e.target.value)}
                        placeholder="English"
                    />

                    <Label htmlFor="domain">Label FR</Label>
                    <Input
                        id="slug"
                        type="text"
                        autoFocus
                        tabIndex={3}
                        value={data.translations.fr}
                        onChange={(e) => updateTranslation('fr', e.target.value)}
                        placeholder="Français"
                    />
                    <Label htmlFor="domain">Label NL</Label>
                    <Input
                        id="slug"
                        type="text"
                        autoFocus
                        tabIndex={4}
                        value={data.translations.nl}
                        onChange={(e) => updateTranslation('nl', e.target.value)}
                        placeholder="Nederlands"
                    />
                    <Label htmlFor="domain">Label DE</Label>
                    <Input
                        id="slug"
                        type="text"
                        autoFocus
                        tabIndex={5}
                        value={data.translations.de}
                        onChange={(e) => updateTranslation('de', e.target.value)}
                        placeholder="Deutsch"
                    />

                    <Button type="submit" tabIndex={5}>
                        Submit
                    </Button>
                </form>
            </div>
        </AppLayout>
    );
}
