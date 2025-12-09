import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function FAQ() {
    const { t } = useLaravelReactI18n();

    function FAQ() {
        const items = [];
        for (let i = 0; i < Array(...t(`website_faq.questions`)).length; i++) {
            items.push(
                <details className="" open={i == 0 ? true : false} key={i}>
                    <summary className="cursor-pointer text-2xl font-bold">
                        <h3>{t(`website_faq.questions`)[i]}</h3>
                        <hr className="mt-3" />
                    </summary>
                    <p className="mt-6 text-lg">{t(`website_faq.answers`)[i]}</p>
                </details>,
            );
        }
        return <>{items}</>;
    }

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
                    <div className="mx-auto flex flex-col gap-10 p-4 md:p-10 lg:max-w-11/12">
                        <h1>FAQ</h1>

                        <div className="border-website-border flex w-full flex-col gap-4 rounded-md border p-6">{FAQ()}</div>

                        <a href={route('website.demo')} className="mx-auto">
                            <Button variant={'cta'}>{t('website_menu.demo_appointment')}</Button>
                        </a>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
