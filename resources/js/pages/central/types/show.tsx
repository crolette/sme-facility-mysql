import AppLayout from '@/layouts/central/app-layout';
import { CentralType, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function ShowCategoryType({ type }: { type: CentralType }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${type.label}`,
            href: ``,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Category type ${type.label}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <ul>
                    <li key={type.id}>
                        {type.slug}
                        <ul>
                            {type.translations.map((translation) => (
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
