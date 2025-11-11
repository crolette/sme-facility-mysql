import { InterventionsByAssigneeChart } from '@/components/tenant/statistics/interventionsByAssigneeChart';
import { InterventionsByStatusChart } from '@/components/tenant/statistics/interventionsByStatusChart';
import { InterventionsByTypeChart } from '@/components/tenant/statistics/interventionsByTypeChart';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';

export default function IndexStatistics({
    interventionsByStatus,
    interventionsByType,
    interventionsByAssignee,
}: {
    interventionsStatusCount: [];
    interventionsByType: [];
    interventionsByAssignee: [];
}) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index statistics`,
            href: `/statistics`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Statistics" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h1>Statistics</h1>
                <div className="border-accent flex gap-10 border-b-2">
                    <InterventionsByTypeChart interventionsByType={interventionsByType} />
                    <InterventionsByStatusChart interventionsByStatus={interventionsByStatus} />
                    <InterventionsByAssigneeChart interventionsByAssignee={interventionsByAssignee} />
                </div>
            </div>
        </AppLayout>
    );
}
