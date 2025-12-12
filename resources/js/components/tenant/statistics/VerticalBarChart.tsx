import { useChartOptions } from '@/hooks/useChartOptions';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LineElement, LinearScale, PointElement, Title, Tooltip } from 'chart.js';
import ChartDataLabels from 'chartjs-plugin-datalabels';
import { Bar } from 'react-chartjs-2';

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

export default function VerticalBarChart({
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
    const { datasetStyle, baseOptions, max } = useChartOptions(chartName, type, dataCount);

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
        <div className="h-96">
            <Bar
                width={100}
                height={100}
                options={{
                    ...baseOptions,
                    maintainAspectRatio: false,
                    plugins: {
                        ...baseOptions.plugins,
                        datalabels: {
                            ...baseOptions.plugins.datalabels,
                            anchor: 'end', // Position the label near the bar's edge
                            align: 'end', // Align the label to the top of the bar
                        },
                    },

                    scales: {
                        ...baseOptions.scales,
                        y: {
                            ...baseOptions.scales.y,
                            suggestedMin: 0,
                            suggestedMax: max,
                        },
                    },
                }}
                data={data}
            />
        </div>
    );
}
