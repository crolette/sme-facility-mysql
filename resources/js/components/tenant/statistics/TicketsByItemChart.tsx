import { useChartOptions } from '@/hooks/useChartOptions';
import { useDashboardFilters } from '@/pages/tenants/statistics/IndexStatistics';
import axios from 'axios';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LineElement, LinearScale, PointElement, Title, Tooltip } from 'chart.js';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useEffect, useState } from 'react';
import { Bar, Doughnut, Line } from 'react-chartjs-2';
import ButtonsChart from './buttonsChart';

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

    const { datasetStyle, baseOptions } = useChartOptions('ticketsByAssetOrLocations', type);

    const data = {
        labels: labels,
        datasets: [
            {
                label: 'ticketsByAssetOrLocations',
                data: dataCount,
                ...datasetStyle,
            },
        ],
    };

    return (
        <div className="min-h-80">
            <ButtonsChart setType={setType} types={['horizontalBar', 'verticalBar', 'line']} />
            {isFetching ? (
                <p className="animate-pulse">{t('statistics.fetching_datas')}</p>
            ) : ticketsByAssetOrLocations.length === 0 ? (
                <p>{t('statistics.no_datas')}</p>
            ) : (
                <>
                    {(type === 'horizontalBar' || type === 'verticalBar') && (
                        <p>
                            <Bar options={baseOptions} data={data} />
                        </p>
                    )}
                    {type === 'line' && <Line options={baseOptions} data={data} />}
                    {type === 'doughnut' && <Doughnut options={baseOptions} data={data} />}
                </>
            )}
        </div>
    );
};
