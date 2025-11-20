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

export const InterventionsByAssigneeChart = ({ interventionsByAssignee }: { interventionsByAssignee: [] }) => {
    const { t } = useLaravelReactI18n();
    const [type, setType] = useState<'doughnut' | 'horizontalBar' | 'verticalBar' | 'line'>('verticalBar');
    const [isFetching, setIsFetching] = useState(false);
    const { dateFrom, dateTo } = useDashboardFilters();

    const [labels, setLabels] = useState<string[]>(
        interventionsByAssignee.map((item) => {
            return item.name;
        }),
    );

    const [dataCount, setDataCount] = useState<string[]>(
        interventionsByAssignee.map((item) => {
            return item.count;
        }),
    );

    const fetchInterventionsByAssignee = async () => {
        setIsFetching(true);
        try {
            const response = await axios.get(
                route('api.statistics.interventions.by-assignee', {
                    date_from: dateFrom,
                    date_to: dateTo,
                }),
            );

            setLabels(
                Object.entries(response.data.data).map((item) => {
                    return item[1].name;
                }),
            );

            setDataCount(
                Object.entries(response.data.data).map((item) => {
                    return item[1].count;
                }),
            );
        } catch (error) {
            console.log(error);
        } finally {
            setIsFetching(false);
        }
    };

    useEffect(() => {
        if (dateFrom || dateTo) fetchInterventionsByAssignee();
    }, [dateFrom, dateTo]);

    return (
        <>
            <div className="min-h-80">
                <ButtonsChart setType={setType} types={['horizontalBar', 'verticalBar', 'doughnut']} />
                {isFetching ? (
                    <p className="animate-pulse">{t('statistics.fetching_datas')}</p>
                ) : interventionsByAssignee.length === 0 ? (
                    <p>{t('statistics.no_datas')}</p>
                ) : (
                    <>
                        {type === 'horizontalBar' && (
                            <HorizontalBarChart type={type} labels={labels} dataCount={dataCount} chartName="interventionsByAssignee" />
                        )}

                        {type === 'verticalBar' && (
                            <VerticalBarChart type={type} labels={labels} dataCount={dataCount} chartName="interventionsByAssignee" />
                        )}

                        {type === 'doughnut' && (
                            <DoughnutChart type={type} labels={labels} dataCount={dataCount} chartName="interventionsByAssignee" />
                        )}
                    </>
                )}
            </div>
        </>
    );
};
