import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';

export default function FeaturesQrCode() {
    return (
        <WebsiteLayout>
            <Head title="Signalement d’incident par QR Code">
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="Signalement d’incident par QR Code | SME-Facility" />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content="Permettez à chacun de signaler un problème en un scan. SME-Facility simplifie la création de tickets grâce aux QR codes placés sur vos équipements et locaux."
                />

                <meta property="og:title" content="Signalez un incident en un scan avec SME-Facility" />
                <meta
                    property="og:description"
                    content="Les QR codes SME-Facility permettent à tout utilisateur de créer un ticket instantanément. Le responsable reçoit la demande et planifie l’intervention en un clic."
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
                                    <h6 className="font-semibold">Signalement ultra rapide</h6>
                                    <p>
                                        Un QR code sur chaque équipement ou local permet de créer un ticket en quelques secondes, sans login ni
                                        application à installer.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Communication directe et efficace</h6>
                                    <p>
                                        Dès qu’un ticket est soumis, le responsable reçoit un email avec toutes les informations nécessaires pour
                                        planifier une intervention.
                                    </p>
                                </div>
                            </div>
                            <div className="from-website-border rounded-md bg-linear-to-t to-transparent p-0.5">
                                <div className="grid h-full grid-rows-[1fr_2fr] gap-4 rounded-md bg-white p-6">
                                    <h6 className="font-semibold">Traçabilité des incidents</h6>
                                    <p>
                                        Tous les tickets sont liés à l’actif concerné, garantissant une vision claire de l’historique des problèmes et
                                        des actions menées.
                                    </p>
                                </div>
                            </div>
                        </div>
                        <img src="/images/Group 20.png" alt="" className="w-full" />

                        <div className="border-website-border flex w-full flex-col gap-4 rounded-md border p-6">
                            <details className="" open>
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Scan simple et universel</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Chaque QR code est associé à un équipement ou un local précis. Il suffit de le scanner avec un smartphone pour
                                    ouvrir un formulaire web clair et intuitif : description du problème, photo optionnelle et envoi immédiat.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Aucune installation requise</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Le système est pensé pour être accessible à tous — même sans compte utilisateur. Parfait pour les collaborateurs
                                    internes, visiteurs, ou prestataires qui doivent signaler un dysfonctionnement ponctuel.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Notification automatique au responsable</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Dès qu’un ticket est créé, le responsable de maintenance ou le facility manager reçoit un email détaillant la
                                    demande : description, photo, localisation, QR code d’origine. Il peut alors attribuer la tâche à un technicien
                                    interne ou à un prestataire externe.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Centralisation et suivi des tickets</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    Chaque ticket est automatiquement rattaché à l’actif ou au local correspondant. Depuis l’application, le
                                    responsable peut consulter l’historique complet : incidents passés, interventions réalisées, délais de traitement.
                                </p>
                            </details>
                            <details className="">
                                <summary className="cursor-pointer text-2xl font-bold">
                                    <h3>Gain de temps et meilleure réactivité</h3>
                                    <hr className="mt-3" />
                                </summary>
                                <p className="mt-6 text-lg">
                                    En éliminant les échanges informels (emails, appels, messages), le QR code simplifie la remontée d’information
                                    depuis le terrain. Les problèmes sont signalés plus vite, les interventions mieux planifiées et les délais de
                                    résolution réduits.
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
