import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Linkedin, Youtube } from 'lucide-react';
import LocaleChange from '../tenant/LocaleChange';
import FaqSection from './faq_section';
import OurSolutions from './our_solutions';
import WhySMESection from './why-sme-section';

export default function Footer() {
    const { t } = useLaravelReactI18n();
    return (
        <>
            <OurSolutions />
            <WhySMESection />
            <FaqSection />

            <footer className="bg-logo flex flex-col items-center justify-center space-y-10 px-4 py-10 text-white md:p-20">
                <div className="container grid gap-12 md:grid-cols-4">
                    <div className="gap flex flex-col gap-10">
                        <img src="images/logo.png" alt="" className="w-40" />
                        <p>{t('website_common.footer.title')}</p>
                        <div className="flex gap-4">
                            <Linkedin></Linkedin>
                            <Youtube></Youtube>
                        </div>
                        <LocaleChange url={'website.locale'} />
                    </div>
                    <div className="flex flex-col gap-6">
                        <h6>{t('website_common.footer.column_one')}</h6>
                        <ul className="text-website-border text-md flex flex-col gap-4">
                            <li>
                                <a href={route('website.features.qrcode')} className="!no-underline">
                                    {t('website_common.qrcode')}
                                </a>
                            </li>
                            <li>
                                <a href={route('website.features.maintenance')} className="!no-underline">
                                    {t('website_common.maintenance')}
                                </a>
                            </li>
                            <li>
                                <a href={route('website.features.contracts')} className="!no-underline">
                                    {t('website_common.contracts')}
                                </a>
                            </li>
                            <li>
                                <a href={route('website.features.documents')} className="!no-underline">
                                    {t('website_common.documents')}
                                </a>
                            </li>
                            <li>
                                <a href={route('website.features.assets')} className="!no-underline">
                                    {t('website_common.assets')}
                                </a>
                            </li>
                            <li>
                                <a href={route('website.features.statistics')} className="!no-underline">
                                    {t('website_common.statistics')}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div className="flex flex-col gap-6">
                        <h6>{t('website_common.footer.column_two')}</h6>
                        <ul className="text-website-border text-md flex flex-col gap-4">
                            <li>
                                <a href={route('website.who.facility-manager')} className="!no-underline">
                                    {t('website_common.facility_manager')}
                                </a>
                            </li>
                            <li>
                                <a href={route('website.who.maintenance-manager')} className="!no-underline">
                                    {t('website_common.maintenance_manager')}
                                </a>
                            </li>
                            <li>
                                <a href={route('website.who.sme')} className="!no-underline">
                                    {t('website_common.sme')}
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div className="flex flex-col gap-6">
                        <h6>{t('website_common.footer.column_three')}</h6>
                        <ul className="text-website-border text-md flex flex-col gap-4">
                            <li>
                                <a href={route('website.why')} className="!no-underline">
                                    {t('website_common.footer.who_are_we')}
                                </a>
                            </li>
                            <li>
                                <a href={route('website.faq')} className="!no-underline">
                                    {t('website_common.faq')}
                                </a>
                            </li>
                            <li> {t('website_common.footer.implementation')}</li>
                            <li> {t('website_common.footer.careers')}</li>
                            <li>
                                <a href={route('website.contact')} className="!no-underline">
                                    {t('website_common.footer.contact')}
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
                <div className="text-website-border container mx-auto flex w-full flex-col justify-between gap-4 lg:flex-row">
                    <p>
                        © SME-Facility 2025. {t('website_common.footer.sme_service')}{' '}
                        <a href="https://www.facilitywebxp.be" target="_blank">
                            Facility Web Experience srl
                        </a>
                    </p>
                    <ul className="flex flex-col md:flex-row">
                        <li> {t('website_common.footer.cgu')}</li>
                        <span className="hidden md:inline-block">|</span>
                        <li> {t('website_common.footer.cgv')}</li>
                        <span className="hidden md:inline-block">|</span>
                        <li> {t('website_common.footer.legal')}</li>
                    </ul>
                </div>
            </footer>
        </>
    );
}
