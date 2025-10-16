import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BrickWall, Building, Building2, Cuboid, Handshake, LayoutDashboard, LayoutGrid, ScrollText, Settings, Ticket, Users } from 'lucide-react';
import AppLogo from './app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutDashboard,
    },
    {
        title: 'Sites',
        href: '/sites',
        icon: Building2,
    },
    {
        title: 'Buildings/Outdoor',
        href: '/buildings',
        icon: Building,
    },
    {
        title: 'Floors',
        href: '/floors',
        icon: BrickWall,
    },
    {
        title: 'Rooms',
        href: '/rooms',
        icon: LayoutGrid,
    },
    {
        title: 'Assets',
        href: '/assets',
        icon: Cuboid,
    },
    {
        title: 'Tickets',
        href: '/tickets',
        icon: Ticket,
        count: 5,
    },
    {
        title: 'Interventions',
        href: '/interventions',
        icon: Settings,
    },
    {
        title: 'Providers',
        href: '/providers',
        icon: Handshake,
    },
    {
        title: 'Contracts',
        href: '/contracts',
        icon: ScrollText,
    },
    {
        title: 'Users',
        href: '/users',
        icon: Users,
    },
];

const footerNavItems: NavItem[] = [
    // {
    //     title: 'Home',
    //     href: '/',
    //     icon: BookOpen,
    // },
];

export function AppSidebar() {
    const { props } = usePage();

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
