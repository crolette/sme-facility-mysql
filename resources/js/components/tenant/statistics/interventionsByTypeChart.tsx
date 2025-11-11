import { useDashboardFilters } from '@/pages/tenants/statistics/IndexStatistics';
import axios from 'axios';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import { useEffect, useState } from 'react';
import { Bar, Doughnut } from 'react-chartjs-2';
import ButtonsChart from './buttonsChart';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

export const InterventionsByTypeChart = ({ interventionsByType }: { interventionsByType: [] }) => {
    const [type, setType] = useState<string>('bar');
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
        }
    };

    useEffect(() => {
        if (dateFrom || dateTo) fetchInterventionsByType();
    }, [dateFrom, dateTo]);

    const data = {
        labels: labels,
        datasets: [
            {
                label: 'InterventionsByTypeChart',
                data: dataCount,
                backgroundColor: [
                    'oklch(45.633% 0.13478 263.563)',
                    'oklch(56.627% 0.09703 258.464)',
                    'oklch(42.935% 0.11812 258.322)',
                    'oklch(54.636% 0.08264 263.21)',
                    'oklch(56.983% 0.15547 258.607)',
                ],
                hoverOffset: 4,
            },
        ],
    };

    const options = {
        indexAxis: 'y' as const,

        responsive: true,
        plugins: {
            legend: {
                display: false,
                position: 'bottom' as const,
            },
            title: {
                display: true,
                text: 'InterventionsByType',
            },
        },
    };

    return (
        <>
            <div className="">
                <ButtonsChart setType={setType} />
                {type === 'bar' && (
                    <p>
                        <Bar options={options} data={data} />
                    </p>
                )}
                {type === 'doughnut' && <Doughnut options={options} data={data} />}
            </div>
        </>
    );
};
