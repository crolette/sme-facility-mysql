import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/central/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useState } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Index category types',
        href: '/types',
    },
];

type Tabs = 'action' | 'asset' | 'document' | 'floor_materials' | 'intervention' | 'outdoor_materials' | 'provider' | 'wall_materials';

export default function DocumentTypesIndex({ types }: { types: object }) {
    const { t, tChoice } = useLaravelReactI18n();
    // const { delete: destroy } = useForm();

    // const submit = (type: CentralType) => {
    //     destroy(route(`central.types.destroy`, type.slug));
    // };

    const [showTab, setShowTab] = useState<Tabs>('action');

    // TODO FETCH Types to render different types in different tabs
    console.log(types);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Category types" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route(`central.types.create`)}>
                    <Button>Create</Button>
                </a>
                <div className="my-2 space-y-2 space-x-2">
                    <Button variant={showTab == 'asset' ? 'default' : 'outline'} onClick={() => setShowTab('asset')} size={'lg'}>
                        {tChoice('assets.title', 2)}
                    </Button>
                    <Button variant={showTab == 'document' ? 'default' : 'outline'} onClick={() => setShowTab('document')} size={'lg'}>
                        {tChoice('documents.title', 2)}
                    </Button>
                    <Button variant={showTab == 'provider' ? 'default' : 'outline'} onClick={() => setShowTab('provider')} size={'lg'}>
                        {tChoice('providers.title', 2)}
                    </Button>
                    <Button variant={showTab == 'intervention' ? 'default' : 'outline'} onClick={() => setShowTab('intervention')} size={'lg'}>
                        {tChoice('interventions.title', 2)}
                    </Button>
                    <Button variant={showTab == 'action' ? 'default' : 'outline'} onClick={() => setShowTab('action')} size={'lg'}>
                        {tChoice('interventions.actions', 2)}
                    </Button>
                    <Button variant={showTab == 'floor_materials' ? 'default' : 'outline'} onClick={() => setShowTab('floor_materials')} size={'lg'}>
                        {t('locations.material_floor')}
                    </Button>
                    <Button variant={showTab == 'wall_materials' ? 'default' : 'outline'} onClick={() => setShowTab('wall_materials')} size={'lg'}>
                        {t('locations.material_wall')}
                    </Button>
                    <Button
                        variant={showTab == 'outdoor_materials' ? 'default' : 'outline'}
                        onClick={() => setShowTab('outdoor_materials')}
                        size={'lg'}
                    >
                        {t('locations.material_outdoor')}
                    </Button>
                </div>
                <h2>Category types</h2>
                <div>
                    <div key={showTab} className="mb-8">
                        <h3 className="">{showTab}</h3>
                        <ul className="space-y-2">
                            {types[showTab].map((item) => (
                                <>
                                    <li key={item.id} className="odd:bg-accent flex items-center justify-between p-2">
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
                                </>
                            ))}
                        </ul>
                    </div>
                </div>
                {/* <ul>
                    {Object.entries(types).map(([key, items]) => (
                        <div key={key} className="mb-8">
                            <h3 className="">{key}</h3>
                            <ul className="space-y-2">
                                {items.map((item: CentralType) => (
                                    <li key={item.id} className="odd:bg-accent flex items-center justify-between p-2">
                                        <span>{item.label}</span>
                                        <div className="space-x-4">
                                            <Button onClick={() => submit(item)} variant={'destructive'}>
                                                Delete
                                            </Button>
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
                </ul> */}
            </div>
        </AppLayout>
    );
}
