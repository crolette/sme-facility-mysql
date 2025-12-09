import { useChartOptions } from '@/hooks/useChartOptions';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LineElement, LinearScale, PointElement, Title, Tooltip } from 'chart.js';
import { Doughnut } from 'react-chartjs-2';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend);

export default function DoughnutChart({
    type,
    labels,
    dataCount,
    chartName = '',
}: {
    type: 'doughnut' | 'horizontalBar' | 'verticalBar' | 'line';
    labels: string[];
    dataCount: string[];
    chartName: string;
}) {
    const { datasetStyle, baseOptions } = useChartOptions(chartName, type, dataCount);

    const data = {
        labels: labels,
        datasets: [
            {
                label: chartName,
                data: dataCount,
                ...datasetStyle,
            },
        ],
    };

    return (
        <div className="h-96 w-xs lg:w-md xl:w-xl ">
            <Doughnut
                width={100}
                height={100}
                options={{
                    ...baseOptions,
                    plugins: {
                        ...baseOptions.plugins,
                        legend: {
                            display: true,
                            position: 'bottom',
                        },
                        datalabels: {
                            ...baseOptions.plugins.datalabels,
                            anchor: 'center', // Position the label near the bar's edge
                            align: 'center', // Align the label to the top of the bar
                        },
                    },

                    scales: {
                        ...baseOptions.scales,
                        x: {
                            grid: { display: false },
                            ticks: {
                                display: false,
                            },
                        },
                        y: {
                            grid: { display: false },
                            ticks: {
                                display: false,
                            },
                        },
                    },
                }}
                data={data}
            />
        </div>
    );
}
