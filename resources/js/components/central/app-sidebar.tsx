import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';
import { BookOpen, LayoutGrid } from 'lucide-react';
import AppLogo from '../app-logo';

const mainNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
        icon: LayoutGrid,
        canView: true,
    },
    {
        title: 'Tenants',
        href: '/tenants',
        icon: LayoutGrid,
        canView: true,
    },
    {
        title: 'Location Types',
        href: route('central.locations.index'),
        icon: LayoutGrid,
        canView: true,
    },
    {
        title: 'Category types',
        href: route('central.types.index'),
        icon: LayoutGrid,
        canView: true,
    },
    // {
    //     title: 'Building Types',
    //     href: route('central.buildings.index'),
    //     icon: LayoutGrid,
    // },
];

const footerNavItems: NavItem[] = [
    {
        title: 'Home',
        href: '/',
        icon: BookOpen,
        canView: true,
    },
];

export function CentralAppSidebar() {
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
