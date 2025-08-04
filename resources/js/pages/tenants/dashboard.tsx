import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { Maintainable, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Cuboid, Ticket } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function TenantDashboard({ tickets, assets, maintainables }: { tickets: number; assets: number; maintainables: Maintainable[] }) {
    console.log(maintainables);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative flex aspect-video items-center justify-center overflow-hidden rounded-xl border">
                        <div className="flex flex-col items-center justify-center">
                            <a href={route('tenant.tickets.index')}>
                                <Ticket size={64} className="m-auto" />
                                Open Tickets: {tickets}
                            </a>
                        </div>
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative flex aspect-video items-center justify-center overflow-hidden rounded-xl border">
                        <div className="flex flex-col items-center justify-center">
                            <a href={route('tenant.assets.index')} className="text-center">
                                <Cuboid size={64} className="m-auto" />
                                Total assets: {assets}
                            </a>
                        </div>
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative aspect-video overflow-hidden rounded-xl border">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                </div>
                <div className="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border p-2 md:min-h-min">
                    <h2>Next maintenances</h2>
                    {maintainables && maintainables.length > 0 ? (
                        <div>
                            {maintainables.map((maintainable) => (
                                <div key={maintainable.id}>
                                    <a href={route('tenant.assets.show', maintainable.maintainable.reference_code)}>
                                        {maintainable.name} ({maintainable.maintainable.reference_code}) - {maintainable.maintenance_frequency} -{' '}
                                        {maintainable.next_maintenance_date}
                                    </a>
                                </div>
                            ))}
                        </div>
                    ) : (
                        <p>No maintenance planned</p>
                    )}
                    {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                </div>
            </div>
        </AppLayout>
    );
}
