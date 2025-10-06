import { Head, Link } from '@inertiajs/react';
import { type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
}

export default function WebsiteLayout({ children, ...props }: AppLayoutProps) {
    return (
        <>
            <Head title="Welcome">
            </Head>
            <div className="bg-background font-website flex min-h-screen w-full flex-col items-center">
                <header className="mb-6 w-full text-sm not-has-[nav]:hidden text-background mt-6">
                    <nav className="bg-logo container mx-auto flex items-center justify-between gap-4 px-20 py-10 rounded-md shadow-2xl">
                        <img src="images/logo.png" alt="" className="w-50" />
                        <ul className="flex gap-12 text-xl font-semibold">
                            <li>
                                <Link href={'/who'} className="!no-underline">
                                    Pour qui ?
                                </Link>
                            </li>

                            <li>
                                <Link href={'/who'} className="!no-underline">
                                    Fonctionnalités
                                </Link>
                            </li>
                            <li>
                                <Link href={'/pricing'} className="!no-underline">
                                    Tarifs
                                </Link>
                            </li>
                            <li>
                                <Link href={'/contact'} className="!no-underline">
                                    Contact
                                </Link>
                            </li>
                        </ul>

                        {/* {auth.user ? (
                            <Link
                                href={route('central.dashboard')}
                                className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link
                                    href={route('central.login')}
                                    className="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                                >
                                    Log in
                                </Link>
                            </>
                        )} */}
                    </nav>
                </header>
                <div className="flex w-full flex-col items-center">
                    <main className="container flex w-full flex-col items-center p-10">{children}</main>
                </div>
            </div>
        </>
    );
};

<a href="https://storyset.com/people">People illustrations by Storyset</a>;
