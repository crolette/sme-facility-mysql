import { InterventionActionManager } from '@/components/tenant/interventionActionManager';
import { PictureManager } from '@/components/tenant/pictureManager';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Intervention } from '@/types';
import { Head } from '@inertiajs/react';
import { useState } from 'react';

export default function ShowIntervention({ intervention }: { intervention: Intervention }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index interventions`,
            href: `/interventions/`,
        },
        {
            title: `Show intervention`,
            href: `/interventions/${intervention}`,
        },
    ];

    const [activeTab, setActiveTab] = useState('information');
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Intervention" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex flex-wrap items-center gap-4"></div>
                <div className="grid max-w-full gap-4 lg:grid-cols-[1fr_6fr]">
                    <SidebarMenuAssetLocation
                        activeTab={activeTab}
                        setActiveTab={setActiveTab}
                        menu={'interventions'}
                        infos={{
                            name: intervention.type,
                            code: intervention.updated_at,
                            status: intervention.status,
                            priority: intervention.priority,
                            levelPath: intervention.interventionable?.location_route ?? '',
                            levelName: intervention.interventionable?.reference_code ?? 'NULL',
                        }}
                    />
                    <div className="overflow-hidden">
                        {activeTab === 'information' && (
                            <>
                                <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                    <h2>Intervention information</h2>
                                    <div className="">
                                        <p>Intervention type : {intervention.type}</p>
                                        <p>Planned at : {intervention.type}</p>
                                        <p>Description : {intervention.description}</p>
                                        <p>Total costs : {intervention.total_costs}</p>
                                        <p>Created at : {intervention.created_at}</p>
                                        <p>Updated at : {intervention.updated_at}</p>
                                    </div>
                                    <div></div>
                                </div>
                                <InterventionActionManager
                                    interventionId={intervention.id}
                                    actionsChanged={console.log('change')}
                                    closed={closed ? true : intervention.status === 'completed' || intervention.status === 'cancelled' ? true : false}
                                />
                            </>
                        )}
                        {activeTab === 'pictures' && (
                            <PictureManager
                                itemCodeId={intervention.id}
                                getPicturesUrl={`api.interventions.pictures`}
                                uploadRoute={`api.interventions.pictures.post`}
                                deleteRoute={`api.pictures.delete`}
                                showRoute={'api.pictures.show'}
                            />
                        )}

                        {/* {activeTab === 'actions' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>Actions</h2>
                                <div className="">
                                    <ul className="flex flex-col">
                                        {intervention.actions?.map((action) => (
                                            <li key={action.id} className="even:bg-accent p-2">
                                                <p>
                                                    {action.type} ({action.updated_at})
                                                </p>
                                                <p>{action.description}</p>
                                                {action.intervention_date && <p>Intervention date : {action.intervention_date}</p>}
                                                {action.started_at && (
                                                    <p>
                                                        Started at : {action.started_at} - Finished at : {action.finished_at}
                                                    </p>
                                                )}
                                                <ul className="flex flex-row">
                                                    {action.pictures?.map((picture) => (
                                                        <li className="w-32">
                                                            <img
                                                                src={route('api.pictures.show', picture.id)}
                                                                className="aspect-square object-cover"
                                                                alt={picture.filename}
                                                            />
                                                        </li>
                                                    ))}
                                                </ul>
                                            </li>
                                        ))}
                                    </ul>
                                </div>
                            </div>
                        )} */}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
