import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function FeaturesMaintenance() {
    return (
        <WebsiteLayout>
            <Head title="Planification et suivi de maintenance">
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="Planification et suivi de maintenance | SME-Facility" />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content="Planifiez vos maintenances, recevez des notifications avant échéance et suivez vos interventions. SME-Facility simplifie la gestion préventive et corrective pour les PME."
                />

                <meta property="og:title" content="Anticipez et planifiez vos maintenances avec SME-Facility" />
                <meta
                    property="og:description"
                    content="SME-Facility permet de planifier les maintenances selon leur fréquence, d’être alerté à l’approche des échéances et de créer des interventions ciblées pour chaque équipement."
                />
            </Head>
            <section className="bg-website-primary -mt-20 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:max-w-11/12 md:grid-cols-2 md:p-10">
                        <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                            <h1 className="leading-16">
                                Structurez vos maintenances et <span className="font-extrabold">gardez le contrôle.</span>
                            </h1>
                            <p className="">
                                SME-Facility vous aide à organiser vos maintenances planifiées selon la fréquence définie pour chaque équipement ou
                                local. Les responsables sont automatiquement notifiés à l’approche d’une échéance afin de planifier une intervention
                                au bon moment.
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
                                <div className="grid h-full grid-rows-2 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Maintenance planifiée par fréquence</h6>
                                    <p>
                                        Définissez la fréquence de maintenance pour vos équipements et laissez SME-Facility calculer la prochaine date
                                        à prévoir, selon la dernière opération effectuée.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-2 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Notification avant échéance</h6>
                                    <p>
                                        Le système vous alerte automatiquement lorsqu’une maintenance arrive à échéance, afin d’assurer le respect du
                                        planning et d’éviter les oublis.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-2 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Création d’interventions ciblées</h6>
                                    <p>
                                        À partir d’une maintenance planifiée ou d’un besoin ponctuel, créez facilement une intervention : maintenance
                                        préventive, corrective, nettoyage, visite ou autre.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <img src="/images/Group 20.png" alt="" className="w-full" />

                        <div className="border-website-border flex w-full flex-col gap-4 rounded-md border p-6">
                            <details className="" open>
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Paramétrage des fréquences de maintenance</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Lors de la création ou de la mise à jour d’un asset ou d’un local, indiquez la fréquence à laquelle une
                                    maintenance doit être effectuée (mensuelle, semestrielle, annuelle, etc.). SME-Facility enregistre cette donnée
                                    comme base de planification.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Calcul de la prochaine maintenance</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Le système détermine automatiquement la prochaine date de maintenance en fonction de la dernière date renseignée
                                    (ou de la date actuelle s’il s’agit de la première). Cette approche vous permet d’avoir une vision claire du
                                    planning global.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Notification automatique au responsable</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Avant chaque échéance, SME-Facility envoie une alerte au responsable de maintenance ou au facility manager. Vous
                                    pouvez alors planifier la maintenance à venir en créant une intervention au moment opportun.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Distinction entre maintenance planifiée et intervention ponctuelle</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Les maintenances assurent la continuité et la fiabilité de vos équipements, tandis que les interventions répondent
                                    aux besoins ponctuels et imprévus. En séparant ces deux approches, SME-Facility vous aide à anticiper les actions
                                    récurrentes, limiter les urgences et optimiser vos ressources pour une meilleure maîtrise des coûts et de la
                                    performance.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Historique et visibilité complète</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Chaque maintenance effectuée et chaque intervention ponctuelle sont enregistrées dans l’historique de l’actif.
                                    Vous disposez d’une traçabilité complète des opérations, utile pour le suivi réglementaire, les audits ou les
                                    décisions de remplacement.
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
