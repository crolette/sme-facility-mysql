import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function WhoMaintenanceManager() {
    return (
        <WebsiteLayout>
            <Head title="Application mobile pour Responsables de maintenance">
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="Application mobile pour Responsables de maintenance | SME-Facility" />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content="Supervisez vos interventions, vos techniciens et vos équipements depuis une seule interface. SME-Facility simplifie la maintenance au quotidien."
                />

                <meta property="og:title" content="Gardez le contrôle de vos opérations de maintenance avec SME-Facility" />
                <meta
                    property="og:description"
                    content="Planifiez, suivez et analysez vos interventions où que vous soyez. SME-Facility offre aux responsables de maintenance une gestion fluide, mobile et performante des opérations."
                />
            </Head>
            <section className="bg-website-border -mt-28 flex min-h-screen w-full flex-col items-center justify-center py-20 md:-mt-38">
                <div className="container mx-auto">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:grid-cols-[2fr_1fr] md:px-10 md:py-16 lg:max-w-11/12">
                        <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                            <h1 className="leading-16">
                                <span className="font-extrabold">Gardez le contrôle sur vos interventions et vos équipes</span>, où que vous soyez.
                            </h1>
                            <h2 className="!text-xl">
                                SME-Facility accompagne les responsables de maintenance dans la gestion quotidienne des interventions, des équipes et
                                des équipements.
                            </h2>
                            <p className="">
                                Sur le terrain ou au bureau, vous disposez d’un outil mobile, simple et efficace pour organiser, suivre et optimiser
                                vos opérations de maintenance.
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
                                <p className="font-bold">Une vision globale et centralisée du parc technique</p>
                                <p>
                                    Accédez en temps réel à l’ensemble de vos actifs : équipements critiques, contrats, sites, prestataires et
                                    interventions. SME-Facility vous fournit une cartographie claire de votre patrimoine technique, facilitant la
                                    priorisation des actions et la prise de décision stratégique. L’historique détaillé des entretiens et
                                    interventions garantit une traçabilité complète.
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
                                <p className="font-bold">Planification et suivi des maintenances</p>
                                <p>
                                    Automatisez vos plans de maintenance préventive et générez des rappels pour chaque échéance. Vous pouvez suivre
                                    l’avancement des interventions en direct, évaluer la charge des équipes et analyser la performance des
                                    prestataires. Les bons d’intervention numériques standardisent les processus et améliorent la qualité du
                                    reporting.
                                </p>
                            </div>
                        </div>
                        <div className="relative grid grid-cols-1 gap-10 overflow-hidden p-10 md:grid-cols-2">
                            <span className="text-border/10 absolute top-1/3 left-14 -translate-1/2 font-sans text-[256px] font-extrabold">3</span>

                            <div className="space-y-4">
                                <p className="font-bold">Gestion documentaire et conformité simplifiée</p>
                                <p>
                                    SME-Facility intègre la gestion documentaire contextualisée : notices techniques, photos, rapports, certificats ou
                                    documents contractuels sont accessibles depuis chaque équipement. Grâce aux QR codes et à l’accès mobile, vos
                                    techniciens disposent toujours de la bonne information, réduisant les risques d’erreur et facilitant les audits.
                                </p>
                            </div>
                            <div className="flex items-center">
                                <img src="/images/Group 22.png" alt="" className="" />
                            </div>
                        </div>
                        <div className="relative grid grid-cols-1 gap-10 overflow-hidden p-10 md:grid-cols-2">
                            <span className="text-border/10 absolute top-1/4 -right-24 -translate-1/2 font-sans text-[256px] font-extrabold">4</span>
                            <div className="order-2 flex items-center md:order-none">
                                <img src="/images/Group 22.png" alt="" className="" />
                            </div>
                            <div className="space-y-4">
                                <p className="font-bold">Indicateurs de performance et aide à la décision</p>
                                <p>
                                    Les tableaux de bord et KPI intégrés permettent d’évaluer la disponibilité des équipements, la réactivité des
                                    interventions et le respect des SLA. SME-Facility devient un véritable outil d’aide à la décision, orienté vers la
                                    performance opérationnelle et la maîtrise des coûts.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
