import AppLayout from '@/layouts/central/app-layout';
import { AssetCategory, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function ShowAssetCategory({ category }: { category: AssetCategory }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${category.label}`,
            href: ``,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Asset category ${category.label}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <ul>
                    <li key={category.id}>
                        {category.slug}
                        <ul>
                            {category.translations.map((translation) => (
                                <li key={translation.id}>
                                    {translation.locale} - {translation.label}
                                </li>
                            ))}
                        </ul>
                        {/* <Button onClick={() => setData('tenant', tenant.id)} variant={'destructive'}>
                                    Delete
                                </Button> */}
                    </li>
                </ul>
            </div>
        </AppLayout>
    );
}
