import { InterventionManager } from '@/components/tenant/interventionManager';
import { PictureManager } from '@/components/tenant/pictureManager';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import Field from '@/components/ui/field';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Ticket } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useState } from 'react';

export default function ShowTicket({ item }: { item: Ticket }) {
    const { t, tChoice } = useLaravelReactI18n();
    const [ticket, setTicket] = useState(item);
    const { showToast } = useToast();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${tChoice('tickets.title', 2)}`,
            href: `/tickets`,
        },

        {
            title: `${ticket.code} -  ${ticket.ticketable.maintainable.name} - ${ticket.ticketable.reference_code}`,
            href: `/tickets/${ticket.id}`,
        },
    ];

    const fetchTicket = async () => {
        try {
            const response = await axios.get(route(`api.tickets.get`, ticket.id));
            setTicket(response.data.data);
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const changeStatusTicket = async (id: number, status: string) => {
        try {
            const response = await axios.patch(route('api.tickets.status', id), { status: status });
            if (response.data.status === 'success') {
                fetchTicket();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const [activeTab, setActiveTab] = useState('information');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={tChoice('tickets.title', 1) + ' ' + ticket.code} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex gap-2">
                    {ticket.status !== 'closed' && (
                        <Button variant={'destructive'} onClick={() => changeStatusTicket(ticket.id, 'closed')}>
                            {t('actions.close')}
                        </Button>
                    )}
                    {!ticket.ticketable.deleted_at && ticket.status === 'closed' && (
                        <Button variant={'green'} onClick={() => changeStatusTicket(ticket.id, 'open')}>
                            {t('tickets.reopen')}
                        </Button>
                    )}
                </div>
                <div className="grid max-w-full gap-4 lg:grid-cols-[1fr_6fr]">
                    <SidebarMenuAssetLocation
                        activeTab={activeTab}
                        setActiveTab={setActiveTab}
                        menu="ticket"
                        infos={{
                            name: ticket.code,
                            code: ticket.closed_at ?? ticket.created_at,
                            status: ticket.status,
                            levelPath: ticket.ticketable_route,
                            levelName: ticket.ticketable.name + ' - ' + ticket.ticketable.reference_code,
                        }}
                    />
                    <div className="space-y-4 overflow-hidden">
                        {activeTab === 'information' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h2>{t('common.information')}</h2>
                                <div className="space-y-2">
                                    <Field label={t('common.description')} text={ticket.description} />

                                    <div className="flex gap-2">
                                        <Field
                                            label={t('tickets.reporter')}
                                            text={ticket.reporter ? ticket.reporter.full_name : ticket.reporter_email}
                                        />
                                        <Field label={t('common.created_at')} text={ticket.created_at} />
                                        {ticket.handled_at && <Field label={t('tickets.handled_at')} text={ticket.handled_at} />}
                                    </div>

                                    <div className="flex flex-wrap gap-2">
                                        {ticket.closer && <Field label={t('tickets.closer')} text={ticket.closer?.full_name} />}
                                        <Field label={t('tickets.closed_at')} text={ticket.closed_at} />
                                    </div>
                                </div>
                            </div>
                        )}

                        {/* {activeTab === 'pictures' && ( */}
                        <PictureManager
                            itemCodeId={ticket.id}
                            getPicturesUrl={`api.tickets.pictures`}
                            uploadRoute={`api.tickets.pictures.post`}
                            deleteRoute={`api.pictures.delete`}
                            showRoute={'api.pictures.show'}
                            canAdd={ticket.status === 'closed' ? false : true}
                        />
                        {/* )} */}
                        {/* {activeTab === 'interventions' && ( */}
                        <InterventionManager
                            itemCodeId={ticket.id}
                            getInterventionsUrl="api.tickets.interventions"
                            type="ticket"
                            closed={ticket.status === 'closed' ? true : false}
                        />
                        {/* )} */}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
