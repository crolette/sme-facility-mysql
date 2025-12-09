import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function CheckoutConfirmed() {
    const { t } = useLaravelReactI18n();

    return (
        <WebsiteLayout>
            <Head title={t('website_pricing.meta_title')}>
                <meta name="robots" content="noindex, nofollow, noarchive, nosnippet" />
            </Head>
            <section className="text-website-font w-full">
                <h1>Congratulations</h1>
            </section>
        </WebsiteLayout>
    );
}
