import axios from 'axios';
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js';
import { ChartColumn, ChartPie } from 'lucide-react';
import { useEffect, useState } from 'react';
import { Bar, Doughnut } from 'react-chartjs-2';

ChartJS.register(ArcElement, Tooltip, Legend, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend);

export const TicketsByPeriodChart = ({ ticketsByPeriod }: { ticketsByPeriod: [] }) => {
    const [type, setType] = useState<string>('bar');
    const [period, setPeriod] = useState('week');
    const [labels, setLabels] = useState(
        Object.entries(ticketsByPeriod).map((item) => {
            return 'Week ' + item[0];
        }),
    );
    const [dataCount, setDataCount] = useState(
        Object.entries(ticketsByPeriod).map((item) => {
            return item[1];
        }),
    );

    const fetchTicketsByPeriod = async () => {
        console.log('fetchTicketsByPeriod');
        try {
            const response = await axios.get(route('api.statistics.tickets.by-period', { period: period }));
            console.log(response.data);

            setLabels(
                Object.entries(response.data.data).map((item) => {
                    return period === 'week' ? 'Week ' + item[0] : item[0];
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
        fetchTicketsByPeriod();
    }, [period]);

    const options = {
        responsive: true,
        plugins: {
            legend: {
                display: false,
                position: 'top' as const,
            },
            title: {
                display: true,
                text: 'TicketsByPeriod',
            },
        },
    };

    const data = {
        labels: labels,
        datasets: [
            {
                label: 'TicketsByPeriod',
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
                <div>
                    <p onClick={() => setPeriod('week')}>Week</p>
                    <p onClick={() => setPeriod('month')}>Month</p>
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
