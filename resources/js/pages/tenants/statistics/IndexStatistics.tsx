import { ChartComponent } from '@/components/tenant/statistics/ChartComponent';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { createContext, useContext, useState } from 'react';

type Statistics = 'interventions' | 'tickets' | 'assets';

const DashboardFiltersContext = createContext<{
    dateFrom: string | null;
    dateTo: string | null;
}>({ dateFrom: '', dateTo: '' });

export const useDashboardFilters = () => useContext(DashboardFiltersContext);

export default function IndexStatistics({
    interventionsByStatus,
    interventionsByType,
    interventionsByAssignee,
    interventionsMissed,
    ticketsByPeriod,
    ticketsByAssetOrLocations,
    ticketsAvgDuration,
    ticketsByAvgHandlingDuration,
}: {
    interventionsByStatus: [];
    interventionsByType: [];
    interventionsByAssignee: [];
    interventionsMissed: [];
    ticketsByPeriod: [];
    ticketsByAssetOrLocations: [];
    ticketsAvgDuration: [];
    ticketsByAvgHandlingDuration: [];
}) {
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${t('statistics.title')}`,
            href: `/statistics`,
        },
    ];

    const now = new Date();

    const formatDateForInput = (date: Date) => date.toISOString().split('T')[0];

    const defaultDateFrom = formatDateForInput(new Date(now.getFullYear() - 1, now.getMonth(), now.getDate()));
    const defaultDateTo = formatDateForInput(new Date());

    const [dateFrom, setDateFrom] = useState<string | null>(null);
    const [dateTo, setDateTo] = useState<string | null>(null);
    const [dateFromTemp, setDateFromTemp] = useState<string | null>(defaultDateFrom);
    const [dateToTemp, setDateToTemp] = useState<string | null>(defaultDateTo);

    const [showTab, setShowTab] = useState<Statistics>('interventions');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('statistics.title')} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1>{t('statistics.title')}</h1>
                <div className="flex flex-wrap items-center gap-4">
                    <div className="space-x-2">
                        <Label>{t('common.from')}</Label>
                        <input
                            type="date"
                            name="dateFrom"
                            id="dateFrom"
                            value={dateFromTemp ?? '2025-01-01'}
                            onChange={(e) => setDateFromTemp(e.target.value)}
                        />
                    </div>
                    <div className="space-x-2">
                        <Label>{t('common.to')}</Label>
                        <input
                            type="date"
                            name="dateTo"
                            id="dateTo"
                            value={dateToTemp ?? '2025-12-31'}
                            onChange={(e) => setDateToTemp(e.target.value)}
                        />
                    </div>
                    <Button
                        disabled={dateFromTemp == defaultDateFrom && dateToTemp == defaultDateTo}
                        onClick={() => {
                            if (dateFromTemp) setDateFrom(dateFromTemp);
                            if (dateToTemp) setDateTo(dateToTemp);
                        }}
                    >
                        {t('actions.update')}
                    </Button>
                    <Button
                        disabled={dateFromTemp == defaultDateFrom && dateToTemp == defaultDateTo}
                        onClick={() => {
                            setDateToTemp(defaultDateFrom);
                            setDateFromTemp(defaultDateTo);
                            setDateFrom(defaultDateFrom);
                            setDateTo(defaultDateTo);
                        }}
                    >
                        {t('statistics.clear_interval')}
                    </Button>
                </div>

                <DashboardFiltersContext.Provider value={{ dateFrom, dateTo }}>
                    <div className="my-2 space-y-2 space-x-2">
                        <Button variant={showTab == 'interventions' ? 'default' : 'outline'} onClick={() => setShowTab('interventions')} size={'lg'}>
                            {tChoice('interventions.title', 2)}
                        </Button>
                        <Button variant={showTab == 'tickets' ? 'default' : 'outline'} onClick={() => setShowTab('tickets')} size={'lg'}>
                            {tChoice('tickets.title', 2)}
                        </Button>
                        <Button variant={showTab == 'assets' ? 'default' : 'outline'} onClick={() => setShowTab('assets')} size={'lg'}>
                            {tChoice('assets.title', 2)}
                        </Button>
                    </div>

                    {showTab === 'interventions' && (
                        <>
                            <h2>{tChoice('interventions.title', 2)}</h2>
                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                {/* <div className="border-accent flex w-full flex-wrap gap-10"> */}
                                <ChartComponent
                                    datas={interventionsByType}
                                    url="api.statistics.interventions.by-type"
                                    chartName={t('statistics.interventions_by_type')}
                                    chartTypes={['horizontalBar', 'verticalBar', 'doughnut']}
                                />
                                <ChartComponent
                                    datas={interventionsByStatus}
                                    url="api.statistics.interventions.by-status"
                                    chartName={t('statistics.interventions_by_status')}
                                    chartTypes={['horizontalBar', 'verticalBar', 'doughnut']}
                                />
                                <ChartComponent
                                    datas={interventionsByAssignee}
                                    url="api.statistics.interventions.by-assignee"
                                    chartName={t('statistics.interventions_by_assignee')}
                                    chartTypes={['horizontalBar', 'verticalBar', 'doughnut']}
                                />
                                <ChartComponent
                                    datas={interventionsMissed}
                                    url="api.statistics.interventions.missed"
                                    chartName={t('statistics.interventions_missed')}
                                    chartTypes={['horizontalBar', 'verticalBar', 'line']}
                                    withPeriods
                                />
                            </div>
                        </>
                    )}

                    {showTab === 'tickets' && (
                        <>
                            {' '}
                            <h2>{tChoice('tickets.title', 2)}</h2>
                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <ChartComponent
                                    datas={ticketsByPeriod}
                                    url="api.statistics.tickets.by-period"
                                    chartName={t('statistics.tickets_by_period')}
                                    chartTypes={['horizontalBar', 'verticalBar', 'line']}
                                    withPeriods
                                />
                                <ChartComponent
                                    datas={ticketsAvgDuration}
                                    url="api.statistics.tickets.by-duration"
                                    chartName={t('statistics.tickets_by_avg_duration')}
                                    chartTypes={['horizontalBar', 'verticalBar', 'line']}
                                    withPeriods
                                />
                                <ChartComponent
                                    datas={ticketsByAvgHandlingDuration}
                                    url="api.statistics.tickets.by-handling-duration"
                                    chartName={t('statistics.tickets_by_avg_duration')}
                                    chartTypes={['horizontalBar', 'verticalBar', 'line']}
                                    withPeriods
                                />
                            </div>
                        </>
                    )}

                    {showTab === 'assets' && (
                        <>
                            <h2>
                                {tChoice('assets.title', 2)}/{tChoice('locations.location', 2)}
                            </h2>
                            <div className="grid grid-cols-1 gap-5 md:grid-cols-2">
                                <ChartComponent
                                    datas={ticketsByAssetOrLocations}
                                    url="api.statistics.tickets.by-items"
                                    chartName={t('statistics.tickets_by_items')}
                                    chartTypes={['horizontalBar', 'verticalBar', 'doughnut']}
                                />
                            </div>
                        </>
                    )}
                </DashboardFiltersContext.Provider>
            </div>
        </AppLayout>
    );
}
