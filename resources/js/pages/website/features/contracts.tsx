import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function FeaturesContracts() {
    return (
        <WebsiteLayout>
            <Head title="Gestion des contrats et garanties">
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="Gestion des contrats et garanties | SME-Facility" />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content="Centralisez vos contrats, suivez vos fournisseurs et recevez des alertes avant les échéances. SME-Facility simplifie la gestion des engagements et des garanties pour vos équipements."
                />

                <meta property="og:title" content="Anticipez vos échéances avec SME-Facility" />
                <meta
                    property="og:description"
                    content="Gérez vos contrats de maintenance et garanties en toute simplicité. SME-Facility vous alerte avant chaque échéance pour mieux piloter vos fournisseurs et vos actifs techniques."
                />
            </Head>
            <section className="bg-website-primary -mt-20 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:max-w-11/12 md:grid-cols-2 md:p-10">
                        <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                            <h1 className="leading-16">
                                Anticipez vos échéances et <span className="font-extrabold">maîtrisez vos contrats </span>
                            </h1>
                            <p className="">
                                SME-Facility centralise la gestion de vos contrats et garanties. Vous suivez vos engagements, vos fournisseurs et vos
                                dates d’échéance depuis une interface unique, tout en recevant des alertes automatiques avant les renouvellements ou
                                les fins de garantie.
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
                                    <h6 className="font-semibold">Contrats centralisés</h6>
                                    <p>
                                        Gérez facilement tous vos contrats, assignez-les à des équipements ou des locaux, et reliez-les à vos
                                        fournisseurs pour un suivi global et structuré.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Notifications avant échéance</h6>
                                    <p>
                                        Recevez automatiquement un email avant la fin d’un contrat ou d’une garantie afin de renouveler, prolonger ou
                                        résilier à temps.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Suivi clair des statuts</h6>
                                    <p>
                                        Identifiez en un coup d’œil les contrats actifs, expirés ou annulés, et gardez une vision claire sur vos
                                        engagements en cours.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <img src="/images/Group 20.png" alt="" className="w-full" />

                        <div className="border-website-border flex w-full flex-col gap-4 rounded-md border p-6">
                            <details className="" open>
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Encodage complet des contrats</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Créez des contrats avec leur type (maintenance, location, assurance, service, etc.), leur fournisseur associé,
                                    leur durée et leur délai de préavis. Vous pouvez préciser si la reconduction est manuelle ou automatique afin
                                    d’éviter toute mauvaise surprise à la date d’échéance.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Association directe aux assets ou locaux</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Chaque contrat peut être rattaché à un ou plusieurs équipements ou emplacements, offrant une traçabilité complète
                                    entre vos installations, vos prestataires et vos engagements contractuels.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Notifications automatiques par email</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Avant la fin d’un contrat ou d’une garantie, SME-Facility envoie une alerte par email au responsable concerné.
                                    Vous disposez ainsi du temps nécessaire pour anticiper les renouvellements, renégocier les conditions ou organiser
                                    une résiliation dans les délais.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Suivi du cycle de vie des contrats</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Chaque contrat dispose d’un statut (en cours, expiré, annulé) mis à jour automatiquement selon la date d’échéance.
                                    Cette visibilité vous permet de maîtriser vos coûts, d’éviter les reconductions involontaires et de garder le
                                    contrôle sur vos fournisseurs.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Gestion des garanties intégrée aux assets</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Pour chaque équipement, vous pouvez indiquer si une garantie est active, sa durée et sa date de fin. SME-Facility
                                    vous notifie automatiquement à l’approche de son expiration, vous permettant d’agir rapidement avant la fin de
                                    couverture.
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
