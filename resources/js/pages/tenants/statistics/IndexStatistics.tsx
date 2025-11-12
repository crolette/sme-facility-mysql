import { InterventionsByAssigneeChart } from '@/components/tenant/statistics/interventionsByAssigneeChart';
import { InterventionsByStatusChart } from '@/components/tenant/statistics/interventionsByStatusChart';
import { InterventionsByTypeChart } from '@/components/tenant/statistics/interventionsByTypeChart';
import { TicketsByAvgDurationChart } from '@/components/tenant/statistics/TicketsByAvgDurationChart';
import { TicketsByAvgHandlingDurationChart } from '@/components/tenant/statistics/TicketsByAvgHandlingDurationChart';
import { TicketsByItemChart } from '@/components/tenant/statistics/TicketsByItemChart';
import { TicketsByPeriodChart } from '@/components/tenant/statistics/TicketsByPeriodChart';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { createContext, useContext, useState } from 'react';

const DashboardFiltersContext = createContext<{
    dateFrom: string | null;
    dateTo: string | null;
}>({ dateFrom: '', dateTo: '' });

export const useDashboardFilters = () => useContext(DashboardFiltersContext);

export default function IndexStatistics({
    interventionsByStatus,
    interventionsByType,
    interventionsByAssignee,
    ticketsByPeriod,
    ticketsByAssetOrLocations,
    ticketsAvgDuration,
    ticketsByAvgHandlingDuration,
}: {
    interventionsByStatus: [];
    interventionsByType: [];
    interventionsByAssignee: [];
    ticketsByPeriod: [];
    ticketsByAssetOrLocations: [];
    ticketsAvgDuration: [];
    ticketsByAvgHandlingDuration: [];
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index statistics`,
            href: `/statistics`,
        },
    ];

    const [dateFrom, setDateFrom] = useState<string | null>(null);
    const [dateTo, setDateTo] = useState<string | null>(null);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Statistics" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1>Statistics</h1>
                <div className="flex items-center gap-4">
                    <Label>From</Label>
                    <input type="date" name="dateFrom" id="" value={dateFrom ?? '2025-01-01'} onChange={(e) => setDateFrom(e.target.value)} />
                    <Label>To</Label>
                    <input type="date" name="dateTo" id="" value={dateTo ?? '2025-12-31'} onChange={(e) => setDateTo(e.target.value)} />
                    <Button
                        onClick={() => {
                            setDateFrom(null);
                            setDateTo(null);
                        }}
                    >
                        Clear interval
                    </Button>
                </div>

                <DashboardFiltersContext.Provider value={{ dateFrom, dateTo }}>
                    <h2>Interventions</h2>
                    <div className="border-accent grid w-full grid-cols-1 gap-10 border-b-2 lg:grid-cols-3">
                        <InterventionsByTypeChart interventionsByType={interventionsByType} />
                        <InterventionsByStatusChart interventionsByStatus={interventionsByStatus} />
                        <InterventionsByAssigneeChart interventionsByAssignee={interventionsByAssignee} />
                    </div>
                    <h2>Tickets</h2>
                    <div className="border-accent grid w-full grid-cols-1 gap-10 border-b-2 lg:grid-cols-3">
                        <TicketsByPeriodChart ticketsByPeriod={ticketsByPeriod} />
                        <TicketsByAvgDurationChart ticketsAvgDuration={ticketsAvgDuration} />
                        <TicketsByAvgHandlingDurationChart ticketsByAvgHandlingDuration={ticketsByAvgHandlingDuration} />
                    </div>
                    <h2>Assets/Locations</h2>
                    <div className="border-accent grid w-full grid-cols-1 gap-10 border-b-2 lg:grid-cols-3">
                        <TicketsByItemChart ticketsByAssetOrLocations={ticketsByAssetOrLocations} />
                    </div>
                </DashboardFiltersContext.Provider>
            </div>
        </AppLayout>
    );
}
