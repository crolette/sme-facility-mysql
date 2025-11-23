import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function FAQ() {
    const { t } = useLaravelReactI18n();
    return (
        <WebsiteLayout>
            <Head title={t('website_who_are_we.meta_title')}>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content={t('website_who_are_we.meta_title') + ' | ' + import.meta.env.VITE_APP_NAME} />
                <meta name="description" itemProp="description" property="description" content={t('website_who_are_we.meta-description')} />

                <meta property="og:title" content={t('website_who_are_we.meta-title-og')} />
                <meta property="og:description" content={t('website_who_are_we.meta-description-og')} />
            </Head>
            <section className="text-website-font w-full">
                <div className="container mx-auto">
                    {/* <div className="mx-auto grid h-full gap-10 md:grid-cols-2 md:p-10 lg:max-w-11/12"></div> */}
                    <div className="mx-auto flex flex-col gap-10 p-4 md:p-10 lg:max-w-11/12">
                        <h1>{t('website_who_are_we.title')}</h1>
                        <h2>{t('website_who_are_we.subtitle')}</h2>
                        <div className="space-y-4">
                            <p>{t('website_who_are_we.paragraph_1')}</p>
                            <p>{t('website_who_are_we.paragraph_2')}</p>
                            <p>{t('website_who_are_we.paragraph_3')}</p>
                            <ul>
                                {Array(...t('website_who_are_we.paragraph_3_list')).map((elem, index) => (
                                    <li key={index} className="ml-10 list-disc">
                                        {elem}
                                    </li>
                                ))}
                            </ul>
                            <p>{t('website_who_are_we.paragraph_4')}</p>
                        </div>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
