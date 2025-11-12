import { useChartOptions } from '@/hooks/useChartOptions';
import { useDashboardFilters } from '@/pages/tenants/statistics/IndexStatistics';
import axios from 'axios';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LineElement, LinearScale, PointElement, Title, Tooltip } from 'chart.js';
import { useEffect, useState } from 'react';
import { Bar, Doughnut, Line } from 'react-chartjs-2';
import ButtonsChart from './buttonsChart';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend);

export const InterventionsByTypeChart = ({ interventionsByType }: { interventionsByType: [] }) => {
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

    const { datasetStyle, baseOptions } = useChartOptions('InterventionsByType', type);

    const data = {
        labels: labels,
        datasets: [
            {
                label: 'InterventionsByTypeChart',
                data: dataCount,
                ...datasetStyle,
            },
        ],
    };

    return (
        <>
            <div className="">
                <ButtonsChart setType={setType} types={['horizontalBar', 'verticalBar', 'doughnut']} />
                {isFetching ? (
                    <p className="animate-pulse">Fetching datas...</p>
                ) : interventionsByType.length === 0 ? (
                    <p>No datas</p>
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
