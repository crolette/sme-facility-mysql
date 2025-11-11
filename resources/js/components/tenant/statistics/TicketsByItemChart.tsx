import { useDashboardFilters } from '@/pages/tenants/statistics/IndexStatistics';
import axios from 'axios';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import { useEffect, useState } from 'react';
import { Bar, Doughnut } from 'react-chartjs-2';
import ButtonsChart from './buttonsChart';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

export const TicketsByItemChart = ({ ticketsByAssetOrLocations }: { ticketsByAssetOrLocations: [] }) => {
    const [type, setType] = useState<string>('bar');
    const { dateFrom, dateTo } = useDashboardFilters();

    const [labels, setLabels] = useState<string[]>(
        Object.entries(ticketsByAssetOrLocations).map(([index, item]) => {
            return item.reference_code;
        }),
    );

    const [dataCount, setDataCount] = useState<string[]>(
        Object.entries(ticketsByAssetOrLocations).map(([index, item]) => {
            return item.count;
        }),
    );

    const fetchTicketsByItem = async () => {
        try {
            const response = await axios.get(
                route('api.statistics.tickets.by-items', {
                    date_from: dateFrom,
                    date_to: dateTo,
                }),
            );
            setLabels(
                Object.entries(response.data.data).map(([index, item]) => {
                    return item.reference_code;
                }),
            );

            setDataCount(
                Object.entries(response.data.data).map(([index, item]) => {
                    return item.count;
                }),
            );
        } catch (error) {
            console.log(error);
        }
    };

    useEffect(() => {
        fetchTicketsByItem();
    }, [dateFrom, dateTo]);

    const options = {
        responsive: true,
        plugins: {
            legend: {
                display: false,
                position: 'bottom' as const,
            },
            title: {
                display: true,
                text: 'ticketsByAssetOrLocations',
            },
        },
    };

    const data = {
        labels: labels,
        datasets: [
            {
                label: 'ticketsByAssetOrLocations',
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
