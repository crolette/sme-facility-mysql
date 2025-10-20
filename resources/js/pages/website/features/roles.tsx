import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function FeaturesRoles() {
    return (
        <WebsiteLayout>
            <Head title="Rôles et gestion des accès utilisateurs">
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="Rôles et gestion des accès utilisateurs | SME-Facility" />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content="Des accès simples et sécurisés. SME-Facility définit des rôles clairs pour les PME : Facility Manager et Responsable de maintenance, chacun avec des droits adaptés."
                />

                <meta property="og:title" content="Des accès clairs pour une gestion maîtrisée" />
                <meta
                    property="og:description"
                    content="Avec SME-Facility, les rôles sont simples et précis. Le Facility Manager supervise tout, le Responsable de maintenance gère ses équipements. Clarté, sécurité et efficacité garanties."
                />
            </Head>
            <section className="bg-website-primary text-website-card -mt-20 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container mx-auto">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:max-w-11/12 md:grid-cols-2 md:px-10">
                        <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                            <h1 className="leading-16">
                                Des accès clairs pour <span className="font-extrabold">une gestion maîtrisée </span>
                            </h1>
                            <p className="">
                                SME-Facility définit des rôles simples et précis pour garantir une utilisation fluide et sécurisée. Chaque utilisateur
                                accède uniquement aux données et équipements qui le concernent, sans risque d’erreur ou de confusion.
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
                <div className="container mx-auto">
                    <div className="mx-auto flex h-full flex-col gap-10 px-4 md:max-w-10/12 md:gap-30">
                        <div className="grid gap-6 md:grid-cols-3">
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Structure claire et sans complexité</h6>
                                    <p>
                                        Deux rôles distincts, pensés pour les PME : Facility Manager et Responsable de maintenance, chacun avec des
                                        droits adaptés à ses responsabilités.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Accès sécurisé et contrôlé</h6>
                                    <p>
                                        Chaque utilisateur agit uniquement sur les données qu’il gère, assurant la cohérence et la fiabilité des
                                        informations partagées.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Visibilité adaptée à chaque profil</h6>
                                    <p>
                                        Les Facility Managers conservent une vue globale tandis que les Responsables de maintenance se concentrent sur
                                        leurs propres équipements.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <img src="/images/Group 20.png" alt="" className="w-full" />

                        <div className="border-website-border flex w-full flex-col gap-4 rounded-md border p-6">
                            <details className="" open>
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Facility Manager : supervision complète</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Le Facility Manager dispose d’un accès complet à l’application : création d’assets, configuration des
                                    emplacements, gestion des contrats et suivi global de la maintenance. Il supervise l’ensemble du parc technique et
                                    des opérations.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Responsable de maintenance : gestion ciblée</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Ce profil accède uniquement aux assets dont il est responsable. Il peut mettre à jour les informations et suivre
                                    les interventions sans interférer avec les autres zones de gestion, garantissant une utilisation simple et
                                    maîtrisée.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Un modèle pensé pour les PME</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    La séparation des rôles dans SME-Facility évite la complexité des systèmes à permissions multiples. Vous
                                    bénéficiez d’un outil clair, facile à administrer et parfaitement adapté aux structures à taille humaine.
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
