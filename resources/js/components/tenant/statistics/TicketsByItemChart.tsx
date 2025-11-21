import { useDashboardFilters } from '@/pages/tenants/statistics/IndexStatistics';
import axios from 'axios';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LineElement, LinearScale, PointElement, Title, Tooltip } from 'chart.js';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useEffect, useState } from 'react';
import ButtonsChart from './buttonsChart';
import DoughnutChart from './DoughnutChart';
import HorizontalBarChart from './HorizontalBarChart';
import VerticalBarChart from './VerticalBarChart';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend);

export const TicketsByItemChart = ({ ticketsByAssetOrLocations }: { ticketsByAssetOrLocations: [] }) => {
    const { t } = useLaravelReactI18n();
    const [type, setType] = useState<'doughnut' | 'horizontalBar' | 'verticalBar' | 'line'>('verticalBar');
    const [isFetching, setIsFetching] = useState(false);
    const { dateFrom, dateTo } = useDashboardFilters();

    const [labels, setLabels] = useState<string[]>(
        Object.entries(ticketsByAssetOrLocations).map(([index, item]) => {
            return item.reference_code;
        }),
    );

    const [dataCount, setDataCount] = useState<string[]>(
        Object.entries(ticketsByAssetOrLocations).map(([index, item]) => {
            return item.count;
        }),
    );

    const fetchTicketsByItem = async () => {
        setIsFetching(true);
        try {
            const response = await axios.get(
                route('api.statistics.tickets.by-items', {
                    date_from: dateFrom,
                    date_to: dateTo,
                }),
            );
            setLabels(
                Object.entries(response.data.data).map(([index, item]) => {
                    return item.reference_code;
                }),
            );

            setDataCount(
                Object.entries(response.data.data).map(([index, item]) => {
                    return item.count;
                }),
            );
        } catch (error) {
            console.log(error);
        } finally {
            setIsFetching(false);
        }
    };

    useEffect(() => {
        if (dateFrom || dateTo) fetchTicketsByItem();
    }, [dateFrom, dateTo]);

    return (
        <div className="min-h-80">
            <ButtonsChart setType={setType} types={['horizontalBar', 'verticalBar', 'doughnut']} />
            {isFetching ? (
                <p className="animate-pulse">{t('statistics.fetching_datas')}</p>
            ) : ticketsByAssetOrLocations.length === 0 ? (
                <p>{t('statistics.no_datas')}</p>
            ) : (
                <>
                    {type === 'horizontalBar' && (
                        <HorizontalBarChart type={type} labels={labels} dataCount={dataCount} chartName="TicketsByAssetOrLocations" />
                    )}

                    {type === 'verticalBar' && (
                        <VerticalBarChart type={type} labels={labels} dataCount={dataCount} chartName="TicketsByAssetOrLocations" />
                    )}

                    {type === 'doughnut' && <DoughnutChart type={type} labels={labels} dataCount={dataCount} chartName="TicketsByAssetOrLocations" />}
                </>
            )}
        </div>
    );
};
