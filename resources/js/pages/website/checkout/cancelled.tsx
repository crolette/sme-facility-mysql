import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function CheckoutCancelled() {
    const { t } = useLaravelReactI18n();

    return (
        <WebsiteLayout>
            <Head title={t('website_pricing.meta_title')}></Head>
            <section className="text-website-font w-full">
                <h1>Cancelled</h1>
            </section>
        </WebsiteLayout>
    );
}
