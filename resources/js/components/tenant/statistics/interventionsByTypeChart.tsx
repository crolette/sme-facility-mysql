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

export const InterventionsByTypeChart = ({ interventionsByType }: { interventionsByType: [] }) => {
    const { t } = useLaravelReactI18n();
    const [type, setType] = useState<'doughnut' | 'horizontalBar' | 'verticalBar' | 'line'>('verticalBar');
    const [isFetching, setIsFetching] = useState(false);
    const { dateFrom, dateTo } = useDashboardFilters();

    const [labels, setLabels] = useState<string[]>(
        Object.entries(interventionsByType).map((item) => {
            return item[0];
        }),
    );

    const [dataCount, setDataCount] = useState<string[]>(
        Object.entries(interventionsByType).map((item) => {
            return item[1];
        }),
    );

    const fetchInterventionsByType = async () => {
        setIsFetching(true);
        try {
            const response = await axios.get(
                route('api.statistics.interventions.by-type', {
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
        if (dateFrom || dateTo) fetchInterventionsByType();
    }, [dateFrom, dateTo]);

    return (
        <div className="min-h-80">
            <ButtonsChart setType={setType} types={['horizontalBar', 'verticalBar', 'doughnut']} />
            {isFetching ? (
                <p className="animate-pulse">{t('statistics.fetching_datas')}</p>
            ) : interventionsByType.length === 0 ? (
                <p>{t('statistics.no_datas')}</p>
            ) : (
                <>
                    {type === 'horizontalBar' && (
                        <HorizontalBarChart type={type} labels={labels} dataCount={dataCount} chartName="InterventionsByType" />
                    )}

                    {type === 'verticalBar' && <VerticalBarChart type={type} labels={labels} dataCount={dataCount} chartName="InterventionsByType" />}

                    {type === 'doughnut' && <DoughnutChart type={type} labels={labels} dataCount={dataCount} chartName="InterventionsByType" />}
                </>
            )}
        </div>
    );
};
