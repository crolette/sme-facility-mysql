import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function FeaturesStatistics() {
    return (
        <WebsiteLayout>
            <Head title="Tableau de bord et statistiques maintenance">
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="Tableau de bord et statistiques maintenance | SME-Facility" />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content="Pilotez vos interventions et suivez vos KPI maintenance. SME-Facility offre un tableau de bord visuel pour analyser la performance et améliorer la gestion de vos équipements."
                />

                <meta property="og:title" content="Analysez et pilotez vos performances de maintenance" />
                <meta
                    property="og:description"
                    content="Avec SME-Facility, suivez vos statistiques en temps réel : interventions effectuées, retards, temps moyens et répartition des maintenances. Prenez des décisions éclairées pour optimiser vos opérations."
                />
            </Head>
            <section className="bg-website-primary -mt-20 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:max-w-11/12 md:grid-cols-2 md:p-10">
                        <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                            <h1 className="leading-16">
                                Analysez, pilotez et <span className="font-extrabold">améliorez vos performances.</span>
                            </h1>
                            <p className="">
                                Grâce à son tableau de bord intégré, SME-Facility fournit une vision claire et chiffrée de votre activité. Suivez vos
                                interventions, détectez les retards, mesurez les temps moyens de traitement et pilotez la performance de votre
                                maintenance.
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
                            <img src="../images/Group 22.png" alt="" className="" />
                        </div>
                    </div>
                </div>
            </section>
            <section className="text-website-font flex min-h-screen w-full flex-col items-center justify-center py-40">
                <div className="container">
                    <div className="mx-auto flex h-full flex-col gap-10 px-4 md:max-w-11/12 md:gap-30">
                        <div className="grid gap-6 md:grid-cols-3">
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Indicateurs clés en temps réel</h6>
                                    <p>Visualisez l’évolution de vos interventions et maintenances grâce à des KPI clairs et actualisés.</p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Pilotage simplifié pour les responsables</h6>
                                    <p>
                                        Les statistiques sont accessibles aux administrateurs et Facility Managers pour un suivi global de l’activité.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Amélioration continue et décisions éclairées</h6>
                                    <p>Identifiez les points de blocage, optimisez vos ressources et améliorez vos délais d’intervention.</p>
                                </div>
                            </div>
                        </div>
                        <img src="/images/Group 20.png" alt="" className="w-full" />

                        <div className="border-website-border flex w-full flex-col gap-4 rounded-md border p-6">
                            <details className="" open>
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Tableau de bord visuel et intuitif</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    SME-Facility met à disposition un tableau de bord dynamique présentant vos principaux indicateurs : nombre total
                                    d’interventions, maintenance en retard, temps moyen de résolution et répartition des types d’intervention. Les
                                    graphiques facilitent la lecture et la comparaison dans le temps.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Suivi de la performance opérationnelle</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Les responsables peuvent mesurer la réactivité et la charge de travail des équipes, évaluer les prestataires et
                                    identifier les zones nécessitant une attention particulière. Ces données favorisent une gestion proactive et un
                                    meilleur équilibre des ressources.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Détection rapide des anomalies</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Les maintenances en retard et les interventions non clôturées apparaissent clairement dans le tableau de bord.
                                    Vous pouvez ainsi agir immédiatement pour corriger les retards et maintenir la qualité du service.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Vision globale pour la prise de décision</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    En centralisant les indicateurs clés, SME-Facility aide les administrateurs et Facility Managers à suivre les
                                    tendances et à orienter leurs décisions : planification, investissements, priorisation des interventions ou
                                    renforcement des contrats de maintenance.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Support à l’amélioration continue</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Les statistiques constituent un levier d’amélioration : elles permettent d’analyser l’efficacité des processus,
                                    d’ajuster les fréquences de maintenance et de démontrer la valeur ajoutée du service auprès de la direction.
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
