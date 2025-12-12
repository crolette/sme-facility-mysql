import { cn } from '@/lib/utils';
import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function ButtonsChart({ setPeriod, activePeriod }: { setPeriod: (item: 'week' | 'month') => void; activePeriod: string | null }) {
    const { t } = useLaravelReactI18n();
    console.log(activePeriod);
    return (
        <div className="flex flex-col gap-2 md:flex-row">
            <p
                className={cn('cursor-pointer', activePeriod == 'week' || activePeriod == null ? 'font-bold underline' : '')}
                onClick={() => setPeriod('week')}
            >
                {t('statistics.per_week')}
            </p>

            <p className={cn('cursor-pointer', activePeriod == 'month' ? 'font-bold underline' : '')} onClick={() => setPeriod('month')}>
                {t('statistics.per_month')}
            </p>
        </div>
    );
}
