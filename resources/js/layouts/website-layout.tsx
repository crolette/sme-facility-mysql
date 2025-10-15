import { Button } from '@/components/ui/button';
import Footer from '@/components/website/footer';
import { Head, Link } from '@inertiajs/react';
import { Menu, X } from 'lucide-react';
import { useEffect, useState, type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
}

export default function WebsiteLayout({ children, ...props }: AppLayoutProps) {
    const [showMobileMenu, setShowMobileMenu] = useState(false);

    useEffect(() => {
        if (showMobileMenu) {
            if (typeof window != 'undefined' && window.document) {
                document.body.style.overflow = 'hidden';
            }
        }

        if (!showMobileMenu) {
            document.body.style.overflow = 'unset';
        }
    }, [showMobileMenu]);

    return (
        <>
            <Head title="Welcome"></Head>
            <div className="font-website relative flex min-h-screen w-full flex-col items-center bg-white">
                <header className="sticky top-0 z-50 container mb-6 w-full text-sm not-has-[nav]:hidden">
                    <nav className="bg-logo mx-auto flex flex-row items-center justify-between gap-4 rounded-b-md px-5 py-6 shadow-2xl lg:px-5 lg:py-10">
                        <a href={route('home')}>
                            <img src="../images/logo.png" alt="" className="w-32 lg:w-42" />
                        </a>

                        <ul className="hidden gap-8 text-lg font-semibold md:flex md:shrink-0 md:items-center lg:gap-12">
                            <li className="group relative">
                                <a className="block py-2 !no-underline" id="features">
                                    Fonctionnalités
                                </a>
                                <ul className="bg-logo absolute top-full -ml-3 hidden w-52 flex-col gap-4 rounded-b-md p-3 pt-2 group-hover:flex">
                                    <li>
                                        <Link href={route('website.features.central')} className="!no-underline">
                                            Assets
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href={route('website.features.documents')} className="!no-underline">
                                            Documents
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href={route('website.features.maintenance')} className="!no-underline">
                                            Maintenance
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href={route('website.features.contracts')} className="!no-underline">
                                            Contracts
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href={route('website.features.qr')} className="!no-underline">
                                            QR Code
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href={route('website.features.roles')} className="!no-underline">
                                            Roles
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href={route('website.features.statistics')} className="!no-underline">
                                            Statistiques
                                        </Link>
                                    </li>
                                </ul>
                            </li>
                            <li className="group relative">
                                <a className="block py-2 !no-underline" id="features">
                                    Pour qui ?
                                </a>
                                <ul className="bg-logo absolute top-full -ml-3 hidden w-72 flex-col gap-4 rounded-b-md p-3 pt-2 group-hover:flex">
                                    <li>
                                        <Link href={route('website.who.sme')} className="!no-underline" id="features">
                                            PME
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href={route('website.who.facility-manager')} className="!no-underline" id="features">
                                            Facility Manager
                                        </Link>
                                    </li>
                                    <li>
                                        <Link href={route('website.who.maintenance-manager')} className="!no-underline" id="features">
                                            Responsable de maintenance
                                        </Link>
                                    </li>
                                </ul>
                            </li>
                            <li>
                                <Link href={route('website.why')} className="!no-underline" id="why">
                                    Pour quoi choisir SME-Facility ?
                                </Link>
                            </li>
                            <li>
                                <Link href={route('website.pricing')} className="!no-underline">
                                    Tarifs
                                </Link>
                            </li>

                            <li>
                                <Button variant={'cta'}>Demo</Button>
                            </li>
                        </ul>
                        <Menu size={24} onClick={() => setShowMobileMenu(true)} className="block md:hidden" />
                    </nav>
                    {showMobileMenu && (
                        <div className="bg-logo/80 absolute inset-0 flex h-screen items-center justify-center overflow-x-hidden">
                            <div className="absolute inset-0 flex h-screen items-center justify-center bg-transparent md:hidden">
                                <div className="bg-logo/90 flex h-full w-10/12 flex-col items-center justify-center">
                                    MENU
                                    <X onClick={() => setShowMobileMenu(false)} />
                                </div>
                            </div>
                        </div>
                    )}
                </header>

                <div className="w-full">
                    <main className="website">{children}</main>
                    <Footer />
                </div>
            </div>
        </>
    );
}

<a href="https://storyset.com/people">People illustrations by Storyset</a>;
