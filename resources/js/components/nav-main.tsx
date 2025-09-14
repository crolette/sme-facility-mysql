import { SidebarGroup, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import { Link } from '@inertiajs/react';

export function NavMain({ items = [] }: { items: NavItem[] }) {

    return (
        <SidebarGroup className="px-2 py-0">
            <SidebarMenu>
                {items.map((item) => (
                    <SidebarMenuItem key={item.title}>
                        <SidebarMenuButton
                            asChild
                            isActive={route().current()?.includes(item.title.toLowerCase())}
                            tooltip={{ children: item.title }}
                        >
                            <Link href={item.href} prefetch className="flex">
                                {item.icon && <item.icon />}
                                <span>{item.title}</span>
                                {item.count && (
                                    <div className="bg-accent-foreground text-accent flex h-4 w-4 items-center justify-center rounded-full p-3 text-xs">
                                        <span>{item.count}</span>
                                    </div>
                                )}
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                ))}
            </SidebarMenu>
        </SidebarGroup>
    );
}
