import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function ButtonsChart({ setPeriod }: { setPeriod: (item: 'week' | 'month') => void }) {
    const { t } = useLaravelReactI18n();

    return (
        <div className="flex flex-col gap-2 md:flex-row">
            <p className={'cursor-pointer'} onClick={() => setPeriod('week')}>
                {t('statistics.per_week')}
            </p>
            <p className={'cursor-pointer'} onClick={() => setPeriod('month')}>
                {t('statistics.per_month')}
            </p>
        </div>
    );
}
