import { cn } from '@/lib/utils';
import { PaginatedData } from '@/types';
import { Link } from '@inertiajs/react';
import { Button } from './ui/button';

function Pagination({ items, className }: { items: PaginatedData; className?: string }) {
    return (
        <div className={cn('flex w-full flex-wrap justify-between', className)}>
            {items.links && items.links[0].url ? (
                <Link href={items.links[0].url}>
                    <Button variant={'secondary'}>Previous</Button>
                </Link>
            ) : (
                <div></div>
            )}
            {items.links && items.links[items.links.length - 1].url && (
                <Link href={items.links[items.links.length - 1].url}>
                    <Button variant={'secondary'}>Next</Button>
                </Link>
            )}
        </div>
    );
}

export { Pagination };
