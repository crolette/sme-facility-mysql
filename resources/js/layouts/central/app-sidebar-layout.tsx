import { AppContent } from '@/components/central/app-content';
import { AppShell } from '@/components/central/app-shell';
import { CentralAppSidebar } from '@/components/central/app-sidebar';
import { CentralAppSidebarHeader } from '@/components/central/app-sidebar-header';
import { type BreadcrumbItem } from '@/types';
import { type PropsWithChildren } from 'react';

export default function AppSidebarLayout({ children, breadcrumbs = [] }: PropsWithChildren<{ breadcrumbs?: BreadcrumbItem[] }>) {
    return (
        <AppShell variant="sidebar">
            <CentralAppSidebar />
            <AppContent variant="sidebar">
                <CentralAppSidebarHeader breadcrumbs={breadcrumbs} />
                {children}
            </AppContent>
        </AppShell>
    );
}
