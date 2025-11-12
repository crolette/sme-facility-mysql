import { useChartOptions } from '@/hooks/useChartOptions';
import { useDashboardFilters } from '@/pages/tenants/statistics/IndexStatistics';
import axios from 'axios';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LineElement, LinearScale, PointElement, Title, Tooltip } from 'chart.js';
import { useEffect, useState } from 'react';
import { Bar, Doughnut, Line } from 'react-chartjs-2';
import ButtonsChart from './buttonsChart';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend);

export const TicketsByPeriodChart = ({ ticketsByPeriod }: { ticketsByPeriod: [] }) => {
    const [type, setType] = useState<'doughnut' | 'horizontalBar' | 'verticalBar' | 'line'>('line');
    const [isFetching, setIsFetching] = useState(false);
    const { dateFrom, dateTo } = useDashboardFilters();
    const [period, setPeriod] = useState<string | null>(null);
    const [labels, setLabels] = useState<string[]>(
        Object.entries(ticketsByPeriod).map((item) => {
            return 'Week ' + item[0];
        }),
    );

    const [dataCount, setDataCount] = useState<string[]>(
        Object.entries(ticketsByPeriod).map((item) => {
            return item[1];
        }),
    );

    const fetchTicketsByPeriod = async () => {
        setIsFetching(true);
        try {
            const response = await axios.get(
                route('api.statistics.tickets.by-period', {
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
        if (period || dateFrom || dateTo) fetchTicketsByPeriod();
    }, [period, dateFrom, dateTo]);

    const { datasetStyle, baseOptions } = useChartOptions('TicketsByPeriod', type);

    const data = {
        labels: labels,
        datasets: [
            {
                label: 'TicketsByPeriod',
                data: dataCount,
                ...datasetStyle,
            },
        ],
    };

    return (
        <>
            <div>
                <div className="flex justify-between">
                    <ButtonsChart setType={setType} types={['horizontalBar', 'verticalBar', 'line']} />
                    <div className="flex gap-2">
                        <p className={'cursor-pointer'} onClick={() => setPeriod('week')}>
                            By Week
                        </p>
                        <p className={'cursor-pointer'} onClick={() => setPeriod('month')}>
                            By Month
                        </p>
                    </div>
                </div>
                {isFetching ? (
                    <p className="animate-pulse">Fetching datas...</p>
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
