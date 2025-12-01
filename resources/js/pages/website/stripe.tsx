import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function Stripe() {
    const { t } = useLaravelReactI18n();

    return (
        <WebsiteLayout>
            <Head title={t('website_pricing.meta_title')}></Head>
            <section className="text-website-font w-full">
                <script async src="https://js.stripe.com/v3/pricing-table.js"></script>
                <stripe-pricing-table
                    pricing-table-id="prctbl_1SZZVxFHXryfbBkbVbNwFclV"
                    publishable-key="pk_test_51SZTAqFHXryfbBkbpPc0MmNd1JMN3mM5vdwd6ot9qMQMNXa01QD7SDSaDHpvMXxNqmsin14as3RHdTyOQo9IP2b200mjbZl7G1"
                ></stripe-pricing-table>
            </section>
        </WebsiteLayout>
    );
}
