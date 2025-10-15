import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function WhySME() {
    return (
        <WebsiteLayout>
            <Head title={'Pourquoi choisir SME-Facility ?'}>
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
                <div className="container mx-auto">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:grid-cols-2 md:p-10 lg:max-w-11/12">
                        <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                            <h1 className="">
                                SME-Facility est <span className="font-extrabold">votre partenaire idéal </span>pour gérer vos installations.
                            </h1>
                            <h2 className="!text-xl">
                                Conçu pour les PME, SME-Facility réunit toutes les fonctionnalités essentielles du Facility Management dans une
                                solution simple, accessible et performante.
                            </h2>
                            <p className="">Une plateforme complète, sans complexité inutile, pour maîtriser vos coûts et gagner en efficacité.</p>
                            <div className="flex flex-col items-center gap-6 md:flex-row md:gap-10">
                                <a href={route('website.contact')}>
                                    <Button variant={'cta'}>Prendre rendez-vous pour une démo</Button>
                                </a>
                                <a href={route('website.pricing')}>
                                    <Button variant={'transparent'}>Découvrir les formules</Button>
                                </a>
                            </div>
                        </div>
                        <div className="mx-auto my-auto">
                            <img src="/images/Group 22.png" alt="" className="" />
                        </div>
                    </div>
                </div>
            </section>
            <section className="text-website-font flex min-h-screen w-full flex-col items-center justify-center py-20">
                <div className="container mx-auto">
                    <div className="mx-auto flex h-full flex-col items-center gap-10 px-4 md:max-w-10/12 md:p-10">
                        <h2>Que de bonnes raisons de choisir SME-Facility</h2>

                        <div className="bg-website-card relative flex w-full flex-col gap-8 overflow-hidden rounded-md p-6">
                            <span className="text-website-border/20 absolute top-1/3 left-9 -translate-1/2 font-sans text-[256px] font-extrabold">
                                1
                            </span>
                            <h3>Accompagnement : un vrai soutien sur le terrain</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                <li>Notre équipe vous accompagne dans la mise en place de votre programme de maintenance.</li>
                                <li>Nous vous conseillons dans la définition de vos actifs critiques et de vos stratégies FM.</li>
                                <li>Un suivi personnalisé pour garantir le succès de votre déploiement.</li>
                                <li>Une aide concrète à l’importation de vos données et au paramétrage initial.</li>
                            </ul>
                        </div>
                        <div className="bg-website-secondary relative flex w-full flex-col gap-8 overflow-hidden rounded-md p-6">
                            <span className="text-border/5 absolute top-1/3 left-10 -translate-1/2 font-sans text-[256px] font-extrabold">2</span>
                            <h3>Centralisation : tout au même endroit</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                <li>
                                    L’ensemble de vos données — équipements, maintenances, contrats, garanties — regroupé dans une seule plateforme.
                                </li>
                                <li>Une vision unifiée de votre patrimoine technique et de vos opérations.</li>
                                <li>Moins de temps perdu à chercher l’information, plus d’efficacité au quotidien.</li>
                                <li>Une base de données structurée, prête pour la collaboration interne et externe.</li>
                            </ul>
                        </div>
                        <div className="bg-website-primary/90 text-website-card relative flex w-full flex-col gap-8 overflow-hidden rounded-md p-6">
                            <span className="text-website-secondary/20 absolute top-1/3 left-10 -translate-1/2 font-sans text-[256px] font-extrabold">
                                3
                            </span>
                            <h3>Facilité d’utilisation : simple, intuitive et rapide</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                <li>Une interface ergonomique pensée pour les PME, aucune formation complexe requise.</li>
                                <li>Import et export Excel pour intégrer rapidement vos données existantes ou extraire vos rapports.</li>
                                <li>Un démarrage rapide : paramétrage minimal, pas d’intervention extérieure.</li>
                                <li>Une application web fluide, accessible en tout lieu et sur tout appareil.</li>
                            </ul>
                        </div>

                        <div className="bg-logo text-website-card relative flex w-full flex-col gap-8 overflow-hidden rounded-md p-6">
                            <span className="text-website-secondary/20 absolute top-1/3 left-14 -translate-1/2 font-sans text-[256px] font-extrabold">
                                4
                            </span>
                            <h3>Coût : une solution performante et abordable</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                <li>
                                    Un abonnement clair, sans frais cachés ni installation technique (
                                    <a href={route('website.pricing')}>voir les tarifs</a>).
                                </li>
                                <li>Une réduction immédiate des coûts projets grâce à une mise en service rapide et sans maintenance IT.</li>
                                <li>Une solution 100 % cloud, sans serveur ni infrastructure à gérer.</li>
                                <li>Un retour sur investissement mesurable dès les premiers mois d’utilisation.</li>
                            </ul>
                        </div>
                        <div className="bg-website-font text-website-card relative flex w-full flex-col gap-8 overflow-hidden rounded-md p-6">
                            <span className="text-website-secondary/20 absolute top-1/3 left-14 -translate-1/2 font-sans text-[256px] font-extrabold">
                                5
                            </span>
                            <h3>Une PME pour les PME : proximité et compréhension</h3>
                            <ul className="ml-10 flex list-decimal flex-col gap-8">
                                <li>SME-Facility est développée par une PME, pour les PME : proximité, réactivité et écoute.</li>
                                <li>Nous comprenons vos contraintes, vos priorités et votre réalité terrain.</li>
                                <li>Une approche humaine, loin des grands groupes et des logiciels impersonnels.</li>
                                <li>Des échanges directs avec l’équipe qui conçoit et améliore la solution.</li>
                            </ul>
                        </div>
                        <a href={route('website.contact')}>
                            <Button variant={'cta'}>Prendre rendez-vous pour une démo</Button>
                        </a>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
