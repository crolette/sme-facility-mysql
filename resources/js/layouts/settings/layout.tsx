import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Separator } from '@/components/ui/separator';
import { cn } from '@/lib/utils';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { type PropsWithChildren } from 'react';

export default function SettingsLayout({ children }: PropsWithChildren) {
    const { t } = useLaravelReactI18n();
    // When server-side rendering, we only render the layout on the client...
    if (typeof window === 'undefined') {
        return null;
    }

    const sidebarNavItems: NavItem[] = [
        {
            title: `${t('settings.profile')}`,
            href: '/settings/profile',
            icon: null,
        },
        {
            title: `${t('settings.notifications')}`,
            href: '/settings/notification-preferences',
            icon: null,
        },
        {
            title: `${t('auth.password')}`,
            href: '/settings/password',
            icon: null,
        },
        {
            title: `${t('settings.appearance')}`,
            href: '/settings/appearance',
            icon: null,
        },
        {
            title: `${t('settings.import_export')}`,
            href: '/settings/import-export',
            icon: null,
        },
        {
            title: `${t('settings.company')}`,
            href: '/settings/company',
            icon: null,
        },
    ];
    const currentPath = window.location.pathname;

    return (
        <div className="px-4 py-6">
            <Heading title={t('settings.title')} description={t('settings.settings_description')} />

            <div className="flex flex-col space-y-8 lg:flex-row lg:space-y-0 lg:space-x-12">
                <aside className="w-full max-w-xl lg:w-48">
                    <nav className="flex flex-col space-y-1 space-x-0">
                        {sidebarNavItems.map((item, index) => (
                            <Button
                                key={`${item.href}-${index}`}
                                size="sm"
                                variant="ghost"
                                asChild
                                className={cn('w-full justify-start', {
                                    'bg-muted': currentPath === item.href,
                                })}
                            >
                                <Link href={item.href} prefetch className="!no-underline">
                                    {item.title}
                                </Link>
                            </Button>
                        ))}
                    </nav>
                </aside>

                <Separator className="my-6 md:hidden" />

                <div className="w-full px-2">
                    <section className="w-full space-y-12">{children}</section>
                </div>
            </div>
        </div>
    );
}
