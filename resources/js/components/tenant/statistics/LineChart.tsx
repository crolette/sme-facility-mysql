import { useChartOptions } from '@/hooks/useChartOptions';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LineElement, LinearScale, PointElement, Title, Tooltip } from 'chart.js';
import { Line } from 'react-chartjs-2';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, PointElement, LineElement, BarElement, Title, Tooltip, Legend);

export default function LineChart({
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
            <Line
                width={100}
                height={100}
                options={{
                    ...baseOptions,
                    maintainAspectRatio: false,
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
