import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function FeaturesDocuments() {
    const { t } = useLaravelReactI18n();
    function FAQ() {
        const items = [];
        for (let i = 0; i < 5; i++) {
            items.push(
                <details className="" open={i == 0 ? true : false} key={i}>
                    <summary className="cursor-pointer text-2xl font-bold">
                        <h3>{t(`website_features.documents.faq.${i + 1}.question`)}</h3>
                        <hr className="mt-3" />
                    </summary>
                    <p className="mt-6 text-lg">{t(`website_features.documents.faq.${i + 1}.answer`)}</p>
                </details>,
            );
        }
        return <>{items}</>;
    }

    return (
        <WebsiteLayout>
            <Head title={t('website_features.documents.meta_title')}>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content={t('website_features.documents.meta_title') + ' | ' + import.meta.env.VITE_APP_NAME} />
                <meta name="description" itemProp="description" property="description" content={t('website_features.documents.meta_description')} />

                <meta property="og:title" content={t('website_features.documents.meta_title_og')} />
                <meta property="og:description" content={t('website_features.documents.meta_description_og')} />
            </Head>
            <section className="bg-website-primary text-website-card -mt-20 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:max-w-11/12 md:grid-cols-2 md:px-10">
                        <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                            <h1 className="">
                                {t('website_features.documents.title')}{' '}
                                <span className="font-extrabold">{t('website_features.documents.title-span')}</span>
                            </h1>
                            <h2 className="!text-xl">{t('website_features.documents.subtitle')}</h2>
                            <p className="">{t('website_features.documents.description')}</p>
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
            <section className="text-website-font min-h-screen w-full py-40">
                <div className="container mx-auto">
                    <div className="mx-auto flex h-full flex-col gap-10 px-4 md:max-w-11/12 md:gap-30">
                        <div className="grid gap-6 md:grid-cols-3">
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">{t('website_features.documents.card-1.title')}</h6>
                                    <p>{t('website_features.documents.card-1.description')}</p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">{t('website_features.documents.card-2.title')}</h6>
                                    <p>{t('website_features.documents.card-2.description')}</p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">{t('website_features.documents.card-3.title')}</h6>
                                    <p>{t('website_features.documents.card-3.description')}</p>
                                </div>
                            </div>
                        </div>
                        <img src="/images/Group 20.png" alt="" className="w-full" />

                        <div className="border-website-border flex w-full flex-col gap-4 rounded-md border p-6">{FAQ()}</div>
                        <Button variant={'cta'} className="mx-auto w-fit p-6 text-lg">
                            {t('website_common.demo_appointment')}
                        </Button>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
