import { Pill } from '@/components/ui/pill';
import AppLayout from '@/layouts/app-layout';
import { Intervention, Maintainable, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Cuboid, HardDrive, Ticket, Wrench } from 'lucide-react';

export default function TenantDashboard({
    counts,
    maintainables,
    interventions,
    overdueMaintenances,
    overdueInterventions,
    diskSizes,
}: {
    counts: { ticketsCount: number; assetsCount: number; interventionsCount: number };
    maintainables: Maintainable[];
    interventions: Intervention[];
    overdueMaintenances: Maintainable[];
    overdueInterventions: Intervention[];
    diskSizes: { mb: number; gb: number; percent: number };
}) {
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${t('dashboard.title')}`,
            href: '/dashboard',
        },
    ];
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="grid grid-cols-4 gap-4">
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative flex items-center justify-center overflow-hidden rounded-xl border p-4">
                        <div className="flex flex-col">
                            <p className="font-semibold uppercase">{t('dashboard.disk_space')}</p>
                            <HardDrive strokeWidth={1} className="m-auto h-12 w-12" />
                            <p className="text-lg">
                                {diskSizes.gb} GB <span className="text-xs">({diskSizes.percent < 1 ? '< 1 ' : diskSizes.percent} %)</span>
                            </p>
                        </div>
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative flex items-center justify-center overflow-hidden rounded-xl border p-4">
                        <a href={route('tenant.tickets.index')} className="w-full text-center !no-underline">
                            <div className="flex flex-col">
                                <p className="font-semibold uppercase">{tChoice('tickets.title', 2)}</p>
                                <Ticket strokeWidth={1} className="m-auto h-12 w-12" />
                                <p className="text-lg">{counts.ticketsCount}</p>
                            </div>
                        </a>
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative flex items-center justify-center overflow-hidden rounded-xl border p-4">
                        <a href={route('tenant.assets.index')} className="text-center !no-underline">
                            <div className="flex flex-col items-center justify-center">
                                <p className="font-semibold uppercase">{tChoice('assets.title', 2)}</p>
                                <Cuboid strokeWidth={1} className="m-auto h-12 w-12" />
                                <p className="text-lg">{counts.assetsCount}</p>
                            </div>
                        </a>
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative overflow-hidden rounded-xl border p-4">
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                        <a href={route('tenant.assets.index')} className="text-center !no-underline">
                            <div className="flex flex-col items-center justify-center">
                                <p className="font-semibold uppercase">{tChoice('interventions.title', 2)}</p>
                                <Wrench strokeWidth={1} className="m-auto h-12 w-12" />
                                <p className="text-lg">{counts.interventionsCount}</p>
                            </div>
                        </a>
                    </div>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative h-fit flex-1 overflow-hidden rounded-xl border p-4 md:min-h-min">
                        <h2>{t('dashboard.maintenances_overdue')}</h2>
                        {overdueMaintenances && overdueMaintenances.length > 0 ? (
                            <ul className="flex flex-col gap-2">
                                {overdueMaintenances.map((maintainable) => (
                                    <li key={maintainable.id} className="even:bg-sidebar odd:bg-secondary p-2">
                                        <a href={maintainable.maintainable.location_route} className="!no-underline">
                                            <p>
                                                {maintainable.next_maintenance_date}- {maintainable.name}
                                            </p>
                                            <p className="text-sm">
                                                ({maintainable.maintainable.reference_code}) -{' '}
                                                {t(`maintenances.frequency.${maintainable.maintenance_frequency}`)}
                                            </p>
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p>{t('maintenances.none')}</p>
                        )}
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative h-fit flex-1 overflow-hidden rounded-xl border p-4 md:min-h-min">
                        <h2>{t('dashboard.interventions_overdue')}</h2>
                        {overdueInterventions && overdueInterventions.length > 0 ? (
                            <ul className="flex flex-col gap-2">
                                {overdueInterventions.map((intervention) => (
                                    <li key={intervention.id} className="even:bg-sidebar odd:bg-secondary p-2">
                                        <a href={intervention.interventionable.location_route} className="!no-underline">
                                            <p>
                                                {intervention.planned_at} - {intervention.interventionable.name}{' '}
                                                <Pill variant={intervention.priority}>{t(`interventions.priority.${intervention.priority}`)}</Pill>
                                            </p>
                                            <p className="text-sm">
                                                ({intervention.interventionable.reference_code}) - {t(`interventions.status.${intervention.status}`)}{' '}
                                                - {intervention.intervention_type.label}
                                            </p>
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p>{t('interventions.none')}</p>
                        )}
                    </div>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative h-fit flex-1 overflow-hidden rounded-xl border p-4 md:min-h-min">
                        <h2>{t('dashboard.maintenances_next')}</h2>
                        {maintainables && maintainables.length > 0 ? (
                            <ul className="flex flex-col gap-2">
                                {maintainables.map((maintainable) => (
                                    <li key={maintainable.id} className="even:bg-sidebar odd:bg-secondary p-2">
                                        <a href={maintainable.maintainable.location_route} className="!no-underline">
                                            <p>
                                                {maintainable.next_maintenance_date}- {maintainable.name}
                                            </p>
                                            <p className="text-sm">
                                                ({maintainable.maintainable.reference_code}) -{' '}
                                                {t(`maintenances.frequency.${maintainable.maintenance_frequency}`)}
                                            </p>
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p>{t('maintenances.none')}</p>
                        )}
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative h-fit flex-1 overflow-hidden rounded-xl border p-4 md:min-h-min">
                        <h2>{t('dashboard.interventions_next')}</h2>
                        {interventions && interventions.length > 0 ? (
                            <ul className="flex flex-col gap-2">
                                {interventions.map((intervention) => (
                                    <li key={intervention.id} className="even:bg-sidebar odd:bg-secondary p-2">
                                        <a href={intervention.interventionable.location_route} className="!no-underline">
                                            <p>
                                                {intervention.planned_at} - {intervention.interventionable.name}{' '}
                                                <Pill variant={intervention.priority}>{t(`interventions.priority.${intervention.priority}`)}</Pill>
                                            </p>
                                            <p className="text-sm">
                                                ({intervention.interventionable.reference_code}) - {t(`interventions.status.${intervention.status}`)}{' '}
                                                - {intervention.intervention_type.label}
                                            </p>
                                        </a>
                                    </li>
                                ))}
                            </ul>
                        ) : (
                            <p>{t('interventions.none')}</p>
                        )}
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
