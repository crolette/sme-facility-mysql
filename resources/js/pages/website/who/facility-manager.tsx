import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function WhoFacilityManager() {
    return (
        <WebsiteLayout>
            <Head>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="SME-Facility | Outil de Facility Management pour les Facility Managers" />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content="Centralisez vos installations, planifiez vos maintenances et suivez vos KPI avec SME-Facility, la plateforme complète pour Facility Managers exigeants."
                />

                <meta property="og:title" content="Pilotez vos installations avec précision grâce à SME-Facility" />
                <meta
                    property="og:description"
                    content="Avec SME-Facility, les Facility Managers disposent d’un outil complet pour gérer le parc technique, automatiser la maintenance préventive et mesurer la performance opérationnelle."
                />
            </Head>
            <section className="bg-website-border -mt-28 flex min-h-screen w-full flex-col items-center justify-center py-20 md:-mt-38">
                <div className="container mx-auto">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:max-w-11/12 md:grid-cols-[2fr_1fr] md:px-10 md:py-16">
                        <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                            <h1 className="leading-16">
                                <span className="font-extrabold">Pilotez vos installations</span> avec précision et optimisez vos opérations de
                                maintenance.
                            </h1>
                            <p className="">
                                SME-Facility offre aux Facility Managers une plateforme complète de gestion des installations et du cycle de vie des
                                équipements. En centralisant les données techniques, les interventions et les prestataires, vous maîtrisez vos coûts,
                                sécurisez vos opérations et améliorez la performance de vos sites.
                            </p>
                        </div>
                        <div className="mx-auto my-auto">
                            <img src="../images/Group 22.png" alt="" className="" />
                        </div>
                    </div>
                    <div className="mx-auto flex w-full flex-col items-center justify-center gap-6 md:flex-row md:gap-10">
                        <Button variant={'cta'}>Prendre rendez-vous pour une démo</Button>
                        <Button variant={'transparent'}>Découvrir les formules</Button>
                    </div>
                </div>
            </section>
            <section className="text-website-font flex min-h-screen w-full flex-col items-center justify-center gap-20 py-40">
                <div className="container mx-auto">
                    <div className="mx-auto flex h-full flex-col gap-10 px-4 md:max-w-11/12 md:gap-30">
                        <div className="relative grid grid-cols-1 gap-10 overflow-hidden p-10 md:grid-cols-2">
                            <span className="text-border/10 absolute top-1/3 left-14 -translate-1/2 font-sans text-[256px] font-extrabold">1</span>

                            <div className="space-y-4">
                                <p className="font-bold">Centralisez vos informations et équipements</p>
                                <p>
                                    Finis les fichiers dispersés et les informations perdues : SME-Facility rassemble l’ensemble de vos équipements,
                                    contrats et interventions dans un seul espace sécurisé. Vous disposez d’un inventaire numérique complet,
                                    accessible en tout lieu, pour suivre vos actifs techniques en temps réel.
                                </p>
                            </div>
                            <div className="flex items-center">
                                <img src="../images/Group 22.png" alt="" className="" />
                            </div>
                        </div>
                        <div className="relative grid grid-cols-1 gap-10 overflow-hidden p-10 md:grid-cols-2">
                            <span className="text-border/10 absolute top-1/3 -right-24 -translate-1/2 font-sans text-[256px] font-extrabold">2</span>
                            <div className="order-2 flex items-center md:order-none">
                                <img src="../images/Group 22.png" alt="" className="" />
                            </div>
                            <div className="space-y-4">
                                <p className="font-bold">Automatisez vos rappels et échéances</p>
                                <p>
                                    Ne ratez plus un entretien, un contrat ou une vérification réglementaire. Le système vous envoie des alertes
                                    automatiques pour toutes les échéances importantes. Vous anticipez les interventions et évitez les arrêts
                                    imprévus, tout en réduisant la charge administrative.
                                </p>
                            </div>
                        </div>
                        <div className="relative grid grid-cols-1 gap-10 overflow-hidden p-10 md:grid-cols-2">
                            <span className="text-border/10 absolute top-1/3 left-14 -translate-1/2 font-sans text-[256px] font-extrabold">3</span>

                            <div className="space-y-4">
                                <p className="font-bold">Simplifiez la communication avec vos prestataires</p>
                                <p>
                                    Avec le portail de ticketing, vos techniciens, sous-traitants ou partenaires peuvent échanger directement sur
                                    chaque demande. Les notifications par email tiennent toutes les parties informées, pour un suivi fluide et
                                    transparent.
                                </p>
                            </div>
                            <div className="flex items-center">
                                <img src="../images/Group 22.png" alt="" className="" />
                            </div>
                        </div>
                        <div className="relative grid grid-cols-1 gap-10 overflow-hidden p-10 md:grid-cols-2">
                            <span className="text-border/10 absolute top-1/4 -right-24 -translate-1/2 font-sans text-[256px] font-extrabold">4</span>
                            <div className="order-2 flex items-center md:order-none">
                                <img src="../images/Group 22.png" alt="" className="" />
                            </div>
                            <div className="space-y-4">
                                <p className="font-bold">Démarrez immédiatement, sans paramétrage complexe</p>
                                <p>
                                    SME-Facility est 100 % cloud : aucune installation, aucune configuration technique. En quelques minutes, vous
                                    définissez vos préférences d’entreprise et commencez à gérer vos équipements. Une interface intuitive vous
                                    garantit une prise en main rapide et des gains de productivité immédiats.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
