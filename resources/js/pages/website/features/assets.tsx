import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function FeaturesAssets() {
    return (
        <WebsiteLayout>
            <Head>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="Inventaire et gestion des équipements | SME-Facility" />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content="Centralisez vos équipements dans une base claire et reliée à vos maintenances, contrats et interventions. SME-Facility simplifie la gestion et le suivi de votre parc technique."
                />

                <meta property="og:title" content="Gardez une vue claire sur vos équipements" />
                <meta
                    property="og:description"
                    content="SME-Facility vous permet de recenser, organiser et suivre vos équipements en un seul endroit. Visualisez votre patrimoine technique et gérez vos actifs avec simplicité et précision."
                />
            </Head>
            <section className="bg-website-primary -mt-20 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container mx-auto">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:max-w-11/12 md:grid-cols-2 md:p-10">
                        <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                            <h1 className="">
                                Gardez une <span className="font-extrabold">vue claire sur vos équipements</span>
                            </h1>
                            <p className="">
                                SME-Facility centralise l’ensemble de vos équipements et installations dans une base claire et structurée. Vous
                                visualisez, organisez et suivez facilement votre patrimoine technique tout en reliant chaque élément à ses
                                maintenances, contrats, garanties et interventions.
                            </p>
                            <div className="flex flex-col gap-6 md:flex-row md:gap-10">
                                <Button variant={'cta'}>Prendre rendez-vous pour une démo</Button>
                                <Button variant={'transparent'}>Découvrir les formules</Button>
                            </div>
                        </div>
                        <div className="mx-auto my-auto">
                            <img src="/images/Group 22.png" alt="" className="" />
                        </div>
                    </div>
                </div>
            </section>
            <section className="text-website-font min-h-screen w-full py-40">
                <div className="container mx-auto">
                    <div className="mx-auto flex h-full flex-col gap-10 px-4 md:max-w-11/12 md:gap-30">
                        <div className="grid gap-6 md:grid-cols-3">
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Vue d'ensemble du parc technique</h6>
                                    <p>
                                        Regroupez tous vos actifs dans une interface unique : machines, installations, locaux, ou équipements
                                        critiques.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Lien entre équipements et opérations</h6>
                                    <p>
                                        Chaque équipement est connecté à ses contrats, interventions, maintenances et garanties pour une traçabilité
                                        complète.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Base patrimoniale simple à maintenir</h6>
                                    <p>Ajoutez, modifiez ou retrouvez vos équipements en quelques clics, sans tableurs ni fichiers dispersés.</p>
                                </div>
                            </div>
                        </div>
                        <img src="/images/Group 20.png" alt="" className="w-full" />

                        <div className="border-website-border flex w-full flex-col gap-4 rounded-md border p-6">
                            <details className="" open>
                                <summary className="text-2xl font-bold">
                                    <h3>Recensement structuré des actifs</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Créez un inventaire complet de vos équipements et de vos sites. Chaque élément peut être décrit avec ses
                                    caractéristiques principales : type, référence, localisation, fournisseur, numéro de série, date d’installation ou
                                    statut opérationnel.
                                </p>
                            </details>
                            <details className="">
                                <summary className="text-2xl font-bold">
                                    <h3>Liaison automatique avec les autres modules</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Les équipements enregistrés sont automatiquement reliés à leurs contrats, maintenances planifiées, interventions
                                    et garanties. Cette approche intégrée permet de suivre tout le cycle de vie de vos actifs depuis une seule
                                    interface.
                                </p>
                            </details>
                            <details className="">
                                <summary className="text-2xl font-bold">
                                    <h3>Recherche et filtres avancés</h3>

                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Retrouvez rapidement un équipement grâce à des filtres par type, localisation, fournisseur ou statut. Cette
                                    fonctionnalité facilite la gestion quotidienne, la planification des interventions et la préparation des audits.
                                </p>
                            </details>
                            <details className="">
                                <summary className="text-2xl font-bold">
                                    <h3>Historique complet de la vie des équipements</h3>

                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Chaque opération effectuée (maintenance, intervention, mise à jour, changement de statut) est historisée. Vous
                                    disposez d’une traçabilité complète, utile pour analyser la fiabilité, prévoir les remplacements ou optimiser les
                                    budgets.
                                </p>
                            </details>
                            <details className="">
                                <summary className="text-2xl font-bold">
                                    <h3>Simplicité d’utilisation et accessibilité cloud</h3>

                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    L’inventaire est accessible en tout temps depuis le web. Aucune installation technique n’est requise, et les
                                    informations sont partagées en temps réel entre les responsables, techniciens et prestataires autorisés.
                                </p>
                            </details>
                        </div>
                        <Button variant={'cta'} className="mx-auto w-fit p-6 text-lg">
                            Prendre rendez-vous pour une démo
                        </Button>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
