import { cn } from '@/lib/utils';
import { ChartBar, ChartColumn, ChartLine, ChartPie } from 'lucide-react';

export default function ButtonsChart({
    setType,
    types,
    activeType,
}: {
    setType: (item: 'doughnut' | 'horizontalBar' | 'verticalBar' | 'line') => void;
    types: string[];
    activeType: 'doughnut' | 'horizontalBar' | 'verticalBar' | 'line';
}) {
    return (
        <div className="flex gap-2">
            {types && types.find((type) => type == 'line') && (
                <ChartLine
                    onClick={() => setType('line')}
                    size={32}
                    className={cn('cursor-pointer rounded-sm border border-blue-200 p-1', activeType === 'line' ? 'bg-chart-2' : 'hover:bg-chart-3')}
                />
            )}
            {types && types.find((type) => type == 'horizontalBar') && (
                <ChartBar
                    onClick={() => setType('horizontalBar')}
                    size={32}
                    className={cn(
                        'cursor-pointer rounded-sm border border-blue-200 p-1',
                        activeType === 'horizontalBar' ? 'bg-chart-2' : 'hover:bg-chart-3',
                    )}
                />
            )}
            {types && types.find((type) => type == 'verticalBar') && (
                <ChartColumn
                    onClick={() => setType('verticalBar')}
                    size={32}
                    className={cn(
                        'cursor-pointer rounded-sm border border-blue-200 p-1',
                        activeType === 'verticalBar' ? 'bg-chart-2' : 'hover:bg-chart-3',
                    )}
                />
            )}
            {types && types.find((type) => type == 'doughnut') && (
                <ChartPie
                    onClick={() => setType('doughnut')}
                    size={32}
                    className={cn(
                        'cursor-pointer rounded-sm border border-blue-200 p-1',
                        activeType === 'doughnut' ? 'bg-chart-2' : 'hover:bg-chart-3',
                    )}
                />
            )}
        </div>
    );
}
