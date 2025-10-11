import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { BadgeCheck, Bell, Clock, Group, QrCode, Settings } from 'lucide-react';

export default function Welcome() {
    return (
        <WebsiteLayout>
            <section className="bg-logo -mt-20 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container mx-auto grid h-full gap-10 px-4 py-20 md:grid-cols-[2fr_1fr] md:gap-30 md:p-10">
                    <div className="flex flex-col items-center justify-center gap-10 md:max-w-lg">
                        <p className="text-4xl font-semibold">Et si la gestion de vos installations devenait facile?</p>
                        <h1 className="!text-lg">SME-Facility booste la productivité et la croissance de votre entreprise.</h1>
                        <p>
                            Véritable outil de Facility management, SME-Facility centralise l'information sur les équipements, simplifie votre
                            quotidien et renforce la collaboration.
                        </p>
                        <div className="flex flex-col gap-6 md:flex-row md:gap-10">
                            <Button variant={'cta'}>Prendre rendez-vous pour une démo</Button>
                            <Button variant={'transparent'}>Découvrir les fonctionnalités</Button>
                        </div>
                    </div>
                    <div className="mx-auto my-auto">
                        <img src="images/home/fm_sm.jpg" alt="" className="blob max-h-72 w-auto rounded-md shadow-2xl" />
                    </div>
                </div>
            </section>
            <section className="min-h-screen py-40">
                <div className="container mx-auto h-full space-y-10 px-4 py-10 text-black md:p-10">
                    <h2>Un outil pour gérer toutes vos installations</h2>
                    <div className="grid gap-6 md:grid-cols-2">
                        <div className="flex flex-col space-y-6 md:items-end">
                            <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:w-72">
                                <div className="flex items-center gap-4">
                                    <Clock size={16} />
                                    <h4>Gain de temps</h4>
                                </div>
                                <p>Gain de temps</p>
                            </div>
                            <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:w-fit md:max-w-96">
                                <div className="flex items-center gap-4">
                                    <Settings size={16} />
                                    <h4>Gestion de la maintenance</h4>
                                </div>
                                <p>Gain de temps</p>
                            </div>
                        </div>
                        <div className="space-y-6">
                            <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:mt-20 md:max-w-96">
                                <div className="flex items-center gap-4">
                                    <Group size={16} />
                                    <h4>Centralisation des données</h4>
                                </div>
                                <p>Fini la multitude de fichiers Excel, de documents éparpillés dans différents dossiers.</p>
                                <p>Retrouvez toutes les informations concernant votre équipement, à un seul et même endroit.</p>
                            </div>
                            <div className="card bg-website-secondary flex flex-col rounded-md p-6 shadow-xl md:w-72">
                                <div className="flex items-center gap-4">
                                    <QrCode size={16} />
                                    <h4>QR Code</h4>
                                </div>
                                <p>Gain de temps</p>
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
            <section className="bg-website-card min-h-screen py-40">
                <div className="text-website-font container mx-auto h-full space-y-10 px-4 py-10 md:p-10">
                    <h2>La résolution d'un problème n'a jamais été ausi facile</h2>
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
                                    <p>Gain de temps</p>
                                </div>
                            </div>
                        </div>
                        <div></div>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
