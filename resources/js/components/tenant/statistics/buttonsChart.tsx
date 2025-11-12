import { ChartBar, ChartColumn, ChartLine, ChartPie } from 'lucide-react';

export default function ButtonsChart({
    setType,
    types,
}: {
    setType: (item: 'doughnut' | 'horizontalBar' | 'verticalBar' | 'line') => void;
    types: string[];
}) {
    return (
        <div className="flex gap-2">
            {types && types.find((type) => type == 'line') && <ChartLine onClick={() => setType('line')} size={20} />}
            {types && types.find((type) => type == 'horizontalBar') && <ChartBar onClick={() => setType('horizontalBar')} size={20} />}
            {types && types.find((type) => type == 'verticalBar') && <ChartColumn onClick={() => setType('verticalBar')} size={20} />}
            {types && types.find((type) => type == 'doughnut') && <ChartPie onClick={() => setType('doughnut')} size={20} />}
        </div>
    );
}
