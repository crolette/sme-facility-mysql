import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import { ChartColumn, ChartPie } from 'lucide-react';
import { useState } from 'react';
import { Bar, Doughnut } from 'react-chartjs-2';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

export const InterventionsByStatusChart = ({ interventionsByStatus }: { interventionsByStatus: [] }) => {
    const [type, setType] = useState<string>('bar');

    const labels = Object.entries(interventionsByStatus).map(([item, count]) => {
        return item;
    });

    const dataCount = Object.entries(interventionsByStatus).map(([item, count]) => {
        return count;
    });

    const options = {
        indexAxis: 'y' as const,
        responsive: true,
        plugins: {
            legend: {
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
                label: 'My First Dataset',
                data: dataCount,
                backgroundColor: ['rgb(255, 99, 132)', 'rgb(255, 99, 132)', 'rgb(255, 27, 132)', 'rgb(54, 162, 235)', 'rgb(255, 9, 86)'],
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
