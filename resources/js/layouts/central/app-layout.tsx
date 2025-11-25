import Toastr from '@/components/Toastr';
import AppLayoutTemplate from '@/layouts/central/app-sidebar-layout';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/react';
import { type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
    breadcrumbs?: BreadcrumbItem[];
}

export default ({ children, breadcrumbs, ...props }: AppLayoutProps) => (
    <>
        <AppLayoutTemplate breadcrumbs={breadcrumbs} {...props}>
            <Head>
                <meta name="robots" content="noindex, nofollow, noarchive, nosnippet" />
            </Head>
            {children}
        </AppLayoutTemplate>
        <Toastr />
    </>
);
