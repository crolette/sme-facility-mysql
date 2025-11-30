import { cn } from '@/lib/utils';
import { AssetsPaginated, ContractsPaginated, ProvidersPaginated } from '@/types';
import { Link } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Button } from './ui/button';

function Pagination({ items, className }: { items: ProvidersPaginated | AssetsPaginated | ContractsPaginated; className?: string }) {
    const { t } = useLaravelReactI18n();
    return (
        <div className={cn('mt-2 flex w-full flex-wrap gap-4', className)}>
            {items.links && items.links[0].url ? (
                <Link href={items.links[0].url}>
                    <Button variant={'secondary'}>{t('common.previous')}</Button>
                </Link>
            ) : (
                <div></div>
            )}
            {items.links && items.links[items.links.length - 1].url && (
                <Link href={items.links[items.links.length - 1].url}>
                    <Button variant={'secondary'}>{t('common.next')}</Button>
                </Link>
            )}
        </div>
    );
}

export { Pagination };
