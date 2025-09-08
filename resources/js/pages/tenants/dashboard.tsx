import { PlaceholderPattern } from '@/components/ui/placeholder-pattern';
import AppLayout from '@/layouts/app-layout';
import { Intervention, Maintainable, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { Cuboid, Ticket } from 'lucide-react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function TenantDashboard({
    tickets,
    assets,
    maintainables,
    interventions,
}: {
    tickets: number;
    assets: number;
    maintainables: Maintainable[];
    interventions: Intervention[];
}) {
    console.log(interventions);

    const getInterventionMaintainableUrl = (intervention: Intervention) => {
        if (intervention.ticket_id) {
            return (
                <a href={route('tenant.tickets.show', intervention.ticket_id)}>
                    {intervention.interventionable?.name} ({intervention.interventionable?.code})
                </a>
            );
        } else {
            switch (intervention.interventionable_type) {
                case 'App\\Models\\Tenants\\Asset':
                    return (
                        <a href={route('tenant.assets.show', intervention.interventionable?.reference_code)}>
                            {intervention.interventionable?.name} ({intervention.interventionable?.code})
                        </a>
                    );
                case 'App\\Models\\Tenants\\Site':
                    return (
                        <a href={route('tenant.sites.show', intervention.interventionable?.reference_code)}>
                            {intervention.interventionable?.name} ({intervention.interventionable?.code})
                        </a>
                    );
                case 'App\\Models\\Tenants\\Building':
                    return (
                        <a href={route('tenant.buildings.show', intervention.interventionable?.reference_code)}>
                            {intervention.interventionable?.name} ({intervention.interventionable?.code})
                        </a>
                    );
                case 'App\\Models\\Tenants\\Floor':
                    return (
                        <a href={route('tenant.floors.show', intervention.interventionable?.reference_code)}>
                            {intervention.interventionable?.name} ({intervention.interventionable?.code})
                        </a>
                    );
                case 'App\\Models\\Tenants\\Room':
                    return (
                        <a href={route('tenant.rooms.show', intervention.interventionable?.reference_code)}>
                            {intervention.interventionable?.name} ({intervention.interventionable?.code})
                        </a>
                    );
                default:
                    return `Sorry`;
            }
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="grid gap-4 md:grid-cols-3">
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative flex items-center justify-center overflow-hidden rounded-xl border p-4">
                        <div className="flex flex-col items-center justify-center">
                            <a href={route('tenant.tickets.index')}>
                                <Ticket size={64} className="m-auto" />
                                Open Tickets: {tickets}
                            </a>
                        </div>
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative flex items-center justify-center overflow-hidden rounded-xl border p-4">
                        <div className="flex flex-col items-center justify-center">
                            <a href={route('tenant.assets.index')} className="text-center">
                                <Cuboid size={64} className="m-auto" />
                                Total assets: {assets}
                            </a>
                        </div>
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border p-4">
                        <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" />
                    </div>
                </div>
                <div className="grid grid-cols-2 gap-4">
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border p-4 md:min-h-min">
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
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative min-h-[100vh] flex-1 overflow-hidden rounded-xl border p-4 md:min-h-min">
                        <h2>Next interventions</h2>
                        {interventions && interventions.length > 0 ? (
                            <div>
                                {interventions.map((intervention) => (
                                    <div key={intervention.id}>
                                        {getInterventionMaintainableUrl(intervention)}
                                        <p className="inline">
                                            {intervention.planned_at} -{intervention.priority} - {intervention.status} -{' '}
                                            {intervention.intervention_type.label}
                                        </p>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p>No interventions planned</p>
                        )}
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
