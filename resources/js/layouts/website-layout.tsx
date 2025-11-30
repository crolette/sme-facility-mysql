import InputError from '@/components/input-error';
import LocaleChange from '@/components/tenant/LocaleChange';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import Footer from '@/components/website/footer';
import { Head, Link, useForm } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { CheckCircle, ChevronDown, ChevronUp, Menu, X } from 'lucide-react';
import { FormEventHandler, useEffect, useRef, useState, type ReactNode } from 'react';

interface AppLayoutProps {
    children: ReactNode;
}

interface NewsletterFormData {
    honey: string;
    email: string;
    consent: boolean;
}

export default function WebsiteLayout({ children, ...props }: AppLayoutProps) {
    const { t } = useLaravelReactI18n();
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

    const { data, setData, reset, setError, errors } = useForm<NewsletterFormData>({
        honey: '',
        email: null,
        consent: false,
    });

    const handleNewsletterForm: FormEventHandler = async (e) => {
        e.preventDefault();
        try {
            const response = await axios.post(route('website.newsletter'), data);
            if (response.data.status === 'success') {
                setShowLaunchEmail(false);
                setSuccessEmail(true);
                reset();
            }
        } catch (error) {
            console.log(error);
            setError('email', error.response.data.message);
        }
    };

    console.log(errors);

    const [showLaunchEmail, setShowLaunchEmail] = useState<boolean>(false);
    const [successEmail, setSuccessEmail] = useState<boolean>(false);

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
                <header className="text-website-card sticky top-0 z-50 container mb-6 w-full text-sm not-has-[nav]:hidden">
                    <nav className="bg-logo mx-auto flex flex-row items-center justify-between gap-4 rounded-b-md px-5 py-6 shadow-2xl lg:px-5 lg:py-10">
                        <a href={route('website.home')}>
                            <img
                                src="/images/logo.png"
                                alt="Logo SME-Facility"
                                className="w-32 lg:w-42"
                                aria-label="Go to home of SME-Facility website"
                            />
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

                            <li className="space-x-2">
                                <a href={route('website.demo')}>
                                    <Button variant={'cta'}> {t('website_menu.demo')}</Button>
                                </a>
                                <LocaleChange url={'website.locale'} />
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
                                        <LocaleChange url={'website.locale'} />
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
                    <div className="fixed bottom-0 flex w-full flex-col items-center justify-center">
                        <div className="bg-cta mx-auto w-9/12 rounded-t-lg px-4 py-2 md:w-7/12">
                            <div>
                                <div
                                    className="text-logo flex cursor-pointer items-center justify-center gap-4"
                                    onClick={() => setShowLaunchEmail(!showLaunchEmail)}
                                >
                                    <div>
                                        <p className="animate-pulse text-center text-2xl font-extrabold">
                                            {t('website_contact.newsletter.launch_title_line1')}
                                        </p>
                                        <p className="animate-pulse text-center">{t('website_contact.newsletter.launch_title_line2')}</p>
                                    </div>
                                    {showLaunchEmail ? <ChevronDown /> : <ChevronUp />}
                                </div>
                                {showLaunchEmail && (
                                    <form
                                        onSubmit={handleNewsletterForm}
                                        className="mx-auto flex w-9/12 max-w-lg flex-col items-center justify-center space-y-2"
                                    >
                                        <input
                                            type="text"
                                            name="honey"
                                            style={{ display: 'none' }}
                                            onChange={(e) => setData('honey', e.target.value)}
                                            tabIndex={-1}
                                            autoComplete="off"
                                        />
                                        <div className="w-full">
                                            <Input
                                                type="email"
                                                name="email"
                                                id=""
                                                required
                                                className="w-full"
                                                placeholder={t('common.email_placeholder')}
                                                onChange={(e) => setData('email', e.target.value)}
                                            />
                                            <InputError message={errors.email} />
                                        </div>
                                        <div className="text-logo flex items-center gap-2 text-xs">
                                            <Checkbox
                                                id="consent"
                                                required
                                                checked={data.consent}
                                                onClick={() => {
                                                    setData('consent', !data.consent);
                                                }}
                                            />
                                            <label htmlFor="consent">
                                                {t('website_contact.newsletter.consent_description')}
                                                <a href={route('website.confidentiality')}>{t('website_common.footer.confidentiality')}.</a>
                                            </label>
                                        </div>
                                        <div>
                                            <Button variant={'default'} disabled={!data.consent}>
                                                {t('actions.submit')}
                                            </Button>
                                        </div>
                                    </form>
                                )}
                                {successEmail && (
                                    <div className="text-logo mt-2 flex items-center gap-2 text-xs">
                                        <CheckCircle />
                                        <p>{t('website_contact.newsletter.thank_you')}</p>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>
                    <Footer />
                </div>
            </div>
        </>
    );
}

<a href="https://storyset.com/people">People illustrations by Storyset</a>;
