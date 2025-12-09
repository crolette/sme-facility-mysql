import { useDashboardFilters } from '@/pages/tenants/statistics/IndexStatistics';
import axios from 'axios';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LineElement, LinearScale, PointElement, Title, Tooltip } from 'chart.js';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useEffect, useState } from 'react';
import ButtonsChart from './buttonsChart';
import ButtonsPeriod from './buttonsPeriod';
import HorizontalBarChart from './HorizontalBarChart';
import LineChart from './LineChart';
import VerticalBarChart from './VerticalBarChart';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend);

export const TicketsByPeriodChart = ({ ticketsByPeriod }: { ticketsByPeriod: [] }) => {
    const { t, tChoice } = useLaravelReactI18n();
    const [type, setType] = useState<'doughnut' | 'horizontalBar' | 'verticalBar' | 'line'>('line');
    const [isFetching, setIsFetching] = useState(false);
    const { dateFrom, dateTo } = useDashboardFilters();
    const [period, setPeriod] = useState<string | null>(null);
    const [labels, setLabels] = useState<string[]>(
        Object.entries(ticketsByPeriod).map((item) => {
            return `${t('statistics.week')}` + ' ' + item[0];
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
                    return period === 'week' ? `${t('statistics.week')}` + item[0] : item[0];
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

    return (
        <div className="min-h-80">
            <div className="flex justify-between">
                <ButtonsChart setType={setType} types={['horizontalBar', 'verticalBar', 'line']} />
                <ButtonsPeriod setPeriod={setPeriod} />
            </div>
            {isFetching ? (
                <p className="animate-pulse">{t('statistics.fetching_datas')}</p>
            ) : ticketsByPeriod.length === 0 ? (
                <p>{t('statistics.no_datas')}</p>
            ) : (
                <>
                    {type === 'horizontalBar' && <HorizontalBarChart type={type} labels={labels} dataCount={dataCount} chartName="TicketsByPeriod" />}

                    {type === 'verticalBar' && <VerticalBarChart type={type} labels={labels} dataCount={dataCount} chartName="TicketsByPeriod" />}

                    {type === 'line' && <LineChart type={type} labels={labels} dataCount={dataCount} chartName="TicketsByPeriod" />}
                </>
            )}
        </div>
    );
};
