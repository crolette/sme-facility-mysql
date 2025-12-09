import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { usePermissions } from '@/hooks/usePermissions';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import {
    BrickWall,
    Building,
    Building2,
    ChartLine,
    Cuboid,
    FileStack,
    Handshake,
    LayoutDashboard,
    LayoutGrid,
    ScrollText,
    Ticket,
    Users,
    Wrench,
} from 'lucide-react';
import AppLogo from './app-logo';

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Home',
    //     href: '/',
    //     icon: BookOpen,
    // },
];

export function AppSidebar() {
    const { hasPermission } = usePermissions();
    const { t, tChoice } = useLaravelReactI18n();
    const { props } = usePage();

    const mainNavItems: NavItem[] = [
        {
            title: `${t('dashboard.title')}`,
            href: '/dashboard',
            icon: LayoutDashboard,
            canView: true,
        },
        {
            title: `${tChoice('locations.sites', 2)}`,
            href: '/sites',
            icon: Building2,
            canView: hasPermission('view any locations'),
        },
        {
            title: `${tChoice('locations.buildings_outdoor', 2)}`,
            href: '/buildings',
            icon: Building,
            canView: hasPermission('view any locations'),
        },
        {
            title: `${tChoice('locations.floors', 2)}`,
            href: '/floors',
            icon: BrickWall,
            canView: hasPermission('view any locations'),
        },
        {
            title: `${tChoice('locations.rooms', 2)}`,
            href: '/rooms',
            icon: LayoutGrid,
            canView: hasPermission('view any locations'),
        },
        {
            title: `${tChoice('assets.title', 2)}`,
            href: '/assets',
            icon: Cuboid,
            canView: hasPermission('view any assets'),
        },
        {
            title: `${tChoice('tickets.title', 2)}`,
            href: '/tickets',
            icon: Ticket,
            count: 5,
            canView: hasPermission('view any tickets'),
        },
        {
            title: `${tChoice('interventions.title', 2)}`,
            href: '/interventions',
            icon: Wrench,
            canView: hasPermission('view any interventions'),
        },
        {
            title: `${tChoice('providers.title', 2)}`,
            href: '/providers',
            icon: Handshake,
            canView: hasPermission('view any providers'),
        },
        {
            title: `${tChoice('contracts.title', 2)}`,
            href: '/contracts',
            icon: ScrollText,
            canView: hasPermission('view any contracts'),
        },
        {
            title: `${tChoice('common.documents', 2)}`,
            href: '/documents',
            icon: FileStack,
            canView: hasPermission('view any documents'),
        },
        {
            title: `${tChoice('contacts.title', 2)}`,
            href: '/users',
            icon: Users,
            canView: hasPermission('view any users'),
        },
        {
            title: `${t('common.statistics')}`,
            href: '/statistics',
            icon: ChartLine,
            canView: props.has_statistics && hasPermission('view statistics'),
        },
    ];

    const openTicketsCount = props.openTicketsCount as number;

    const navItems = mainNavItems.map((item) => (item.title === 'Tickets' ? { ...item, count: openTicketsCount } : item));

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href="/dashboard" prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={navItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
