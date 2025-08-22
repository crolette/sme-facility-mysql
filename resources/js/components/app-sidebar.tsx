import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, BrickWall, Building, Building2, Cuboid, Handshake, LayoutDashboard, LayoutGrid, ScrollText, Ticket, Users } from 'lucide-react';
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
    {
        title: 'Home',
        href: '/',
        icon: BookOpen,
    },
];

export function AppSidebar() {
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
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
