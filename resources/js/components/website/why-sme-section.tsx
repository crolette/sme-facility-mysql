import { useLaravelReactI18n } from 'laravel-react-i18n';
import { BadgeCheck, BadgeEuro, User } from 'lucide-react';

export default function WhySMESection() {
    const { t } = useLaravelReactI18n();

    return (
        <section className="bg-website-border py-20">
            <div className="container mx-auto">
                <div className="text-website-secondary mx-auto h-full space-y-10 px-4 py-10 md:max-w-11/12 md:p-10">
                    <h2>{t('website_common.why_sme.title')}</h2>
                    <h3>{t('website_common.why_sme.subtitle')}</h3>
                    <ul className="ml-6 flex flex-col gap-4">
                        <li className="grid grid-cols-[24px_1fr] gap-4">
                            <BadgeEuro className="inline-block" size={24} />
                            <p>{t('website_common.why_sme.one')}</p>
                        </li>
                        <li className="grid grid-cols-[24px_1fr] gap-4">
                            <User className="inline-block" />
                            <p>{t('website_common.why_sme.two')}</p>
                        </li>
                        <li className="grid grid-cols-[24px_1fr] gap-4">
                            <BadgeCheck className="inline-block" />
                            <p>{t('website_common.why_sme.three')}</p>
                        </li>
                        <li className="grid grid-cols-[24px_1fr] gap-4">
                            <BadgeEuro className="inline-block" />
                            <p>{t('website_common.why_sme.four')}</p>
                        </li>
                    </ul>
                    <a href={route('website.why')} className="!text-white">
                        {t('website_common.know_more')}
                    </a>
                </div>
            </div>
        </section>
    );
}
