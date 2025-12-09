import { useDashboardFilters } from '@/pages/tenants/statistics/IndexStatistics';
import axios from 'axios';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LineElement, LinearScale, PointElement, Title, Tooltip } from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useEffect, useState } from 'react';
import ButtonsChart from './buttonsChart';
import DoughnutChart from './DoughnutChart';
import HorizontalBarChart from './HorizontalBarChart';
import VerticalBarChart from './VerticalBarChart';

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

export const InterventionsByStatusChart = ({ interventionsByStatus }: { interventionsByStatus: [] }) => {
    const { t } = useLaravelReactI18n();
    const [type, setType] = useState<'doughnut' | 'horizontalBar' | 'verticalBar' | 'line'>('verticalBar');
    const [isFetching, setIsFetching] = useState(false);
    const { dateFrom, dateTo } = useDashboardFilters();

    const [labels, setLabels] = useState<string[]>(
        Object.entries(interventionsByStatus).map((item) => {
            return t(`common.status.${item[0]}`);
        }),
    );

    const [dataCount, setDataCount] = useState<string[]>(
        Object.entries(interventionsByStatus).map((item) => {
            return item[1];
        }),
    );

    const fetchInterventionsByStatus = async () => {
        setIsFetching(true);
        try {
            const response = await axios.get(
                route('api.statistics.interventions.by-status', {
                    date_from: dateFrom,
                    date_to: dateTo,
                }),
            );
            setLabels(
                Object.entries(response.data.data).map((item) => {
                    return item[0];
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
        if (dateFrom || dateTo) fetchInterventionsByStatus();
    }, [dateFrom, dateTo]);

    return (
        <>
            <div className="min-h-80">
                <ButtonsChart setType={setType} types={['horizontalBar', 'verticalBar', 'doughnut']} />
                {isFetching ? (
                    <p className="animate-pulse">{t('statistics.fetching_datas')}</p>
                ) : interventionsByStatus.length === 0 ? (
                    <p>{t('statistics.no_datas')}</p>
                ) : (
                    <>
                        {type === 'horizontalBar' && (
                            <HorizontalBarChart type={type} labels={labels} dataCount={dataCount} chartName="interventionsByStatus" />
                        )}

                        {type === 'verticalBar' && (
                            <VerticalBarChart type={type} labels={labels} dataCount={dataCount} chartName="interventionsByStatus" />
                        )}

                        {type === 'doughnut' && <DoughnutChart type={type} labels={labels} dataCount={dataCount} chartName="interventionsByStatus" />}
                    </>
                )}
            </div>
        </>
    );
};
