import { cn } from '@/lib/utils';
import { PaginatedData } from '@/types';
import { Button } from './ui/button';

function PaginationAPI({ items, pageToLoad, className }: { items: PaginatedData; pageToLoad: (page: number) => void; className?: string }) {
    return (
        <div className={cn('mt-2 flex w-full flex-wrap gap-6', className)}>
            {items.links[0].page && (
                <Button variant={'secondary'} onClick={() => pageToLoad(items.links[0].page)}>
                    Previous
                </Button>
            )}
            {items.links[items.links.length - 1].page && (
                <Button variant={'secondary'} onClick={() => pageToLoad(items.links[items.links.length - 1].page)}>
                    Next
                </Button>
            )}
        </div>
    );
}

export { PaginationAPI };
