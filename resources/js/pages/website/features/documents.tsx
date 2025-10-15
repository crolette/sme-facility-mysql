import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function FeaturesDocuments() {
    return (
        <WebsiteLayout>
            <Head title="Centralisation des documents et fichiers">
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="Centralisation des documents et fichiers | SME-Facility" />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content="Centralisez tous vos documents techniques, contrats et rapports dans une interface unique. SME-Facility simplifie la gestion documentaire et améliore la traçabilité de vos données."
                />

                <meta property="og:title" content="Tous vos documents accessibles en un clic" />
                <meta
                    property="og:description"
                    content="SME-Facility centralise la gestion documentaire de votre entreprise : fiches techniques, contrats, rapports et photos, reliés à vos équipements et interventions pour un accès rapide et sécurisé."
                />
            </Head>
            <section className="bg-website-primary -mt-20 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:max-w-11/12 md:grid-cols-2 md:p-10">
                        <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                            <h1 className="leading-16">
                                Tous vos documents, <span className="font-extrabold">accessibles en un clic </span>
                            </h1>
                            <p className="">
                                SME-Facility centralise l’ensemble des documents liés à vos équipements, contrats et interventions. Plus besoin de
                                chercher dans plusieurs dossiers : chaque fichier est rattaché à son actif ou à son opération, accessible
                                instantanément par votre équipe.
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
            <section className="text-website-font flex min-h-screen w-full flex-col items-center justify-center py-40">
                <div className="container">
                    <div className="mx-auto flex h-full flex-col gap-10 px-4 md:max-w-11/12 md:gap-30">
                        <div className="grid gap-6 md:grid-cols-3">
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Accès rapide à l’information</h6>
                                    <p>
                                        Chaque document est relié à son contexte (équipement, contrat, intervention) pour une recherche immédiate et
                                        intuitive.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Partage et collaboration simplifiés</h6>
                                    <p>
                                        Les utilisateurs autorisés peuvent consulter les fichiers dont ils ont besoin, sans échange d’emails ni
                                        doublons.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Archivage et traçabilité garantis</h6>
                                    <p>Toutes les pièces jointes sont conservées et historisées, assurant une parfaite traçabilité documentaire.</p>
                                </div>
                            </div>
                        </div>
                        <img src="/images/Group 20.png" alt="" className="w-full" />

                        <div className="border-website-border flex w-full flex-col gap-4 rounded-md border p-6">
                            <details className="" open>
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Rattachement documentaire contextuel</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Chaque document (photo, rapport, devis, certificat, fiche technique, contrat, etc.) peut être attaché directement
                                    à un actif, une intervention ou un fournisseur. Vous retrouvez toujours la bonne information, au bon endroit.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Consultation rapide depuis une interface unique</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Plus de fichiers dispersés ou versions contradictoires : SME-Facility réunit toute la documentation dans une seule
                                    interface claire. Les utilisateurs peuvent accéder en ligne aux documents dont ils ont besoin, selon leurs
                                    permissions.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Gestion collaborative et sécurisée</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Les documents sont stockés dans un environnement cloud sécurisé. Les membres autorisés — internes ou externes —
                                    peuvent y accéder sans échange de pièces jointes, améliorant la productivité et la cohérence des informations.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Traçabilité et conformité documentaire</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Chaque fichier est historisé et lié à un contexte précis, permettant de retrouver rapidement la version utilisée
                                    lors d’une intervention, d’un audit ou d’un renouvellement de contrat.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Gain de temps et réduction des erreurs</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    La centralisation élimine les pertes de documents, les doublons et les recherches inutiles. Les équipes gagnent du
                                    temps, les audits deviennent simples, et la gestion documentaire plus fluide et fiable.
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
