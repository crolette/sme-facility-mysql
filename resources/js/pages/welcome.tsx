import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { BadgeCheck, Bell, Clock, Group, QrCode, Settings } from 'lucide-react';

export default function Welcome() {
    return (
        <WebsiteLayout>
            <section className="bg-logo -mt-28 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="max:w-full container mx-auto grid h-full gap-10 px-4 py-20 md:grid-cols-[2fr_1fr] md:gap-10 md:p-10">
                    <div className="flex flex-col items-center justify-center gap-10">
                        <h1 className="">
                            Le système de gestion de facility management
                            <span className="font-extrabold"> idéal pour les PME.</span>
                        </h1>
                        <p className="">
                            Véritable outil de Facility management, SME-Facility centralise l'information sur les équipements, simplifie votre
                            quotidien et renforce la collaboration. SME-Facility booste la productivité et la croissance de votre entreprise.
                        </p>
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
            </section>
            <section className="flex min-h-screen items-center py-40">
                <div className="container mx-auto h-full space-y-10 px-4 py-10 text-black md:max-w-2/3 md:p-10">
                    <h2>Un outil pour gérer toutes vos installations</h2>
                    <div className="grid gap-6 md:grid-cols-2">
                        <div className="flex flex-col space-y-6 md:items-end">
                            <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:w-72">
                                <div className="flex items-center gap-4">
                                    <Clock size={16} />
                                    <h3>Gain de temps</h3>
                                </div>
                                <p>
                                    Gagnez du temps en gérant toutes les informations sur vos équipements dans une seule et même application et gardez
                                    une trace de tout pour avoir une vue d’ensemble.
                                </p>
                            </div>
                            <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:w-fit md:max-w-96">
                                <div className="flex items-center gap-4">
                                    <Settings size={16} />
                                    <h3>Gestion de la maintenance</h3>
                                </div>
                                <p>Gérez la maintenance préventive et corrective facilement en planifiant vos interventions.</p>
                            </div>
                        </div>
                        <div className="space-y-6">
                            <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:mt-20 md:max-w-96">
                                <div className="flex items-center gap-4">
                                    <Group size={16} />
                                    <h3>Centralisation des données</h3>
                                </div>
                                <p>Fini la multitude de fichiers Excel, de documents éparpillés dans différents dossiers.</p>
                                <p>Retrouvez toutes les informations concernant votre équipement, à un seul et même endroit.</p>
                            </div>
                            <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:w-72">
                                <div className="flex items-center gap-4">
                                    <QrCode size={16} />
                                    <h3>QR Code</h3>
                                </div>
                                <p>
                                    Ajoutez un QR code sur vos équipements et dans vos locaux afin de vous permettre de déclarer rapidement un
                                    problème sur ceux-ci et d'être averti directement.
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="bg-website-primary text-website-card mx-auto rounded-md p-6 shadow-xl md:w-fit">
                        <p>Découvrez notre FAQ et notre vidéo de présentation</p>
                        <Button variant={'cta'} className="mx-auto">
                            FAQ
                        </Button>
                    </div>
                </div>
            </section>
            <section className="bg-website-card flex min-h-screen items-center py-40">
                <div className="text-website-font flex-flex-col container mx-auto h-full items-center space-y-10 px-4 py-10 md:max-w-2/3 md:p-10">
                    <h2>Intervention accélérée et facilitée</h2>
                    <p>La résolution d'un problème n'a jamais été ausi facile</p>
                    <div className="relative grid md:grid-cols-[2fr_1fr]">
                        <div className="relative space-y-6">
                            <div className="relative">
                                <div className="bg-website-primary text-website-card space-y-4 rounded-md p-6">
                                    <div className="flex items-center gap-4">
                                        <QrCode />
                                        <p className="">Scannez le QR Code</p>
                                    </div>
                                    <p>Encodez le problème rencontré, avec une photo et notifiez le responsable de maintenance.</p>
                                </div>
                            </div>
                            <div className="relative w-full">
                                <img src="images/left-arrow.svg" alt="" className="left-0 hidden md:absolute md:block" />
                                <div className="bg-website-border text-website-card w-full space-y-4 rounded-md p-6 md:ml-12">
                                    <div className="left-4 flex items-center gap-4">
                                        <Bell />
                                        <p className="">Recevez un mail de notification</p>
                                    </div>
                                    <p>Soyez prévenu dès qu'un nouveau dérangement sur une installation a été encodé.</p>
                                </div>
                            </div>
                            <div className="relative w-full">
                                <img src="images/left-arrow.svg" alt="" className="hidden md:absolute md:left-8 md:block" />
                                <div className="bg-website-secondary text-website-font w-full space-y-4 rounded-md p-6 md:ml-20">
                                    <div className="flex items-center gap-4">
                                        <Settings />
                                        <p className="">Traitez le problème</p>
                                    </div>
                                    <p>Réglez le problème en interne ou demandez l'intervention d'un prestataire externe.</p>
                                </div>
                            </div>
                            <div className="relative w-full">
                                <img src="images/left-arrow.svg" alt="" className="hidden md:absolute md:left-20 md:block" />
                                <div className="border-website-border text-website-font w-full space-y-4 rounded-md border bg-white p-6 md:ml-32">
                                    <div className="flex items-center gap-4">
                                        <BadgeCheck />
                                        <p className="">Problème réglé</p>
                                    </div>
                                    <p>Une fois le problème réglé, vous recevez un e-mail avec le rapport d’intervention.</p>
                                </div>
                            </div>
                        </div>
                        <div></div>
                    </div>
                </div>
            </section>
            <section className="flex min-h-screen items-center py-40">
                <div className="text-website-font container mx-auto h-full space-y-10 px-4 py-10 md:max-w-3/4 md:p-10">
                    <h2 className="">Gestion facile de vos installations</h2>
                    <p>Pourquoi se compliquer la gestion des installations si tout peut se faire avec SME-Facility?</p>

                    <div className="from-website-primary text-website-secondary mx-auto grid grid-cols-1 gap-6 rounded-md bg-linear-to-r to-white p-6 lg:grid-cols-[2fr_1fr]">
                        <div>
                            <h3>Facilitez</h3>
                            <p className="font-semibold">vous la vie avec un seul outil</p>
                            <ul className="mt-5 ml-10 list-disc space-y-10">
                                <li>Une application web intuitive et facile d’utilisation</li>
                                <li>Prise en main rapide</li>
                                <li>Gérez tout au même endroit, fini les multiples fichiers Excel</li>
                            </ul>
                        </div>
                        <div className="relative flex items-center justify-center md:justify-end">
                            <img src="images/Digital tools-bro.svg" alt="" className="max-h-72 md:max-h-11/12" />
                        </div>
                    </div>
                    <div className="text-website-font to-website-secondary mx-auto grid grid-cols-1 gap-6 rounded-md bg-linear-to-r from-white p-6 lg:grid-cols-[1fr_2fr]">
                        <div className="relative order-2 flex items-center justify-center md:order-none md:justify-end">
                            <img src="images/Electrician-bro.svg" alt="" className="max-h-72 md:max-h-11/12" />
                        </div>
                        <div>
                            <h3>Planifiez</h3>
                            <p className="font-semibold">rapidement et simplement vos maintenances et interventions</p>
                            <ul className="mt-5 ml-10 list-disc space-y-10">
                                <li>Gérez vos maintenances ou intervention sur vos installations</li>
                                <li>Recevez une notification quelques jours avant</li>
                                <li>La date de la prochaine maintenance se calcule automatiquement</li>
                            </ul>
                        </div>
                    </div>
                    <div className="from-website-primary text-website-secondary mx-auto grid grid-cols-1 gap-6 rounded-md bg-linear-to-r to-white p-6 lg:grid-cols-[2fr_1fr]">
                        <div>
                            <h3>Gérez</h3>
                            <p className="font-semibold">vos contrats et garantie afin de ne rien oublier</p>
                            <ul className="mt-5 ml-10 list-disc space-y-10">
                                <li>Fini d’oublier quand il faut renouveler ou suspendre un contrat </li>
                                <li>Enregistrez les contrats pour vos installations pour voir en un coup d’œil les échéances futures</li>
                                <li>Savoir quand la garantie se termine et décider si vous prenez une extension de garantie</li>
                            </ul>
                        </div>
                        <div className="relative flex items-center justify-center md:justify-end">
                            <img src="images/Office management-pana.svg" alt="" className="max-h-72 md:max-h-11/12" />
                        </div>
                    </div>
                    <div className="text-website-font to-website-secondary mx-auto grid grid-cols-1 gap-6 rounded-md bg-linear-to-r from-white p-6 lg:grid-cols-[1fr_2fr]">
                        <div className="relative order-2 flex items-center justify-center md:order-none md:justify-end">
                            <img src="images/Download-amico.svg" alt="" className="max-h-72 md:max-h-11/12" />
                        </div>
                        <div>
                            <h3>Centralisez</h3>
                            <p className="font-semibold">vos documents et photos</p>
                            <ul className="mt-5 ml-10 list-disc space-y-10">
                                <li>Regroupez vos documents et photos dans un seul endroit</li>
                                <li>Groupez par type de document (manuel, garantie, contrat, …)</li>
                                <li>Ajoutez des photos pour vos installations, interventions, …</li>
                            </ul>
                        </div>
                    </div>
                    <div className="from-website-primary text-website-secondary mx-auto grid grid-cols-1 gap-6 rounded-md bg-linear-to-r to-white p-6 lg:grid-cols-[2fr_1fr]">
                        <div>
                            <h3>Résolvez </h3>
                            <p className="font-semibold">les problèmes sur vos installations rapidement</p>
                            <ul className="mt-5 ml-10 list-disc space-y-10">
                                <li>Créez des QR code pour chaque emplacement ou actif</li>
                                <li>Tout le monde peut encoder un problème et vous êtes immédiatement averti par e-mail</li>
                                <li>Gérez la résolution du problème en interne ou envoyez la demande d’intervention à un prestataire externe</li>
                            </ul>
                        </div>
                        <div className="relative flex items-center justify-center md:justify-end">
                            <img src="images/QR Code-bro.svg" alt="" className="max-h-72 md:max-h-11/12" />
                        </div>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
