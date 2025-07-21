/* eslint-disable @typescript-eslint/no-explicit-any */
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/central/app-layout';
import { cn } from '@/lib/utils';
import { CategoryTypeEnum, CentralType, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

type TypeFormData = {
    category: string;
    translations: {
        en: string;
        fr: string;
        nl: string;
        de: string;
    };
};

export default function CreateDocumentType({ type, categories }: { type?: CentralType; categories: CategoryTypeEnum[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Create category type`,
            href: '/types/create',
        },
    ];

    const { data, setData, post, errors } = useForm<TypeFormData>({
        category: type?.category ?? '',
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
            post(route(`central.types.update`, type.slug), {
                headers: {
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': 'PATCH',
                    Accept: 'application/json',
                },
            });
        } else {
            post(route(`central.types.store`));
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
                    <Label htmlFor="domain">Type</Label>
                    <select
                        name=""
                        value={data.category}
                        onChange={(e) => setData('category', e.target.value)}
                        disabled={type?.category ? true : false}
                        id=""
                        className={cn(
                            'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                            'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                            'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                        )}
                    >
                        <option value="" disabled className="bg-background text-foreground">
                            -- Select a level --
                        </option>
                        {categories.map((category) => (
                            <option value={category} key={category} className="bg-background text-foreground">
                                {category}
                            </option>
                        ))}
                    </select>
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
