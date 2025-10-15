import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function FeaturesRoles() {
    return (
        <WebsiteLayout>
            <Head>
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
            <section className="bg-website-primary -mt-20 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container"></div>
                <div className="mx-auto grid h-full gap-10 px-4 py-20 md:max-w-11/12 md:grid-cols-2 md:p-10">
                    <div className="flex flex-col justify-center gap-10 md:max-w-lg">
                        <h1 className="leading-16">
                            Des accès clairs pour <span className="font-extrabold">une gestion maîtrisée </span>
                        </h1>
                        <p className="">
                            SME-Facility définit des rôles simples et précis pour garantir une utilisation fluide et sécurisée. Chaque utilisateur
                            accède uniquement aux données et équipements qui le concernent, sans risque d’erreur ou de confusion.
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
            </section>
            <section className="text-website-font flex min-h-screen w-full flex-col items-center justify-center py-40">
                <div className="container">
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

                        <div className="border-website-border w-full rounded-md border p-6">
                            <details className="" open>
                                <summary className="text-2xl font-bold">
                                    Gérez vos tickets
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-3 text-lg">
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed viverra, purus eget ullamcorper ullamcorper, tellus
                                    magna interdum magna, et lacinia nisl purus vel dui. Nullam vel pulvinar diam, vitae aliquam nisi. Aliquam id arcu
                                    nec diam bibendum malesuada vel nec purus. Nunc semper, mi quis porttitor euismod, enim justo dictum felis, at
                                    elementum arcu odio id tellus. Donec molestie lacinia egestas. Quisque in odio et turpis iaculis egestas. Vivamus
                                    imperdiet vestibulum mauris, ac accumsan dui volutpat id. Sed vitae nibh ligula.
                                </p>
                            </details>
                            <details className="">
                                <summary className="text-2xl font-bold">
                                    Gérez vos tickets
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-3 text-lg">
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed viverra, purus eget ullamcorper ullamcorper, tellus
                                    magna interdum magna, et lacinia nisl purus vel dui. Nullam vel pulvinar diam, vitae aliquam nisi. Aliquam id arcu
                                    nec diam bibendum malesuada vel nec purus. Nunc semper, mi quis porttitor euismod, enim justo dictum felis, at
                                    elementum arcu odio id tellus. Donec molestie lacinia egestas. Quisque in odio et turpis iaculis egestas. Vivamus
                                    imperdiet vestibulum mauris, ac accumsan dui volutpat id. Sed vitae nibh ligula.
                                </p>
                            </details>
                            <details className="">
                                <summary className="text-2xl font-bold">
                                    Gérez vos tickets
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-3 text-lg">
                                    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed viverra, purus eget ullamcorper ullamcorper, tellus
                                    magna interdum magna, et lacinia nisl purus vel dui. Nullam vel pulvinar diam, vitae aliquam nisi. Aliquam id arcu
                                    nec diam bibendum malesuada vel nec purus. Nunc semper, mi quis porttitor euismod, enim justo dictum felis, at
                                    elementum arcu odio id tellus. Donec molestie lacinia egestas. Quisque in odio et turpis iaculis egestas. Vivamus
                                    imperdiet vestibulum mauris, ac accumsan dui volutpat id. Sed vitae nibh ligula.
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
