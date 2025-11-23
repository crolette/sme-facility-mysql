import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Building, Building2, Check, Factory, X } from 'lucide-react';
import React from 'react';

export default function Pricing() {
    const { t } = useLaravelReactI18n();

    const boldNumber = (str: string) => {
        const newString = str.split(' ').map((word, i) => (
            <React.Fragment key={i}>
                {word.match(/\d+/g) ? <span className="font-extrabold">{word}</span> : word}
                {i < str.split(' ').length - 1 && ' '}
            </React.Fragment>
        ));

        return newString;
    };

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
                    <div className="mx-auto flex flex-col gap-10 p-4 md:p-10 xl:w-11/12">
                        <h1>{t('website_pricing.title')}</h1>
                        {/* <h2>{t('website_pricing.subtitle')}</h2> */}
                        {/* <p>Un peu de patience... Plus d'informations très bientôt</p> */}
                        <p>{t('website_pricing.description')}</p>

                        <div className="bg-cta p-4 text-center">
                            <h2 className="animate-pulse font-bold">{t('website_pricing.launching_offer')}</h2>
                            <p>{t('website_pricing.launching_offer.description')}</p>
                        </div>

                        <div className="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
                            <div className="flex flex-col gap-6 rounded-md border p-6 lg:p-10">
                                <Building size={36} className="mx-auto" />
                                <h3 className="text-center">{t('website_pricing.starter.title')}</h3>
                                <div className="text-center">
                                    <p className={'text-xl line-through'}>149€ / {t('website_pricing.month')}</p>
                                    <p className={'animate-pulse text-2xl font-extrabold'}>99€ / {t('website_pricing.month')}</p>
                                </div>
                                <ul className="flex flex-col gap-6">
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p className="break-all">{boldNumber(Array(...t('website_pricing.starter.items'))[0])}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p className="break-all">{Array(...t('website_pricing.starter.items'))[1]}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p className="break-all">{boldNumber(Array(...t('website_pricing.starter.items'))[2])}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p className="break-all">{boldNumber(Array(...t('website_pricing.starter.items'))[3])}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p className="break-all">{Array(...t('website_pricing.starter.items'))[4]}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p>{Array(...t('website_pricing.starter.items'))[5]}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <X className="text-destructive" size={24} />
                                        <p>{Array(...t('website_pricing.starter.items'))[6]}</p>
                                    </li>
                                </ul>
                                <a href={route('website.demo')} className="mx-auto">
                                    <Button variant={'cta'} className="">
                                        {t('website_pricing.start_today')}
                                    </Button>
                                </a>
                            </div>
                            <div className="flex flex-col gap-6 rounded-md border p-6 lg:p-10">
                                <Building2 size={36} className="mx-auto" />
                                <h3 className="text-center">Premium</h3>
                                <div className="text-center">
                                    <p className={'text-xl line-through'}>299€ / {t('website_pricing.month')}</p>
                                    <p className={'animate-pulse text-2xl font-extrabold'}>199€ / {t('website_pricing.month')}</p>
                                </div>
                                <ul className="flex flex-col gap-6">
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p>{boldNumber(Array(...t('website_pricing.premium.items'))[0])}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p>{Array(...t('website_pricing.premium.items'))[1]}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p>{boldNumber(Array(...t('website_pricing.premium.items'))[2])}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p>{boldNumber(Array(...t('website_pricing.premium.items'))[3])}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p>{Array(...t('website_pricing.premium.items'))[4]}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p>{Array(...t('website_pricing.premium.items'))[5]}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p>{Array(...t('website_pricing.premium.items'))[6]}</p>
                                    </li>
                                </ul>
                                <a href={route('website.demo')} className="mx-auto">
                                    <Button variant={'cta'} className="">
                                        {t('website_pricing.start_today')}
                                    </Button>
                                </a>
                            </div>
                            <div className="flex flex-col gap-6 rounded-md border p-6 sm:col-span-2 lg:col-auto lg:p-10">
                                <Factory size={36} className="mx-auto" />
                                <h3 className="text-center">Entreprise</h3>
                                <div className="text-center">
                                    <p>{t('website_pricing.offer')}</p>
                                    <p className={'text-2xl font-extrabold'}>{t('website_pricing.on_demand')}</p>
                                </div>
                                <ul className="flex flex-col gap-6">
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p>{Array(...t('website_pricing.enterprise.items'))[0]}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p>{Array(...t('website_pricing.enterprise.items'))[1]}</p>
                                    </li>
                                    <li className="grid grid-cols-[24px_1fr] gap-4">
                                        <Check className="text-success" size={24} />
                                        <p>{Array(...t('website_pricing.enterprise.items'))[2]}</p>
                                    </li>
                                </ul>
                                <a href={route('website.demo')} className="mx-auto">
                                    <Button variant={'cta'} className="text-wrap">
                                        {t('website_pricing.enterprise.discuss')}
                                    </Button>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
