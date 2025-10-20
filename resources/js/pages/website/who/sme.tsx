import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function WhoSME() {
    return (
        <WebsiteLayout>
            <Head title={'Application de Facility Management pour PME'}>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="Application de Facility Management pour PME | SME-Facility" />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content="Optimisez la gestion de vos équipements et la maintenance de votre PME avec SME-Facility, la solution cloud simple, rapide et prête à l’emploi."
                />

                <meta property="og:title" content="Simplifiez la gestion de vos équipements avec SME-Facility" />
                <meta
                    property="og:description"
                    content="SME-Facility aide les PME à centraliser la maintenance, suivre les contrats et automatiser les rappels. Un outil complet pour gagner du temps et booster la productivité."
                />
            </Head>
            <section className="bg-website-border text-website-card -mt-28 flex min-h-screen w-full flex-col items-center justify-center py-20 md:-mt-38">
                <div className="container mx-auto">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:grid-cols-[2fr_1fr] md:px-10 md:py-16 lg:max-w-11/12">
                        <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                            <h1 className="leading-16">
                                Simplifiez la gestion de vos équipements et{' '}
                                <span className="font-extrabold">concentrez-vous sur votre activité.</span>
                            </h1>
                            <h2 className="!text-xl">
                                Avec SME-Facility, les PME disposent enfin d’un outil de facility management simple, complet et prêt à l’emploi.
                            </h2>
                            <p className="">
                                Gagnez du temps, structurez vos opérations de maintenance et améliorez la productivité sans complexité technique.
                            </p>
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
                                <img src="/images/Group 22.png" alt="" className="" />
                            </div>
                        </div>
                        <div className="relative grid grid-cols-1 gap-10 overflow-hidden p-10 md:grid-cols-2">
                            <span className="text-border/10 absolute top-1/3 -right-24 -translate-1/2 font-sans text-[256px] font-extrabold">2</span>
                            <div className="order-2 flex items-center md:order-none">
                                <img src="/images/Group 22.png" alt="" className="" />
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
                                <img src="/images/Group 22.png" alt="" className="" />
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
