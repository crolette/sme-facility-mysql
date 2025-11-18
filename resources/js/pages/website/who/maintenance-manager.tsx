import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function WhoMaintenanceManager() {
    const { t } = useLaravelReactI18n();
    return (
        <WebsiteLayout>
            <Head title={t('website_who.maintenance_manager.meta_title')}>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content={t('website_who.maintenance_manager.meta_title') + ' | ' + import.meta.env.VITE_APP_NAME} />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content={t('website_who.maintenance_manager.meta_description')}
                />

                <meta property="og:title" content={t('website_who.maintenance_manager.meta_title_og')} />
                <meta property="og:description" content={t('website_who.maintenance_manager.meta_description_og')} />
            </Head>
            <section className="bg-website-border text-website-card -mt-28 flex min-h-screen w-full flex-col items-center justify-center py-20 md:-mt-38">
                <div className="container mx-auto">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:grid-cols-[2fr_1fr] md:px-10 md:py-16 lg:max-w-11/12">
                        <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                            <h1 className="leading-16">
                                <span className="font-extrabold">{t('website_who.maintenance_manager.title-span')}</span>{' '}
                                {t('website_who.maintenance_manager.title')}
                            </h1>
                            <h2 className="!text-xl">{t('website_who.maintenance_manager.subtitle')}</h2>
                            <p className="">{t('website_who.maintenance_manager.description')}</p>
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
                            <img src="/images/Group 22.png" alt="" className="" />
                        </div>
                    </div>
                </div>
            </section>
            <section className="text-website-font flex min-h-screen w-full flex-col items-center justify-center gap-20 py-40">
                <div className="container mx-auto">
                    <div className="mx-auto flex h-full flex-col gap-10 px-4 md:max-w-11/12 md:gap-30">
                        <div className="relative grid grid-cols-1 gap-10 overflow-hidden p-10 md:grid-cols-2">
                            <span className="text-border/10 absolute top-1/3 left-14 -translate-1/2 font-sans text-[256px] font-extrabold">1</span>

                            <div className="space-y-4">
                                <p className="font-bold">{t('website_who.maintenance_manager.section.1.title')}</p>
                                <p>{t('website_who.maintenance_manager.section.1.description')}</p>
                            </div>
                            <div className="flex items-center">
                                <img src="/images/Group 22.png" alt="" className="" />
                            </div>
                        </div>
                        <div className="relative grid grid-cols-1 gap-10 overflow-hidden p-10 md:grid-cols-2">
                            <span className="text-border/10 absolute top-1/3 -right-24 -translate-1/2 font-sans text-[256px] font-extrabold">2</span>
                            <div className="order-2 flex items-center md:order-none">
                                <img src="/images/Group 22.png" alt="" className="" />
                            </div>
                            <div className="space-y-4">
                                <p className="font-bold">{t('website_who.maintenance_manager.section.2.title')}</p>
                                <p>{t('website_who.maintenance_manager.section.2.description')}</p>
                            </div>
                        </div>
                        <div className="relative grid grid-cols-1 gap-10 overflow-hidden p-10 md:grid-cols-2">
                            <span className="text-border/10 absolute top-1/3 left-14 -translate-1/2 font-sans text-[256px] font-extrabold">3</span>

                            <div className="space-y-4">
                                <p className="font-bold">{t('website_who.maintenance_manager.section.3.title')}</p>
                                <p>{t('website_who.maintenance_manager.section.3.description')}</p>
                            </div>
                            <div className="flex items-center">
                                <img src="/images/Group 22.png" alt="" className="" />
                            </div>
                        </div>
                        <div className="relative grid grid-cols-1 gap-10 overflow-hidden p-10 md:grid-cols-2">
                            <span className="text-border/10 absolute top-1/4 -right-24 -translate-1/2 font-sans text-[256px] font-extrabold">4</span>
                            <div className="order-2 flex items-center md:order-none">
                                <img src="/images/Group 22.png" alt="" className="" />
                            </div>
                            <div className="space-y-4">
                                <p className="font-bold">{t('website_who.maintenance_manager.section.4.title')}</p>
                                <p>{t('website_who.maintenance_manager.section.4.description')}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
