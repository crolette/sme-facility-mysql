import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function WhySME() {
    return (
        <WebsiteLayout>
            <Head>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="SME-Facility | La solution Facility Management pensée pour les PME" />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content="Découvrez pourquoi SME-Facility est le partenaire idéal des PME. Une solution cloud abordable, simple et complète pour centraliser vos équipements, maintenances et contrats."
                />

                <meta property="og:title" content="SME-Facility, votre partenaire Facility Management" />
                <meta
                    property="og:description"
                    content="Une solution conçue par une PME pour les PME : centralisation des données, simplicité d’utilisation, accompagnement personnalisé et coût maîtrisé. SME-Facility facilite la gestion de vos installations."
                />
            </Head>
            <section className="bg-website-secondary text-website-font -mt-20 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:max-w-10/12 md:grid-cols-2 md:p-10">
                        <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                            <h1 className="leading-16">
                                SME-Facility est <span className="font-extrabold">votre partenaire idéal </span>pour gérer vos installations.
                            </h1>
                            <p className="">Centralisation des informations de tous vos équipements</p>
                            <div className="flex flex-col gap-6 md:flex-row md:gap-10">
                                <Button variant={'cta'}>Prendre rendez-vous pour une démo</Button>
                                <Button variant={'transparent'}>Découvrir les formules</Button>
                            </div>
                        </div>
                        <div className="mx-auto my-auto">
                            <img src="../images/Group 22.png" alt="" className="" />
                        </div>
                    </div>
                </div>
            </section>
            <section className="text-website-font flex min-h-screen w-full flex-col items-center justify-center py-20">
                <div className="container">
                    <div className="mx-auto flex h-full flex-col gap-10 px-4 md:max-w-10/12 md:p-10">
                        <h2>Que de bonnes raisons</h2>
                        <div className="bg-logo text-website-card flex w-full flex-col gap-8 rounded-md p-6">
                            <h3>Une équipe proche de vous</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                <li>Nous sommes localisés en Belgique, au plus proche de vous.</li>
                                <li>Nous sommes également une PME et comprenons vos problématiques.</li>
                                <li>Une expérience dans le Facility Management depuis plus de 20 ans.</li>
                            </ul>
                        </div>
                        <div className="bg-website-secondary flex w-full flex-col gap-8 rounded-md p-6">
                            <h3>Facilité d'utilisation</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                <li>Localisés en Belgique, au plus proche de vous.</li>
                                <li>Localisés en Belgique, au plus proche de vous.</li>
                                <li>Localisés en Belgique, au plus proche de vous.</li>
                            </ul>
                        </div>
                        <div className="bg-website-primary text-website-card flex w-full flex-col gap-8 rounded-md p-6">
                            <h3>Coûts</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                <li>Localisés en Belgique, au plus proche de vous.</li>
                                <li>Localisés en Belgique, au plus proche de vous.</li>
                                <li>Localisés en Belgique, au plus proche de vous.</li>
                            </ul>
                        </div>
                        <div className="bg-website-card flex w-full flex-col gap-8 rounded-md p-6">
                            <h3>Une équipe à votre écoute</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                <li>Localisés en Belgique, au plus proche de vous.</li>
                                <li>Localisés en Belgique, au plus proche de vous.</li>
                                <li>Localisés en Belgique, au plus proche de vous.</li>
                            </ul>
                        </div>
                    </div>
                    <Button variant={'cta'} className="p-6 text-lg">
                        Prendre rendez-vous pour une démo
                    </Button>
                </div>
            </section>
        </WebsiteLayout>
    );
}
