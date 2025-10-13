import { Button } from '@/components/ui/button';
import Footer from '@/components/website/footer';
import { Head, Link } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';
import { useState, type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
}

export default function WebsiteLayout({ children, ...props }: AppLayoutProps) {
    const [showMobileMenu, setShowMobileMenu] = useState(false);

    return (
        <>
            <Head title="Welcome"></Head>
            <div className="font-website flex min-h-screen w-full flex-col items-center bg-white">
                <header className="sticky top-0 z-50 container mb-6 w-full text-sm not-has-[nav]:hidden">
                    <nav className="bg-logo mx-auto flex flex-row items-center justify-between gap-4 rounded-b-md px-10 py-6 shadow-2xl lg:px-20 lg:py-10">
                        <a href={route('home')}>
                            <img src="../images/logo.png" alt="" className="w-32 lg:w-50" />
                        </a>

                        <ul className="hidden gap-12 text-lg font-semibold md:flex">
                            <li>
                                <Link href={'/features/qr-code'} className="!no-underline">
                                    Fonctionnalités
                                </Link>
                            </li>
                            <li>
                                <Link href={'/who/manager'} className="!no-underline">
                                    Pour qui ?
                                </Link>
                            </li>
                            <li>
                                <Link href={'/why/sme'} className="!no-underline">
                                    Pour quoi choisir SME-Facility ?
                                </Link>
                            </li>
                            <li>
                                <Link href={'/pricing'} className="!no-underline">
                                    Tarifs
                                </Link>
                            </li>

                            <li>
                                <Button variant={'cta'}>Demo</Button>
                            </li>
                        </ul>
                        <Menu size={24} onClick={() => setShowMobileMenu(true)} className="block md:hidden" />
                        {showMobileMenu && (
                            <div className="absolute inset-0 flex h-screen w-full flex-col items-center justify-center bg-red-500 md:hidden">
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
