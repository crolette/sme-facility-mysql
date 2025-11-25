import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { BadgeCheck, Bell, Check, Clock, Group, QrCode, Settings } from 'lucide-react';

export default function Welcome() {
    const { t } = useLaravelReactI18n();

    return (
        <WebsiteLayout>
            <Head title={t('website_home.title')}>
                <meta property="title" content={t('website_home.title') + ' | ' + import.meta.env.VITE_APP_NAME} />
                <meta name="description" itemProp="description" property="description" content={t('website_home.description')} />

                <meta property="og:title" content={t('website_home.title_og')} />
                <meta property="og:description" content={t('website_home.description_og')} />
            </Head>
            <section className="bg-logo text-website-card -mt-30 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container mx-auto">
                    <div className="mx-auto grid h-full gap-10 px-4 py-32 md:grid-cols-[2fr_1fr] md:gap-10 md:px-10 lg:max-w-11/12">
                        <div className="flex flex-col items-center justify-center gap-10">
                            <h1 className="">
                                {t('website_home.hero_title')}
                                <span className="font-extrabold"> {t('website_home.hero_title_span')}</span>
                            </h1>
                            <h2 className="!text-xl">{t('website_home.hero_subtitle')}</h2>
                            <p className="">{t('website_home.hero_description')}</p>
                            <div className="flex flex-col items-center gap-6 md:flex-row md:gap-10">
                                <a href={route('website.contact')} rel="follow">
                                    <Button variant={'cta'}>{t('website_menu.demo_appointment')}</Button>
                                </a>
                                <a href={route('website.pricing')} rel="follow">
                                    <Button variant={'transparent'}>{t('website_menu.pricing_discover')}</Button>
                                </a>
                            </div>
                        </div>
                        <div className="mx-auto my-auto">
                            <img src="images/home/fm_sm.jpg" alt="" className="blob h-auto max-w-72 rounded-md shadow-2xl md:w-full" />
                        </div>
                    </div>
                </div>
            </section>
            <section className="flex min-h-screen items-center py-40">
                <div className="container mx-auto">
                    <div className="mx-auto h-full space-y-10 px-4 py-10 text-black md:max-w-11/12 md:p-10">
                        <h2>{t('website_home.section-1.title')}</h2>
                        <h3 className="">{t('website_home.section-1.subtitle')}</h3>
                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="flex flex-col space-y-6 md:items-end">
                                <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:w-72">
                                    <div className="flex items-center gap-4">
                                        <Clock size={16} className="shrink-0" />
                                        <h4>{t('website_home.section-1.card-1.title')}</h4>
                                    </div>
                                    <p>{t('website_home.section-1.card-1.paragraph')}</p>
                                </div>
                                <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:w-fit md:max-w-96">
                                    <div className="flex items-center gap-4">
                                        <Settings size={16} className="shrink-0" />
                                        <h4>{t('website_home.section-1.card-2.title')}</h4>
                                    </div>
                                    <p>{t('website_home.section-1.card-2.paragraph')}</p>
                                </div>
                            </div>
                            <div className="space-y-6">
                                <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:mt-20 md:max-w-96">
                                    <div className="flex items-center gap-4">
                                        <Group size={16} className="shrink-0" />
                                        <h4>{t('website_home.section-1.card-3.title')}</h4>
                                    </div>
                                    <p>{t('website_home.section-1.card-3.paragraph')}</p>
                                </div>
                                <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:w-72">
                                    <div className="flex items-center gap-4">
                                        <QrCode size={16} className="shrink-0" />
                                        <h4>{t('website_home.section-1.card-4.title')}</h4>
                                    </div>
                                    <p>{t('website_home.section-1.card-4.paragraph')}</p>
                                </div>
                            </div>
                        </div>
                        <p className="text-center text-xl italic">{t('website_home.section-1.headline')}</p>
                    </div>
                </div>
            </section>
            <section className="bg-website-card flex min-h-screen items-center py-40">
                <div className="container mx-auto">
                    <div className="text-website-font flex-flex-col mx-auto h-full items-center space-y-10 px-4 py-10 md:max-w-11/12 md:p-6 lg:p-10">
                        <h2 className="">{t('website_home.section-2.title')}</h2>
                        <h3 className="">{t('website_home.section-2.subtitle')}</h3>
                        <div className="relative grid md:grid-cols-[2fr_1fr]">
                            <div className="relative space-y-6">
                                <div className="relative">
                                    <div className="bg-website-primary text-website-card space-y-4 rounded-md p-6">
                                        <div className="flex items-center gap-4">
                                            <QrCode />
                                            <p className="font-bold">{t('website_home.section-2.card-1.title')}</p>
                                        </div>
                                        <p>{t('website_home.section-2.card-1.paragraph')}</p>
                                    </div>
                                </div>
                                <div className="relative w-full">
                                    <img src="images/left-arrow.svg" alt="" className="left-0 hidden md:absolute md:block" />
                                    <div className="bg-website-border text-website-card w-full space-y-4 rounded-md p-6 md:ml-12">
                                        <div className="left-4 flex items-center gap-4">
                                            <Bell />
                                            <p className="font-bold">{t('website_home.section-2.card-2.title')}</p>
                                        </div>
                                        <p>{t('website_home.section-2.card-2.paragraph')}</p>
                                    </div>
                                </div>
                                <div className="relative w-full">
                                    <img src="images/left-arrow.svg" alt="" className="hidden md:absolute md:left-8 md:block" />
                                    <div className="bg-website-secondary text-website-font w-full space-y-4 rounded-md p-6 md:ml-20">
                                        <div className="flex items-center gap-4">
                                            <Settings />
                                            <p className="font-bold">{t('website_home.section-2.card-3.title')}</p>
                                        </div>
                                        <p>{t('website_home.section-2.card-3.paragraph')}</p>
                                    </div>
                                </div>
                                <div className="relative w-full">
                                    <img src="images/left-arrow.svg" alt="" className="hidden md:absolute md:left-20 md:block" />
                                    <div className="border-website-border text-website-font w-full space-y-4 rounded-md border bg-white p-6 md:ml-32">
                                        <div className="flex items-center gap-4">
                                            <BadgeCheck />
                                            <p className="font-bold">{t('website_home.section-2.card-4.title')}</p>
                                        </div>
                                        <p>{t('website_home.section-2.card-4.paragraph')}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p className="text-center text-xl italic">{t('website_home.section-2.headline')}</p>
                    </div>
                </div>
            </section>
            <section className="flex min-h-screen items-center py-40">
                <div className="container mx-auto">
                    <div className="text-website-font mx-auto h-full space-y-14 px-4 py-10 md:max-w-11/12 md:p-10">
                        <h2 className="">{t('website_home.section-3.title')}</h2>
                        <h3>{t('website_home.section-3.subtitle')}</h3>

                        <div className="from-website-primary text-website-secondary mx-auto grid grid-cols-1 gap-10 rounded-md bg-linear-to-r to-white p-10 lg:grid-cols-[2fr_1fr]">
                            <div className="">
                                <h3>
                                    {t('website_home.section-3.card-1.title')}
                                    <span className="block text-lg">{t('website_home.section-3.card-1.title-span')}</span>
                                </h3>
                                <ul className="mt-5 ml-5 space-y-10">
                                    {Array(...t('website_home.section-3.card-1.list')).map((elem, index) => (
                                        <li key={index}>
                                            <Check size={16} className="mr-4 inline-block" />
                                            {elem}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                            <div className="relative flex items-center justify-center md:justify-end">
                                <img src="images/Digital tools-bro.svg" alt="" className="mx-auto max-h-64 lg:max-h-11/12" />
                            </div>
                        </div>
                        <div className="text-website-font to-website-secondary mx-auto grid grid-cols-1 gap-10 rounded-md bg-linear-to-r from-white p-10 lg:grid-cols-[1fr_2fr]">
                            <div className="relative order-2 flex items-center justify-center md:justify-end lg:order-none">
                                <img src="images/Electrician-bro.svg" alt="" className="mx-auto max-h-64 lg:max-h-11/12" />
                            </div>
                            <div className="">
                                <h3>
                                    {t('website_home.section-3.card-2.title')}
                                    <span className="block text-lg">{t('website_home.section-3.card-2.title-span')}</span>
                                </h3>
                                <ul className="mt-5 ml-5 space-y-10">
                                    {Array(...t('website_home.section-3.card-2.list')).map((elem, index) => (
                                        <li key={index}>
                                            <Check size={16} className="mr-4 inline-block" />
                                            {elem}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                        <div className="from-logo text-website-secondary mx-auto grid grid-cols-1 gap-10 rounded-md bg-linear-to-r to-white p-10 lg:grid-cols-[2fr_1fr]">
                            <div className="">
                                <h3>
                                    {t('website_home.section-3.card-3.title')}
                                    <span className="block text-lg">{t('website_home.section-3.card-3.title-span')}</span>
                                </h3>
                                <ul className="mt-5 ml-5 space-y-10">
                                    {Array(...t('website_home.section-3.card-3.list')).map((elem, index) => (
                                        <li key={index}>
                                            <Check size={16} className="mr-4 inline-block" />
                                            {elem}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                            <div className="relative flex items-center justify-center md:justify-end">
                                <img src="images/Office management-pana.svg" alt="" className="mx-auto max-h-64 lg:max-h-11/12" />
                            </div>
                        </div>
                        <div className="text-website-font to-website-card mx-auto grid grid-cols-1 gap-10 rounded-md bg-linear-to-r from-white p-10 lg:grid-cols-[1fr_2fr]">
                            <div className="relative order-2 flex items-center justify-center md:justify-end lg:order-none">
                                <img src="images/Download-amico.svg" alt="" className="mx-auto max-h-64 lg:max-h-11/12" />
                            </div>
                            <div className="flex flex-col gap-4">
                                <h3>
                                    {t('website_home.section-3.card-4.title')}
                                    <span className="block text-lg">{t('website_home.section-3.card-4.title-span')}</span>
                                </h3>
                                <ul className="mt-5 ml-5 space-y-10">
                                    {Array(...t('website_home.section-3.card-4.list')).map((elem, index) => (
                                        <li key={index}>
                                            <Check size={16} className="mr-4 inline-block" />
                                            {elem}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </div>
                        <div className="from-website-border text-website-secondary mx-auto grid grid-cols-1 gap-10 rounded-md bg-linear-to-r to-white p-10 lg:grid-cols-[2fr_1fr]">
                            <div className="flex flex-col gap-4">
                                <h3>
                                    {t('website_home.section-3.card-5.title')}
                                    <span className="block text-lg">{t('website_home.section-3.card-5.title-span')}</span>
                                </h3>
                                <ul className="mt-5 ml-5 space-y-10">
                                    {Array(...t('website_home.section-3.card-5.list')).map((elem, index) => (
                                        <li key={index}>
                                            <Check size={16} className="mr-4 inline-block" />
                                            {elem}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                            <div className="relative flex items-center justify-center md:justify-end">
                                <img src="images/QR Code-bro.svg" alt="" className="mx-auto max-h-64 lg:max-h-11/12" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
