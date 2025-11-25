import AuthLayoutTemplate from '@/layouts/auth/auth-simple-layout';
import { Head } from '@inertiajs/react';

export default function AuthLayout({ children, title, description, ...props }: { children: React.ReactNode; title: string; description: string }) {
    return (
        <AuthLayoutTemplate title={title} description={description} {...props}>
            <Head>
                <meta name="robots" content="noindex, nofollow, noarchive, nosnippet" />
            </Head>
            {children}
        </AuthLayoutTemplate>
    );
}
