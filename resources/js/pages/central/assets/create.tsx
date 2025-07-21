/* eslint-disable @typescript-eslint/no-explicit-any */
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/central/app-layout';
import { AssetCategory, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

type TypeFormData = {
    translations: {
        en: string;
        fr: string;
        nl: string;
        de: string;
    };
};

export default function CreateAssetCategory({ category }: { category?: AssetCategory }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create asset category`,
            href: '/assets/create',
        },
    ];

    const { data, setData, post, errors } = useForm<TypeFormData>({
        translations: {
            fr: category?.translations.find((t) => t.locale === 'fr')?.label ?? '',
            en: category?.translations.find((t) => t.locale === 'en')?.label ?? '',
            nl: category?.translations.find((t) => t.locale === 'nl')?.label ?? '',
            de: category?.translations.find((t) => t.locale === 'de')?.label ?? '',
        },
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (category) {
            post(route(`central.assets.update`, category.slug), {
                headers: {
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': 'PATCH',
                    Accept: 'application/json',
                },
            });
        } else {
            post(route(`central.assets.store`));
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
            <Head title={`Create asset category`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <form onSubmit={submit}>
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
                    <InputError className="mt-2" message={errors['translations.en']} />

                    <Label htmlFor="domain">Label FR</Label>
                    <Input
                        id="slug"
                        type="text"
                        tabIndex={3}
                        value={data.translations.fr}
                        onChange={(e) => updateTranslation('fr', e.target.value)}
                        placeholder="Français"
                    />
                    <InputError className="mt-2" message={errors['translations.fr']} />

                    <Label htmlFor="domain">Label NL</Label>
                    <Input
                        id="slug"
                        type="text"
                        tabIndex={4}
                        value={data.translations.nl}
                        onChange={(e) => updateTranslation('nl', e.target.value)}
                        placeholder="Nederlands"
                    />
                    <InputError className="mt-2" message={errors['translations.nl']} />

                    <Label htmlFor="domain">Label DE</Label>
                    <Input
                        id="slug"
                        type="text"
                        tabIndex={5}
                        value={data.translations.de}
                        onChange={(e) => updateTranslation('de', e.target.value)}
                        placeholder="Deutsch"
                    />
                    <InputError className="mt-2" message={errors['translations.de']} />

                    <Button type="submit" tabIndex={5}>
                        Submit
                    </Button>
                </form>
            </div>
        </AppLayout>
    );
}
