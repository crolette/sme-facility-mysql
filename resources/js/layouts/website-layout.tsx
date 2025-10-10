import Footer from '@/components/footer';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/react';
import { type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
}

export default function WebsiteLayout({ children, ...props }: AppLayoutProps) {
    return (
        <>
            <Head title="Welcome"></Head>
            <div className="font-website flex min-h-screen w-full flex-col items-center bg-white">
                <header className="z-50 mb-6 w-full text-sm not-has-[nav]:hidden sticky top-0">
                    <nav className="bg-logo container mx-auto flex items-center justify-between gap-4 rounded-b-md px-20 py-10 shadow-2xl">
                        <img src="images/logo.png" alt="" className="w-50" />
                        <ul className="flex gap-12 text-lg font-semibold">
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
                            <li>
                                <Button variant={"cta"}>Demo</Button>
                            </li>
                        </ul>
                    </nav>
                </header>
                <div className="w-full ">
                    <main className=" ">{children}</main>
                    <Footer/>
                </div>
            </div>
        </>
    );
};

<a href="https://storyset.com/people">People illustrations by Storyset</a>;
