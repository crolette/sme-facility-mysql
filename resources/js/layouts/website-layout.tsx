import Footer from '@/components/footer';
import { Button } from '@/components/ui/button';
import { Head, Link } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';
import { useState, type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
}

export default function WebsiteLayout({ children, ...props }: AppLayoutProps) {
    const [showMobileMenu, setShowMobileMenu] = useState(false);

    console.log(showMobileMenu);

    return (
        <>
            <Head title="Welcome"></Head>
            <div className="font-website flex min-h-screen w-full flex-col items-center bg-white">
                <header className="sticky top-0 z-50 mb-6 w-full text-sm not-has-[nav]:hidden">
                    <nav className="bg-logo container mx-auto flex flex-row items-center justify-between gap-4 rounded-b-md px-10 py-6 shadow-2xl md:px-20 md:py-10">
                        <a href={route('home')}>
                            <img src="images/logo.png" alt="" className="w-32 md:w-50" />
                        </a>
                        <Menu size={24} onClick={() => setShowMobileMenu(true)} />
                        <ul className="hidden gap-12 text-lg font-semibold md:flex">
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
                                <Button variant={'cta'}>Demo</Button>
                            </li>
                        </ul>
                        {showMobileMenu && (
                            <div className="absolute inset-0 flex h-screen w-full flex-col items-center justify-center bg-red-500">
                                MENU
                                <X onClick={() => setShowMobileMenu(false)} />
                            </div>
                        )}
                    </nav>
                </header>
                <div className="w-full">
                    <main className="">{children}</main>
                    <Footer />
                </div>
            </div>
        </>
    );
}

<a href="https://storyset.com/people">People illustrations by Storyset</a>;
