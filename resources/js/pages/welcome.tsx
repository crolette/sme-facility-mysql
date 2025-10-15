import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { BadgeCheck, Bell, Check, Clock, Group, QrCode, Settings } from 'lucide-react';

export default function Welcome() {
    return (
        <WebsiteLayout>
            <Head>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="Solution de Facility Management pour PME | SME-Facility" />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content="Simplifiez la gestion de vos installations avec SME-Facility. Une solution cloud tout-en-un pour centraliser vos équipements, maintenances, contrats et interventions."
                />

                <meta property="og:title" content="Le système de Facility Management idéal pour les PME" />
                <meta
                    property="og:description"
                    content="SME-Facility centralise la gestion de vos équipements, maintenances et contrats dans une application web simple et complète. Gagnez du temps et optimisez vos opérations."
                />
            </Head>
            <section className="bg-logo -mt-28 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container mx-auto">
                    <div className="mx-auto grid h-full gap-10 px-4 py-20 md:max-w-11/12 md:grid-cols-[2fr_1fr] md:gap-10 md:p-10">
                        <div className="flex flex-col items-center justify-center gap-10">
                            <h1 className="">
                                Le système de gestion de facility management
                                <span className="font-extrabold"> idéal pour les PME.</span>
                            </h1>
                            <h2 className="!text-xl">Centralisez vos équipements, simplifiez votre maintenance et optimisez vos installations</h2>
                            <p className="">
                                SME-Facility centralise la gestion de vos équipements, maintenances et contrats. Un outil tout-en-un qui simplifie
                                votre quotidien, réduit vos coûts et renforce la collaboration au sein de votre entreprise.
                            </p>
                            <p className="self-start italic">Solution cloud, simple, rapide et prête à l’emploi.</p>
                            <div className="flex flex-col gap-6 md:flex-row md:gap-10">
                                <Button variant={'cta'} className="">
                                    Prendre rendez-vous pour une démo
                                </Button>
                                <Button variant={'transparent'}>Découvrir les fonctionnalités</Button>
                            </div>
                        </div>
                        <div className="mx-auto my-auto">
                            <img src="images/home/fm_sm.jpg" alt="" className="blob h-auto max-w-72 rounded-md shadow-2xl md:w-full" />
                        </div>
                    </div>
                </div>
            </section>
            <section className="flex min-h-screen items-center py-40">
                <div className="container mx-auto">
                    <div className="mx-auto h-full space-y-10 px-4 py-10 text-black md:max-w-11/12 md:p-10">
                        <h2>Gagnez du temps et facilitez votre gestion</h2>
                        <h3 className="l">Une seule plateforme pour toutes vos installations</h3>
                        <div className="grid gap-6 md:grid-cols-2">
                            <div className="flex flex-col space-y-6 md:items-end">
                                <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:w-72">
                                    <div className="flex items-center gap-4">
                                        <Clock size={16} className="shrink-0" />
                                        <h4>Gain de temps</h4>
                                    </div>
                                    <p>
                                        Gérez toutes les informations de vos équipements dans un seul outil et gardez une trace complète des
                                        interventions.
                                    </p>
                                </div>
                                <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:w-fit md:max-w-96">
                                    <div className="flex items-center gap-4">
                                        <Settings size={16} className="shrink-0" />
                                        <h4>Gestion de la maintenance</h4>
                                    </div>
                                    <p>
                                        Planifiez vos maintenances préventives et correctives, suivez vos interventions et recevez des rappels avant
                                        échéance.
                                    </p>
                                </div>
                            </div>
                            <div className="space-y-6">
                                <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:mt-20 md:max-w-96">
                                    <div className="flex items-center gap-4">
                                        <Group size={16} className="shrink-0" />
                                        <h4>Centralisation des données</h4>
                                    </div>
                                    <p>Fini les fichiers Excel et dossiers dispersés, retrouvez tout au même endroit.</p>
                                </div>
                                <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:w-72">
                                    <div className="flex items-center gap-4">
                                        <QrCode size={16} className="shrink-0" />
                                        <h4>QR Code</h4>
                                    </div>
                                    <p>Signalez un problème en un scan, le responsable est immédiatement notifié par e-mail.</p>
                                </div>
                            </div>
                        </div>
                        <div className="bg-website-primary text-website-card mx-auto flex flex-col items-center gap-4 rounded-md p-6 shadow-xl md:w-fit">
                            <p>Découvrez notre FAQ et notre vidéo de présentation</p>
                            <Button variant={'cta'}>FAQ</Button>
                        </div>
                    </div>
                </div>
            </section>
            <section className="bg-website-card flex min-h-screen items-center py-40">
                <div className="container mx-auto">
                    <div className="text-website-font flex-flex-col mx-auto h-full items-center space-y-10 px-4 py-10 md:max-w-11/12 md:p-10">
                        <h2 className="">Interventions accélérées et suivies en temps réel</h2>
                        <h3 className="">La résolution des problèmes n’a jamais été aussi fluide.</h3>
                        <div className="relative grid md:grid-cols-[2fr_1fr]">
                            <div className="relative space-y-6">
                                <div className="relative">
                                    <div className="bg-website-primary text-website-card space-y-4 rounded-md p-6">
                                        <div className="flex items-center gap-4">
                                            <QrCode />
                                            <p className="font-bold">Scan du QR Code</p>
                                        </div>
                                        <p>Tout utilisateur peut encoder un problème avec photo et description.</p>
                                    </div>
                                </div>
                                <div className="relative w-full">
                                    <img src="images/left-arrow.svg" alt="" className="left-0 hidden md:absolute md:block" />
                                    <div className="bg-website-border text-website-card w-full space-y-4 rounded-md p-6 md:ml-12">
                                        <div className="left-4 flex items-center gap-4">
                                            <Bell />
                                            <p className="font-bold">Notification automatique </p>
                                        </div>
                                        <p>Le responsable reçoit immédiatement un e-mail avec le ticket.</p>
                                    </div>
                                </div>
                                <div className="relative w-full">
                                    <img src="images/left-arrow.svg" alt="" className="hidden md:absolute md:left-8 md:block" />
                                    <div className="bg-website-secondary text-website-font w-full space-y-4 rounded-md p-6 md:ml-20">
                                        <div className="flex items-center gap-4">
                                            <Settings />
                                            <p className="font-bold">Planification rapide </p>
                                        </div>
                                        <p>Créez une intervention, assignez-la à un technicien ou à un prestataire externe.</p>
                                    </div>
                                </div>
                                <div className="relative w-full">
                                    <img src="images/left-arrow.svg" alt="" className="hidden md:absolute md:left-20 md:block" />
                                    <div className="border-website-border text-website-font w-full space-y-4 rounded-md border bg-white p-6 md:ml-32">
                                        <div className="flex items-center gap-4">
                                            <BadgeCheck />
                                            <p className="font-bold">Suivi et rapport </p>
                                        </div>
                                        <p>à la clôture, un rapport d’intervention est envoyé automatiquement</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p className="self-start italic">SME-Facility facilite chaque étape, du signalement à la résolution.</p>
                    </div>
                </div>
            </section>
            <section className="flex min-h-screen items-center py-40">
                <div className="container mx-auto">
                    <div className="text-website-font mx-auto h-full space-y-14 px-4 py-10 md:max-w-11/12 md:p-10">
                        <h2 className="">Une application simple, rapide et complète</h2>
                        <p>Pourquoi se compliquer la gestion des installations si tout peut se faire avec SME-Facility?</p>

                        <div className="from-website-primary text-website-secondary mx-auto grid grid-cols-1 gap-10 rounded-md bg-linear-to-r to-white p-10 lg:grid-cols-[2fr_1fr]">
                            <div className="">
                                <h3>
                                    Facilitez{' '}
                                    <span className="block text-lg">
                                        votre gestion au quotidien avec une interface claire et intuitive pensée pour les PME
                                    </span>
                                </h3>
                                <ul className="mt-5 ml-5 space-y-10">
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Utilisation simple, sans formation complexe
                                    </li>
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Application web fluide, accessible partout
                                    </li>
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Zéro installation, zéro paramétrage technique
                                    </li>
                                </ul>
                            </div>
                            <div className="relative flex items-center justify-center md:justify-end">
                                <img src="images/Digital tools-bro.svg" alt="" className="max-h-72 md:max-h-11/12" />
                            </div>
                        </div>
                        <div className="text-website-font to-website-secondary mx-auto grid grid-cols-1 gap-10 rounded-md bg-linear-to-r from-white p-10 lg:grid-cols-[1fr_2fr]">
                            <div className="relative order-2 flex items-center justify-center md:order-none md:justify-end">
                                <img src="images/Electrician-bro.svg" alt="" className="max-h-72 md:max-h-11/12" />
                            </div>
                            <div className="">
                                <h3>
                                    Planifiez <span className="block text-lg">vos maintenances et interventions et gardez le contrôle.</span>
                                </h3>
                                <ul className="mt-5 ml-5 space-y-10">
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Créez des maintenances préventives ou correctives
                                    </li>
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Recevez des notifications avant chaque échéance
                                    </li>
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Recalculez automatiquement la prochaine date prévue
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div className="from-logo text-website-secondary mx-auto grid grid-cols-1 gap-10 rounded-md bg-linear-to-r to-white p-10 lg:grid-cols-[2fr_1fr]">
                            <div className="">
                                <h3>
                                    Gérez{' '}
                                    <span className="block text-lg">
                                        vos contrats et garanties et anticipez vos renouvellements et restez conforme.
                                    </span>
                                </h3>
                                <ul className="mt-5 ml-5 space-y-10">
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Visualisez les contrats actifs, expirés ou annulés
                                    </li>
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Recevez une alerte avant la fin de validité
                                    </li>
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Associez contrats et garanties à vos équipements
                                    </li>
                                </ul>
                            </div>
                            <div className="relative flex items-center justify-center md:justify-end">
                                <img src="images/Office management-pana.svg" alt="" className="max-h-72 md:max-h-11/12" />
                            </div>
                        </div>
                        <div className="text-website-font to-website-card mx-auto grid grid-cols-1 gap-10 rounded-md bg-linear-to-r from-white p-10 lg:grid-cols-[1fr_2fr]">
                            <div className="relative order-2 flex items-center justify-center md:order-none md:justify-end">
                                <img src="images/Download-amico.svg" alt="" className="max-h-72 md:max-h-11/12" />
                            </div>
                            <div className="flex flex-col gap-4">
                                <h3>
                                    Centralisez{' '}
                                    <span className="block text-lg">
                                        vos documents et photos et regroupez toutes vos informations dans un espace unique
                                    </span>
                                </h3>
                                <ul className="mt-5 ml-5 space-y-10">
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Ajoutez des documents techniques, rapports et images
                                    </li>
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Accédez à chaque fichier depuis l’équipement concerné
                                    </li>
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Dites adieu aux fichiers dispersés sur plusieurs supports
                                    </li>
                                </ul>
                            </div>
                        </div>
                        <div className="from-website-border text-website-secondary mx-auto grid grid-cols-1 gap-10 rounded-md bg-linear-to-r to-white p-10 lg:grid-cols-[2fr_1fr]">
                            <div className="flex flex-col gap-4">
                                <h3>
                                    Résolvez{' '}
                                    <span className="block text-lg">
                                        les problèmes en un scan, signalez, traitez et clôturez plus vite vos incidents
                                    </span>
                                </h3>
                                <ul className="mt-5 ml-5 space-y-10">
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Un QR code par équipement ou local
                                    </li>
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Création immédiate d’un ticket avec photo
                                    </li>
                                    <li>
                                        <Check size={16} className="mr-4 inline-block" />
                                        Notification instantanée au responsable concerné
                                    </li>
                                </ul>
                            </div>
                            <div className="relative flex items-center justify-center md:justify-end">
                                <img src="images/QR Code-bro.svg" alt="" className="max-h-72 md:max-h-11/12" />
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
