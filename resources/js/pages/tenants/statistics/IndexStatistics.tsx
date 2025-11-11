import { InterventionsByAssigneeChart } from '@/components/tenant/statistics/interventionsByAssigneeChart';
import { InterventionsByStatusChart } from '@/components/tenant/statistics/interventionsByStatusChart';
import { InterventionsByTypeChart } from '@/components/tenant/statistics/interventionsByTypeChart';
import { TicketsByItemChart } from '@/components/tenant/statistics/TicketsByItemChart';
import { TicketsByPeriodChart } from '@/components/tenant/statistics/TicketsByPeriodChart';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function IndexStatistics({
    interventionsByStatus,
    interventionsByType,
    interventionsByAssignee,
    ticketsByPeriod,
    ticketsByAssetOrLocations,
}: {
    interventionsByStatus: [];
    interventionsByType: [];
    interventionsByAssignee: [];
    ticketsByPeriod: [];
    ticketsByAssetOrLocations: [];
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index statistics`,
            href: `/statistics`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Statistics" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1>Statistics</h1>
                <div className="border-accent grid w-full grid-cols-1 gap-10 border-b-2 lg:grid-cols-3">
                    <InterventionsByTypeChart interventionsByType={interventionsByType} />
                    <InterventionsByStatusChart interventionsByStatus={interventionsByStatus} />
                    <InterventionsByAssigneeChart interventionsByAssignee={interventionsByAssignee} />
                </div>
                <div className="border-accent grid w-full grid-cols-1 gap-10 border-b-2 lg:grid-cols-3">
                    <TicketsByPeriodChart ticketsByPeriod={ticketsByPeriod} />
                    <TicketsByItemChart ticketsByAssetOrLocations={ticketsByAssetOrLocations} />
                </div>
            </div>
        </AppLayout>
    );
}
