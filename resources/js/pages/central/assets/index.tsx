import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/central/app-layout';
import { AssetCategory, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Index asset categories',
        href: '/assets',
    },
];

export default function AssetCategoriesIndex({ categories }: { categories: AssetCategory[] }) {
    const { delete: destroy } = useForm();

    const submit = (category: AssetCategory) => {
        destroy(route(`central.assets.destroy`, category.slug));
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Asset Categories" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route(`central.assets.create`)}>
                    <Button>Create</Button>
                </a>
                <h2>Asset categories</h2>
                <ul>
                    {categories.length > 0 &&
                        categories.map((category: AssetCategory) => (
                            <li key={category.id} className="grid grid-cols-2">
                                <p>{category.label}</p>
                                <div className="space-x-4">
                                    <Button onClick={() => submit(category)} variant={'destructive'}>
                                        Delete
                                    </Button>
                                    <a href={route(`central.assets.edit`, category.slug)}>
                                        <Button>Edit</Button>
                                    </a>
                                    <a href={route(`central.assets.show`, category.slug)}>
                                        <Button variant={'outline'}>See</Button>
                                    </a>
                                </div>
                            </li>
                        ))}
                </ul>
            </div>
        </AppLayout>
    );
}
