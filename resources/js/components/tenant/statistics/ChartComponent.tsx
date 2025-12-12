import { useToast } from '@/components/ToastrContext';
import { useDashboardFilters } from '@/pages/tenants/statistics/IndexStatistics';
import axios from 'axios';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LineElement, LinearScale, PointElement, Title, Tooltip } from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useEffect, useState } from 'react';
import ButtonsChart from './buttonsChart';
import ButtonsPeriod from './buttonsPeriod';
import DoughnutChart from './DoughnutChart';
import HorizontalBarChart from './HorizontalBarChart';
import LineChart from './LineChart';
import VerticalBarChart from './VerticalBarChart';

type chartTypes = 'doughnut' | 'horizontalBar' | 'verticalBar' | 'line';

ChartJS.register(
    ArcElement,
    Tooltip,
    Legend,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    BarElement,
    Title,
    Tooltip,
    Legend,
    ChartDataLabels,
);

export const ChartComponent = ({
    datas,
    withPeriods = false,
    chartTypes,
    url,
    chartName,
}: {
    datas: [];
    withPeriods?: boolean;
    chartTypes: chartTypes[];
    url: string;
    chartName: string;
}) => {
    const { t } = useLaravelReactI18n();
    const { showToast } = useToast();
    const [type, setType] = useState<'doughnut' | 'horizontalBar' | 'verticalBar' | 'line'>(withPeriods ? 'line' : 'verticalBar');
    const [isFetching, setIsFetching] = useState(false);
    const { dateFrom, dateTo } = useDashboardFilters();

    const [period, setPeriod] = useState<string | null>(null);
    const [labels, setLabels] = useState<string[]>(
        Object.entries(datas).map((data) => {
            return withPeriods ? `W` + data[0] : data[0];
        }),
    );

    const [dataCount, setDataCount] = useState<string[]>(
        Object.entries(datas).map((data) => {
            return data[1];
        }),
    );

    const fetchDatas = async () => {
        setIsFetching(true);
        try {
            const response = await axios.get(
                route(url, {
                    period: period,
                    date_from: dateFrom,
                    date_to: dateTo,
                }),
            );
            setLabels(
                Object.entries(response.data.data).map((item) => {
                    return withPeriods ? (period === 'week' ? `W` + item[0] : item[0]) : item[0];
                }),
            );

            setDataCount(
                Object.entries(response.data.data).map((item) => {
                    return item[1];
                }),
            );
        } catch (error) {
            showToast(error.response.data.message);
        } finally {
            setIsFetching(false);
        }
    };
    useEffect(() => {
        if (period || dateFrom || dateTo) fetchDatas();
    }, [dateFrom, dateTo, period]);

    return (
        <>
            <div className="border-accent min-h-80 w-full rounded-md border p-10">
                <div className="flex justify-between">
                    <ButtonsChart setType={setType} types={chartTypes} activeType={type} />
                    {withPeriods && <ButtonsPeriod setPeriod={setPeriod} activePeriod={period} />}
                </div>
                {isFetching ? (
                    <p className="animate-pulse">{t('statistics.fetching_datas')}</p>
                ) : datas.length === 0 ? (
                    <p>{t('statistics.no_datas')}</p>
                ) : (
                    <>
                        {type === 'verticalBar' && <VerticalBarChart type={type} labels={labels} dataCount={dataCount} chartName={chartName} />}

                        {type === 'horizontalBar' && <HorizontalBarChart type={type} labels={labels} dataCount={dataCount} chartName={chartName} />}

                        {type === 'doughnut' && <DoughnutChart type={type} labels={labels} dataCount={dataCount} chartName={chartName} />}

                        {type === 'line' && <LineChart type={type} labels={labels} dataCount={dataCount} chartName={chartName} />}
                    </>
                )}
            </div>
        </>
    );
};
