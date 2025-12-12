import { Pill } from '@/components/ui/pill';
import { usePermissions } from '@/hooks/usePermissions';
import AppLayout from '@/layouts/app-layout';
import { Intervention, Maintainable, ScheduledNotification, type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Cuboid, Ticket, Wrench } from 'lucide-react';

export default function TenantDashboard({
    counts,
    maintainables,
    interventions,
    overdueMaintenances,
    overdueInterventions,
    nextNotifications,
}: {
    counts: { ticketsCount: number; assetsCount: number; interventionsCount: number };
    nextNotifications: ScheduledNotification[];
    maintainables: Maintainable[];
    interventions: Intervention[];
    overdueMaintenances: Maintainable[];
    overdueInterventions: Intervention[];
}) {
    const { hasPermission } = usePermissions();
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${t('dashboard.title')}`,
            href: '/dashboard',
        },
    ];

    function parseDate(dateString) {
        const d = new Date(dateString);

        const date = new Date(dateString);

        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();

        return `${day}/${month}/${year}`;
    }

    console.log(nextNotifications);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="grid grid-flow-col-dense gap-4">
                    {/* {hasPermission('update company') && (
                        <div className="border-sidebar-border/70 dark:border-sidebar-border relative flex items-center justify-center overflow-hidden rounded-xl border p-4">
                            <div className="flex flex-col">
                                <p className="font-semibold uppercase">{t('dashboard.disk_space')}</p>
                                <HardDrive strokeWidth={1} className="m-auto h-4 w-4 md:h-12 md:w-12" />
                                <p className="text-lg">
                                    {diskSizes.gb} GB <span className="text-xs">({diskSizes.percent < 1 ? '< 1 ' : diskSizes.percent} %)</span>
                                </p>
                            </div>
                        </div>
                    )} */}
                    <div className="border-sidebar-border/70 dark:border-sidebar-border hover:bg-secondary relative flex items-center justify-center overflow-hidden rounded-xl border p-4">
                        <a href={route('tenant.tickets.index')} className="w-full text-center !no-underline">
                            <div className="flex flex-col">
                                <p className="hidden font-semibold uppercase md:inline-block">{tChoice('tickets.title', 2)}</p>
                                <Ticket strokeWidth={1} className="m-auto h-12 w-12" />
                                <p className="text-lg">{counts.ticketsCount}</p>
                            </div>
                        </a>
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border hover:bg-secondary relative flex items-center justify-center overflow-hidden rounded-xl border p-4">
                        <a href={route('tenant.assets.index')} className="text-center !no-underline">
                            <div className="flex flex-col items-center justify-center">
                                <p className="hidden font-semibold uppercase md:inline-block">{tChoice('assets.title', 2)}</p>
                                <Cuboid strokeWidth={1} className="m-auto h-12 w-12" />
                                <p className="text-lg">{counts.assetsCount}</p>
                            </div>
                        </a>
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                    </div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border hover:bg-secondary relative overflow-hidden rounded-xl border p-4">
                        {/* <PlaceholderPattern className="absolute inset-0 size-full stroke-neutral-900/20 dark:stroke-neutral-100/20" /> */}
                        <a href={route('tenant.interventions.index')} className="text-center !no-underline">
                            <div className="flex flex-col items-center justify-center">
                                <p className="hidden font-semibold uppercase md:inline-block">{tChoice('interventions.title', 2)}</p>
                                <Wrench strokeWidth={1} className="m-auto h-12 w-12" />
                                <p className="text-lg">{counts.interventionsCount}</p>
                            </div>
                        </a>
                    </div>
                </div>
                <div>
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative flex-1 space-y-3 rounded-xl border p-4">
                        <h2>{t('notifications.upcoming')}</h2>
                        <ul className="flex max-h-42 flex-col gap-2 overflow-hidden overflow-y-auto">
                            {nextNotifications.length > 0 &&
                                nextNotifications.map((notification) => (
                                    <li key={notification.id} className="even:bg-sidebar odd:bg-secondary p-2">
                                        {notification.notification_type === 'end_date' && (
                                            <a href={notification.data.link} className="!no-underline">
                                                <p>
                                                    {t('contracts.end_date')} : {notification.data.subject} ({notification.data.provider}) -{' '}
                                                    {tChoice('contracts.renewal_type.title', 1)} :{' '}
                                                    {t('contracts.renewal_type.' + notification.data.renewal_type)} -{' '}
                                                    {parseDate(notification.data.end_date)}
                                                </p>
                                            </a>
                                        )}
                                        {notification.notification_type === 'notice_date' && (
                                            <a href={notification.data.link} className="!no-underline">
                                                <p>
                                                    {t('contracts.notice_date')} : {notification.data.subject} -{' '}
                                                    {tChoice('contracts.renewal_type.title', 1)} :
                                                    {t('contracts.renewal_type.' + notification.data.renewal_type)} ({notification.data.provider}) -{' '}
                                                    {parseDate(notification.data.notice_date)}
                                                </p>
                                            </a>
                                        )}
                                        {notification.notification_type === 'end_warranty_date' && (
                                            <a href={notification.data.link} className="!no-underline">
                                                <p>
                                                    {t('assets.warranty_end_date')} : {notification.data.subject} ({notification.data.reference}) -{' '}
                                                    {parseDate(notification.data.end_warranty_date)}
                                                </p>
                                            </a>
                                        )}

                                        {notification.notification_type === 'depreciation_end_date' && (
                                            <a href={notification.data.link} className="!no-underline">
                                                <p>
                                                    {t('assets.depreciation_end_date')} :{notification.data.subject} - {notification.data.location} (
                                                    {notification.data.reference}) - {parseDate(notification.data.depreciation_end_date)}
                                                </p>
                                            </a>
                                        )}
                                    </li>
                                ))}
                        </ul>
                    </div>
                </div>
                <div className="grid gap-4 md:grid-cols-2">
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative h-fit flex-1 space-y-3 overflow-hidden rounded-xl border p-4 md:min-h-min">
                        <h2>{t('dashboard.maintenances_overdue')}</h2>

                        {overdueMaintenances && overdueMaintenances.length > 0 ? (
                            <ul className="flex max-h-96 flex-col gap-2 overflow-hidden overflow-y-auto">
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
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative h-full flex-1 space-y-3 overflow-hidden rounded-xl border p-4 md:min-h-min">
                        <h2>{t('dashboard.interventions_overdue')}</h2>
                        {overdueInterventions && overdueInterventions.length > 0 ? (
                            <ul className="flex max-h-96 flex-col gap-2 overflow-hidden overflow-y-auto">
                                {overdueInterventions.map((intervention) => (
                                    <li key={intervention.id} className="even:bg-sidebar odd:bg-secondary p-2">
                                        <a href={intervention.interventionable.location_route} className="!no-underline">
                                            <p>
                                                {intervention.planned_at} - {intervention.interventionable.name}{' '}
                                                <Pill variant={intervention.priority}>{t(`interventions.priority.${intervention.priority}`)}</Pill>
                                            </p>
                                            <p className="text-sm">
                                                ({intervention.interventionable.reference_code}) - {t(`common.status.${intervention.status}`)} -{' '}
                                                {intervention.intervention_type.label}
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
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative h-fit flex-1 space-y-3 overflow-hidden rounded-xl border p-4 md:min-h-min">
                        <h2>{t('dashboard.maintenances_next')}</h2>
                        {maintainables && maintainables.length > 0 ? (
                            <ul className="flex max-h-96 flex-col gap-2 overflow-hidden overflow-y-auto">
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
                    <div className="border-sidebar-border/70 dark:border-sidebar-border relative h-fit flex-1 space-y-3 overflow-hidden rounded-xl border p-4 md:min-h-min">
                        <h2>{t('dashboard.interventions_next')}</h2>
                        {interventions && interventions.length > 0 ? (
                            <ul className="flex max-h-96 flex-col gap-2 overflow-hidden overflow-y-auto">
                                {interventions.map((intervention) => (
                                    <li key={intervention.id} className="even:bg-sidebar odd:bg-secondary p-2">
                                        <a href={intervention.interventionable.location_route} className="!no-underline">
                                            <p>
                                                {intervention.planned_at} - {intervention.interventionable.name}{' '}
                                                <Pill variant={intervention.priority}>{t(`interventions.priority.${intervention.priority}`)}</Pill>
                                            </p>
                                            <p className="text-sm">
                                                ({intervention.interventionable.reference_code}) - {t(`common.status.${intervention.status}`)} -{' '}
                                                {intervention.intervention_type.label}
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
