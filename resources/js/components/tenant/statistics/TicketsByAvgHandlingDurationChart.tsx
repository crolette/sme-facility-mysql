import { useChartOptions } from '@/hooks/useChartOptions';
import { useDashboardFilters } from '@/pages/tenants/statistics/IndexStatistics';
import axios from 'axios';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LineElement, LinearScale, PointElement, Title, Tooltip } from 'chart.js';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useEffect, useState } from 'react';
import { Bar, Doughnut, Line } from 'react-chartjs-2';
import ButtonsChart from './buttonsChart';
import ButtonsPeriod from './buttonsPeriod';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend);

export const TicketsByAvgHandlingDurationChart = ({ ticketsByAvgHandlingDuration }: { ticketsByAvgHandlingDuration: [] }) => {
    const { t, tChoice } = useLaravelReactI18n();
    const [type, setType] = useState<'doughnut' | 'horizontalBar' | 'verticalBar' | 'line'>('line');
    const [isFetching, setIsFetching] = useState(false);
    const { dateFrom, dateTo } = useDashboardFilters();
    const [period, setPeriod] = useState<string | null>(null);
    const [labels, setLabels] = useState<string[]>(
        Object.entries(ticketsByAvgHandlingDuration).map((item) => {
            return 'Week ' + item[0];
        }),
    );

    const [dataCount, setDataCount] = useState<string[]>(
        Object.entries(ticketsByAvgHandlingDuration).map((item) => {
            return item[1];
        }),
    );

    const fetchTicketsByAvgHandlingDuration = async () => {
        setIsFetching(true);
        try {
            const response = await axios.get(
                route('api.statistics.tickets.by-handling-duration', {
                    period: period,
                    date_from: dateFrom,
                    date_to: dateTo,
                }),
            );
            setLabels(
                Object.entries(response.data.data).map((item) => {
                    return period === 'week' ? 'Week ' + item[0] : item[0];
                }),
            );

            setDataCount(
                Object.entries(response.data.data).map((item) => {
                    return item[1];
                }),
            );
        } catch (error) {
            console.log(error);
        } finally {
            setIsFetching(false);
        }
    };

    useEffect(() => {
        if (period || dateFrom || dateTo) fetchTicketsByAvgHandlingDuration();
    }, [period, dateFrom, dateTo]);

    const { datasetStyle, baseOptions } = useChartOptions('ticketsByAvgHandlingDuration', type);

    const data = {
        labels: labels,
        datasets: [
            {
                label: 'ticketsByAvgHandlingDuration',
                data: dataCount,
                ...datasetStyle,
            },
        ],
    };

    return (
        <>
            <div className="min-h-80">
                <div className="flex justify-between">
                    <ButtonsChart setType={setType} types={['horizontalBar', 'verticalBar', 'line']} />
                    <ButtonsPeriod setPeriod={setPeriod} />
                </div>
                {isFetching ? (
                    <p className="animate-pulse">{t('statistics.fetching_datas')}</p>
                ) : ticketsByAvgHandlingDuration.length === 0 ? (
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
        </>
    );
};
