import { ContractsList } from '@/components/tenant/contractsList';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Contract } from '@/types';
import { Head } from '@inertiajs/react';
import { PlusCircle } from 'lucide-react';

export default function IndexContracts({ items }: { items: Contract[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index contracts`,
            href: `/contracts`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Contracts" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route('tenant.contracts.create')}>
                    <Button>
                        <PlusCircle />
                        Create</Button>
                </a>
                <ContractsList getUrl={'api.contracts.index'}  items={items} editable />
            </div>
        </AppLayout>
    );
}
