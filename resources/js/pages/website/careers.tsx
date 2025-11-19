import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function Careers() {
    const { t } = useLaravelReactI18n();
    return (
        <WebsiteLayout>
            <Head title={t('website_pricing.meta_title')}>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content={t('website_pricing.meta_title') + ' | ' + import.meta.env.VITE_APP_NAME} />
                <meta name="description" itemProp="description" property="description" content={t('website_pricing.meta-description')} />

                <meta property="og:title" content={t('website_pricing.meta-title-og')} />
                <meta property="og:description" content={t('website_pricing.meta-description-og')} />
            </Head>
            <section className="text-website-font w-full">
                <div className="container mx-auto">
                    {/* <div className="mx-auto grid h-full gap-10 md:grid-cols-2 md:p-10 lg:max-w-11/12"></div> */}
                    <div className="mx-auto flex flex-col gap-10 p-4 md:p-10 lg:max-w-11/12">
                        <h1>Careers</h1>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
