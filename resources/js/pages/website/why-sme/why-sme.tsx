import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function WhySME() {
    const { t } = useLaravelReactI18n();

    return (
        <WebsiteLayout>
            <Head title={t('website_why.meta_title')}>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content={t('website_why.meta_title') + ' | ' + import.meta.env.VITE_APP_NAME} />
                <meta name="description" itemProp="description" property="description" content={t('website_why.meta_description')} />

                <meta property="og:title" content={t('website_why.meta_title_og')} />
                <meta property="og:description" content={t('website_why.meta_description_og')} />
            </Head>
            <section className="bg-website-secondary text-website-font -mt-30 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container mx-auto">
                    <div className="mx-auto grid h-full gap-10 px-4 py-32 md:grid-cols-[2fr_1fr] md:gap-10 md:px-10 lg:max-w-11/12">
                        <div className="flex flex-col justify-center gap-10">
                            <h1 className="">
                                <span className="font-extrabold">{t('website_why.title-span')}</span>
                                {t('website_why.title')}
                            </h1>
                            <h2 className="!text-xl">{t('website_why.subtitle')}</h2>
                            <p className=""> {t('website_why.description')}</p>
                            <div className="flex flex-col items-center gap-6 md:flex-row md:gap-10">
                                <a href={route('website.demo')}>
                                    <Button variant={'cta'}>{t('website_menu.demo_appointment')}</Button>
                                </a>
                                <a href={route('website.pricing')}>
                                    <Button variant={'transparent'}>{t('website_menu.pricing_discover')}</Button>
                                </a>
                            </div>
                        </div>
                        <div className="mx-auto my-auto">
                            <img src="/images/website/partner.jpg" alt="" className="blob h-auto max-w-72 shadow-2xl md:w-full" />
                        </div>
                    </div>
                </div>
            </section>
            <section className="text-website-font flex min-h-screen w-full flex-col items-center justify-center py-20">
                <div className="container mx-auto">
                    <div className="mx-auto flex h-full flex-col items-center gap-10 px-4 md:max-w-10/12 md:p-10">
                        <h2>{t('website_why.section.1.title')}</h2>

                        <div className="bg-website-secondary relative flex w-full flex-col gap-8 overflow-hidden rounded-md p-6">
                            <span className="text-border/5 absolute top-1/3 left-10 -translate-1/2 font-sans text-[256px] font-extrabold">1</span>
                            <h3>{t('website_why.section.1.card.1.title')}</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                {Array(...t('website_why.section.1.card.1.items')).map((elem, index) => (
                                    <li key={index}>{elem}</li>
                                ))}
                            </ul>
                        </div>
                        <div className="bg-website-primary/90 text-website-card relative flex w-full flex-col gap-8 overflow-hidden rounded-md p-6">
                            <span className="text-website-secondary/20 absolute top-1/3 left-10 -translate-1/2 font-sans text-[256px] font-extrabold">
                                2
                            </span>
                            <h3>{t('website_why.section.1.card.2.title')}</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                {Array(...t('website_why.section.1.card.2.items')).map((elem, index) => (
                                    <li key={index}>{elem}</li>
                                ))}
                            </ul>
                        </div>

                        <div className="bg-logo text-website-card relative flex w-full flex-col gap-8 overflow-hidden rounded-md p-6">
                            <span className="text-website-secondary/20 absolute top-1/3 left-14 -translate-1/2 font-sans text-[256px] font-extrabold">
                                3
                            </span>
                            <h3>{t('website_why.section.1.card.3.title')}</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                {Array(...t('website_why.section.1.card.3.items')).map((elem, index) => (
                                    <li key={index}>{elem}</li>
                                ))}
                            </ul>
                        </div>

                        <div className="bg-website-card relative flex w-full flex-col gap-8 overflow-hidden rounded-md p-6">
                            <span className="text-website-border/20 absolute top-1/3 left-9 -translate-1/2 font-sans text-[256px] font-extrabold">
                                4
                            </span>
                            <h3>{t('website_why.section.1.card.4.title')}</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                {Array(...t('website_why.section.1.card.4.items')).map((elem, index) => (
                                    <li key={index}>{elem}</li>
                                ))}
                            </ul>
                        </div>

                        <div className="bg-website-font text-website-card relative flex w-full flex-col gap-8 overflow-hidden rounded-md p-6">
                            <span className="text-website-secondary/20 absolute top-1/3 left-14 -translate-1/2 font-sans text-[256px] font-extrabold">
                                5
                            </span>
                            <h3>{t('website_why.section.1.card.5.title')}</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                {Array(...t('website_why.section.1.card.5.items')).map((elem, index) => (
                                    <li key={index}>{elem}</li>
                                ))}
                            </ul>
                        </div>
                        <a href={route('website.demo')}>
                            <Button variant={'cta'}>{t('website_menu.demo_appointment')}</Button>
                        </a>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
