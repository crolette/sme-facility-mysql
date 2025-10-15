import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function FeaturesDocuments() {
    return (
        <WebsiteLayout>
            <Head>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="SME-Facility | Centralisation des documents et fichiers" />
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
                                Signalez un problème en <span className="font-extrabold"> un scan. </span>
                            </h1>
                            <p className="">
                                Les QR codes SME-Facility permettent à n’importe quel utilisateur — collaborateur, occupant ou visiteur — de signaler
                                un incident instantanément. Un simple scan ouvre un formulaire de ticketing : description, photo, validation. Le
                                responsable de maintenance reçoit aussitôt la notification et peut planifier l’intervention.
                            </p>
                            <div className="flex flex-col gap-6 md:flex-row md:gap-10">
                                <Button variant={'cta'}>Prendre rendez-vous pour une démo</Button>
                                <Button variant={'transparent'}>Découvrir les formules</Button>
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
                                    <h6 className="font-semibold">Signalement ultra rapide</h6>
                                    <p>
                                        Un QR code sur chaque équipement ou local permet de créer un ticket en quelques secondes, sans login ni
                                        application à installer.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-2 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Communication directe et efficace</h6>
                                    <p>
                                        Dès qu’un ticket est soumis, le responsable reçoit un email avec toutes les informations nécessaires pour
                                        planifier une intervention.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-2 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Traçabilité des incidents</h6>
                                    <p>
                                        Tous les tickets sont liés à l’actif concerné, garantissant une vision claire de l’historique des problèmes et
                                        des actions menées.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <img src="../images/Group 20.png" alt="" className="w-full" />

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
