import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/central/app-layout';
import { CentralType, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Index category types',
        href: '/types',
    },
];

export default function DocumentTypesIndex({ types }: { types: CentralType[] }) {
    const { delete: destroy } = useForm();

    const submit = (type: CentralType) => {
        destroy(route(`central.types.destroy`, type.slug));
    };

    // TODO FETCH Types to render different types in different tabs

    console.log(types);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Category types" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route(`central.types.create`)}>
                    <Button>Create</Button>
                </a>
                <h2>Category types</h2>
                <ul>
                    {types.length > 0 &&
                        types.map((type: CentralType) => (
                            <li key={type.id} className="grid grid-cols-2">
                                <p>
                                    {type.label} - {type.category}
                                </p>
                                <div className="space-x-4">
                                    <Button onClick={() => submit(type)} variant={'destructive'}>
                                        Delete
                                    </Button>
                                    <a href={route(`central.types.edit`, type.slug)}>
                                        <Button>Edit</Button>
                                    </a>
                                    <a href={route(`central.types.show`, type.slug)}>
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
