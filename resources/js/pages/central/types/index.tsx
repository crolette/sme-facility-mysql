import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/central/app-layout';
import { CentralType, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Index category types',
        href: '/types',
    },
];

export default function DocumentTypesIndex({ types }: { types: object }) {
    // const { delete: destroy } = useForm();

    // const submit = (type: CentralType) => {
    //     destroy(route(`central.types.destroy`, type.slug));
    // };

    // TODO FETCH Types to render different types in different tabs

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Category types" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route(`central.types.create`)}>
                    <Button>Create</Button>
                </a>
                <h2>Category types</h2>
                <div></div>
                <ul>
                    {Object.entries(types).map(([key, items]) => (
                        <div key={key} className="mb-8">
                            <h3 className="">{key}</h3>
                            <ul>
                                {items.map((item: CentralType) => (
                                    <li key={item.id} className="grid grid-cols-2 space-y-2">
                                        <span>{item.label}</span>
                                        <div className="space-x-4">
                                            {/* <Button onClick={() => submit(item)} variant={'destructive'}>
                                                Delete
                                            </Button> */}
                                            <a href={route(`central.types.edit`, item.slug)}>
                                                <Button>Edit</Button>
                                            </a>
                                            <a href={route(`central.types.show`, item.slug)}>
                                                <Button variant={'outline'}>See</Button>
                                            </a>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    ))}
                </ul>
            </div>
        </AppLayout>
    );
}
