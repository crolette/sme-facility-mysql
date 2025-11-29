import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Boxes, ChartLine, FileStack, QrCode, ReceiptText, Settings } from 'lucide-react';

export default function OurSolutions() {
    const { t } = useLaravelReactI18n();

    return (
        <section className="bg-website-secondary min-h-screen py-20">
            <div className="container mx-auto">
                <div className="text-website-font mx-auto h-full space-y-10 px-4 py-10 text-sm md:max-w-11/12 md:p-10">
                    <h2>{t('website_common.our_solutions.title')}</h2>
                    <h3>{t('website_common.our_solutions.subtitle')}</h3>
                    <div className="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
                        <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                            <div className="flex gap-4">
                                <Boxes size={24} className="shrink-0" />
                                <h4 className="font-semibold">{t('website_common.our_solutions.inventory.title')}</h4>
                            </div>
                            <p>{t('website_common.our_solutions.inventory.paragraph')}</p>
                            <a href={route('website.features.assets')} className="text-website-primary">
                                {t('website_common.know_more')}
                            </a>
                        </div>
                        <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                            <div className="flex gap-4">
                                <Settings size={24} className="shrink-0" />
                                <h4 className="font-semibold">{t('website_common.our_solutions.maintenance.title')} </h4>
                            </div>
                            <p>{t('website_common.our_solutions.maintenance.paragraph')}</p>
                            <a href={route('website.features.maintenance')} className="text-website-primary">
                                {t('website_common.know_more')}
                            </a>
                        </div>
                        <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                            <div className="flex gap-4">
                                <ReceiptText size={24} className="shrink-0" />
                                <h4 className="font-semibold">{t('website_common.our_solutions.contracts.title')} </h4>
                            </div>
                            <p>{t('website_common.our_solutions.contracts.paragraph')}</p>
                            <a href="" className="text-website-primary">
                                {t('website_common.know_more')}
                            </a>
                        </div>
                        <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                            <div className="flex gap-4">
                                <QrCode size={24} className="shrink-0" />
                                <h4 className="font-semibold">{t('website_common.our_solutions.qrcode.title')} </h4>
                            </div>
                            <p>{t('website_common.our_solutions.qrcode.paragraph')}</p>
                            <a href={route('website.features.qrcode')} className="text-website-primary">
                                {t('website_common.know_more')}
                            </a>
                        </div>
                        <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                            <div className="flex gap-4">
                                <FileStack size={24} className="shrink-0" />
                                <h4 className="font-semibold break-all">{t('website_common.our_solutions.documents.title')} </h4>
                            </div>
                            <p className="">{t('website_common.our_solutions.documents.paragraph')}</p>
                            <a href={route('website.features.documents')} className="text-website-primary">
                                {t('website_common.know_more')}
                            </a>
                        </div>
                        <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                            <div className="flex gap-4">
                                <ChartLine size={24} className="shrink-0" />
                                <h4 className="font-semibold">{t('website_common.our_solutions.statistics.title')} </h4>
                            </div>
                            <p>{t('website_common.our_solutions.statistics.paragraph')}</p>
                            <a href={route('website.features.statistics')} className="text-website-primary">
                                {t('website_common.know_more')}
                            </a>
                        </div>
                    </div>
                </div>
                <p className="text-website-font text-center text-xl italic">{t('website_common.our_solutions.headline')}</p>
            </div>
        </section>
    );
}
