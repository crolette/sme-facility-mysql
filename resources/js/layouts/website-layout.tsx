import { Button } from '@/components/ui/button';
import Footer from '@/components/website/footer';
import { Head, Link } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Menu, X } from 'lucide-react';
import { useEffect, useRef, useState, type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
}

export default function WebsiteLayout({ children, ...props }: AppLayoutProps) {
    const { t, currentLocale } = useLaravelReactI18n();
    const [showMobileMenu, setShowMobileMenu] = useState(false);
    const [showFeaturesMenu, setShowFeaturesMenu] = useState(false);
    const [showWhoMenu, setShowWhoMenu] = useState(false);
    const whoMenuRef = useRef(null);
    const featuresMenuRef = useRef(null);

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

    useEffect(() => {
        const handleClickOutside = (event) => {
            if (whoMenuRef.current && !whoMenuRef.current.contains(event.target)) {
                setShowWhoMenu(false);
            }

            if (featuresMenuRef.current && !featuresMenuRef.current.contains(event.target)) {
                setShowFeaturesMenu(false);
            }
        };

        document.addEventListener('mousedown', handleClickOutside);

        return () => {
            document.removeEventListener('mousedown', handleClickOutside);
        };
    }, []);

    return (
        <>
            <Head>
                <meta name="robots" content="index, follow"></meta>
                <link rel="canonical" href={window.location.href} />
                <link rel="alternate" hrefLang="fr" href={import.meta.env.VITE_APP_URL + '/' + 'fr' + window.location.pathname.substring(3)} />
                <link rel="alternate" hrefLang="de" href={import.meta.env.VITE_APP_URL + '/' + 'de' + window.location.pathname.substring(3)} />
                <link rel="alternate" hrefLang="nl" href={import.meta.env.VITE_APP_URL + '/' + 'nl' + window.location.pathname.substring(3)} />
                <link rel="alternate" hrefLang="en" href={import.meta.env.VITE_APP_URL + '/' + 'en' + window.location.pathname.substring(3)} />
            </Head>
            <div className="font-website relative flex min-h-screen w-full flex-col items-center bg-white">
                <header className="text-website-card sticky top-0 z-50 container mb-6 w-full px-4 text-sm not-has-[nav]:hidden xl:px-0">
                    <nav className="bg-logo mx-auto flex flex-row items-center justify-between gap-4 rounded-b-md px-5 py-6 shadow-2xl lg:px-5 lg:py-10">
                        <a href={route('home')}>
                            <img src="/images/logo.png" alt="" className="max-w-32 lg:w-42" />
                        </a>

                        <ul className="text-md hidden gap-8 font-semibold md:flex md:shrink-0 md:items-center lg:gap-12" ref={featuresMenuRef}>
                            <li className="relative">
                                <a
                                    className="block py-2 !no-underline"
                                    id="features"
                                    onClick={() => {
                                        setShowFeaturesMenu(!showFeaturesMenu);
                                        setShowWhoMenu(false);
                                    }}
                                >
                                    {t('website_menu.features')}
                                </a>
                                {showFeaturesMenu && (
                                    <ul className="bg-logo absolute top-full -ml-3 flex w-52 flex-col gap-4 rounded-b-md p-3 pt-2">
                                        <li>
                                            <Link href={route('website.features.assets')} className="!no-underline">
                                                {t('website_menu.assets')}
                                            </Link>
                                        </li>
                                        <li>
                                            <Link href={route('website.features.documents')} className="!no-underline">
                                                {t('website_menu.documents')}
                                            </Link>
                                        </li>
                                        <li>
                                            <Link href={route('website.features.maintenance')} className="!no-underline">
                                                {t('website_menu.maintenance')}
                                            </Link>
                                        </li>
                                        <li>
                                            <Link href={route('website.features.contracts')} className="!no-underline">
                                                {t('website_menu.contracts')}
                                            </Link>
                                        </li>
                                        <li>
                                            <Link href={route('website.features.qrcode')} className="!no-underline">
                                                {t('website_menu.qrcode')}
                                            </Link>
                                        </li>
                                        <li>
                                            <Link href={route('website.features.roles')} className="!no-underline">
                                                {t('website_menu.roles')}
                                            </Link>
                                        </li>
                                        <li>
                                            <Link href={route('website.features.statistics')} className="!no-underline">
                                                {t('website_menu.statistics')}
                                            </Link>
                                        </li>
                                    </ul>
                                )}
                            </li>
                            <li className="group relative" ref={whoMenuRef}>
                                <a
                                    className="block py-2 !no-underline"
                                    id="features"
                                    onClick={() => {
                                        setShowWhoMenu(!showWhoMenu);
                                        setShowFeaturesMenu(false);
                                    }}
                                >
                                    {t('website_menu.for_who')}
                                </a>
                                {showWhoMenu && (
                                    <ul className="bg-logo absolute top-full -ml-3 flex w-72 flex-col gap-4 rounded-b-md p-3 pt-2">
                                        <li>
                                            <Link href={route('website.who.sme')} className="!no-underline" id="features">
                                                {t('website_menu.sme')}
                                            </Link>
                                        </li>
                                        <li>
                                            <Link href={route('website.who.facility-manager')} className="!no-underline" id="features">
                                                {t('website_menu.facility_manager')}
                                            </Link>
                                        </li>
                                        <li>
                                            <Link href={route('website.who.maintenance-manager')} className="!no-underline" id="features">
                                                {t('website_menu.maintenance_manager')}
                                            </Link>
                                        </li>
                                    </ul>
                                )}
                            </li>
                            <li>
                                <Link href={route('website.why')} className="block py-2 !no-underline" id="why">
                                    {t('website_menu.why_sme')}
                                </Link>
                            </li>
                            <li>
                                <Link href={route('website.pricing')} className="block py-2 !no-underline">
                                    {t('website_menu.pricing')}
                                </Link>
                            </li>

                            <li>
                                <a href={route('website.demo')}>
                                    <Button variant={'cta'}> {t('website_menu.demo')}</Button>
                                </a>
                            </li>
                        </ul>
                        <Menu size={24} onClick={() => setShowMobileMenu(true)} className="block md:hidden" />
                    </nav>
                    {showMobileMenu && (
                        <div className="bg-logo/80 absolute inset-0 flex h-screen items-center justify-center overflow-x-hidden lg:hidden">
                            <div className="absolute inset-0 flex h-screen items-center justify-center bg-transparent md:hidden">
                                <div className="bg-logo/90 flex h-full w-full flex-col justify-center text-center sm:w-10/12">
                                    <ul className="space-y-4 pl-4 text-lg font-semibold sm:pl-10" ref={featuresMenuRef}>
                                        <li className="relative">
                                            <a
                                                className="block py-2 !no-underline"
                                                id="features"
                                                onClick={() => {
                                                    setShowFeaturesMenu(!showFeaturesMenu);
                                                    setShowWhoMenu(false);
                                                }}
                                            >
                                                {t('website_menu.features')}
                                            </a>
                                            {showFeaturesMenu && (
                                                <ul className="bg-logo mt-2 flex flex-col gap-4 py-4">
                                                    <li>
                                                        <Link href={route('website.features.assets')} className="!no-underline">
                                                            {t('website_menu.assets')}
                                                        </Link>
                                                    </li>
                                                    <li>
                                                        <Link href={route('website.features.documents')} className="!no-underline">
                                                            {t('website_menu.documents')}
                                                        </Link>
                                                    </li>
                                                    <li>
                                                        <Link href={route('website.features.maintenance')} className="!no-underline">
                                                            {t('website_menu.maintenance')}
                                                        </Link>
                                                    </li>
                                                    <li>
                                                        <Link href={route('website.features.contracts')} className="!no-underline">
                                                            {t('website_menu.contracts')}
                                                        </Link>
                                                    </li>
                                                    <li>
                                                        <Link href={route('website.features.qrcode')} className="!no-underline">
                                                            {t('website_menu.qrcode')}
                                                        </Link>
                                                    </li>
                                                    <li>
                                                        <Link href={route('website.features.roles')} className="!no-underline">
                                                            {t('website_menu.roles')}
                                                        </Link>
                                                    </li>
                                                    <li>
                                                        <Link href={route('website.features.statistics')} className="!no-underline">
                                                            {t('website_menu.statistics')}
                                                        </Link>
                                                    </li>
                                                </ul>
                                            )}
                                        </li>
                                        <li className="group relative" ref={whoMenuRef}>
                                            <a
                                                className="block py-2 !no-underline"
                                                id="features"
                                                onClick={() => {
                                                    setShowWhoMenu(!showWhoMenu);
                                                    setShowFeaturesMenu(false);
                                                }}
                                            >
                                                {t('website_menu.for_who')}
                                            </a>
                                            {showWhoMenu && (
                                                <ul className="bg-logo mt-2 flex flex-col gap-4 py-4">
                                                    <li>
                                                        <Link href={route('website.who.sme')} className="!no-underline" id="features">
                                                            {t('website_menu.sme')}
                                                        </Link>
                                                    </li>
                                                    <li>
                                                        <Link href={route('website.who.facility-manager')} className="!no-underline" id="features">
                                                            {t('website_menu.facility_manager')}
                                                        </Link>
                                                    </li>
                                                    <li>
                                                        <Link href={route('website.who.maintenance-manager')} className="!no-underline" id="features">
                                                            {t('website_menu.maintenance_manager')}
                                                        </Link>
                                                    </li>
                                                </ul>
                                            )}
                                        </li>
                                        <li>
                                            <Link href={route('website.why')} className="block py-2 !no-underline" id="why">
                                                {t('website_menu.why_sme')}
                                            </Link>
                                        </li>
                                        <li>
                                            <Link href={route('website.pricing')} className="block py-2 !no-underline">
                                                {t('website_menu.pricing')}
                                            </Link>
                                        </li>

                                        <li>
                                            <a href={route('website.demo')}>
                                                <Button variant={'cta'}> {t('website_menu.demo')}</Button>
                                            </a>
                                        </li>
                                        <li>
                                            <X onClick={() => setShowMobileMenu(false)} className="mx-auto" size={32} />
                                        </li>
                                    </ul>
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
