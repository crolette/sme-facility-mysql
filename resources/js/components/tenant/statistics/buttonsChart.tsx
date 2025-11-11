import { ChartColumn, ChartPie } from 'lucide-react';

export default function ButtonsChart({ setType }: { setType: (item: string) => void }) {
    return (
        <div className="flex gap-2">
            <ChartColumn onClick={() => setType('bar')} size={20} />
            <ChartPie onClick={() => setType('doughnut')} size={20} />
        </div>
    );
}
