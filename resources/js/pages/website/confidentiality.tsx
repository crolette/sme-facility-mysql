import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function Confidentiality() {
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
                    <div className="mx-auto flex flex-col gap-10 p-4 md:p-10 lg:max-w-11/12">
                        <h1>{t('website_common.footer.confidentiality')}</h1>
                        <p>
                            <strong>Dernière mise à jour : 25/11/2025</strong>
                        </p>

                        <h2>Collecte des données</h2>
                        <p>Nous collectons votre adresse email lorsque vous vous inscrivez à notre liste d'attente.</p>

                        <h2>Utilisation des données</h2>
                        <p>Votre email sera utilisé uniquement pour :</p>
                        <ul>
                            <li>Vous informer du lancement de SME-Facility</li>
                            <li>Vous envoyer des actualités sur notre plateforme</li>
                        </ul>

                        <h2>Durée de conservation</h2>
                        <p>Vos données sont conservées jusqu'à votre désinscription.</p>

                        <h2>Vos droits</h2>
                        <p>
                            Conformément au RGPD, vous disposez d'un droit d'accès, de rectification et de suppression de vos données.
                            <br />
                            Pour exercer ces droits : <a href="mailto:contact@sme-facility.com">contact@sme-facility.com</a>
                        </p>

                        <h2>Sécurité</h2>
                        <p>Vos données sont stockées de manière sécurisée et ne sont jamais partagées avec des tiers.</p>

                        <h2>Désinscription</h2>
                        <p>Vous pouvez vous désinscrire à tout moment via le lien présent dans nos emails.</p>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
