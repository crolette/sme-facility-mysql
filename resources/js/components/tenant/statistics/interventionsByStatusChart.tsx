import axios from 'axios';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import { ChartColumn, ChartPie } from 'lucide-react';
import { useState } from 'react';
import { Bar, Doughnut } from 'react-chartjs-2';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

export const InterventionsByStatusChart = ({ interventionsByStatus }: { interventionsByStatus: [] }) => {
    const [type, setType] = useState<string>('bar');
    const [datas, setDatas] = useState(null);

    const fetchInterventionsByStatus = async () => {
        console.log('fetchInterventionsByStatus');
        try {
            const response = await axios.get(route('api.statistics.interventions.by-status'));
            console.log(response.data);
        } catch (error) {
            console.log(error);
        }
    };

    fetchInterventionsByStatus();

    const labels = Object.entries(interventionsByStatus).map((item) => {
        return item[0];
    });

    const dataCount = Object.entries(interventionsByStatus).map((item) => {
        return item[1];
    });
    const options = {
        indexAxis: 'y' as const,
        responsive: true,
        plugins: {
            legend: {
                display: false,
                position: 'top' as const,
            },
            title: {
                display: true,
                text: 'InterventionsByStatus',
            },
        },
    };

    const data = {
        labels: labels,
        datasets: [
            {
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

    return (
        <>
            <div>
                <div>
                    <ChartColumn onClick={() => setType('bar')} />
                    <ChartPie onClick={() => setType('doughnut')} />
                </div>
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
