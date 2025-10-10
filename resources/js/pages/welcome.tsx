import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Clock, Cog, Group, QrCode } from 'lucide-react';

export default function Welcome() {

    return (
        <WebsiteLayout>
            <section className="bg-logo -mt-40 flex min-h-screen w-full items-center justify-center py-20">
                <div className="container mx-auto grid h-full grid-cols-2 gap-30 p-10">
                    <div className="flex max-w-lg flex-col items-center justify-center gap-10">
                        <p className="text-4xl font-semibold">Et si la gestion de vos installations devenait facile?</p>
                        <h1 className="!text-lg">SME-Facility booste la productivité et la croissance de votre entreprise.</h1>
                        <p>
                            Véritable outil de Facility management, SME-Facility centralise l'information sur les équipements, simplifie votre
                            quotidien et renforce la collaboration.
                        </p>
                        <div className="flex gap-10">
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
                <div className="container mx-auto h-full space-y-10 p-10 text-black">
                    <h2>Un outil pour gérer toutes vos installations</h2>
                    <div className="grid grid-cols-2 gap-6">
                        <div className="flex flex-col items-end space-y-6">
                            <div className="card bg-website-secondary flex w-72 flex-col rounded-md p-6">
                                <div className="flex items-center gap-4">
                                    <Clock size={16} />
                                    <h4>Gain de temps</h4>
                                </div>
                                <p>Gain de temps</p>
                            </div>
                            <div className="card bg-website-secondary flex w-fit max-w-96 flex-col rounded-md p-6">
                                <div className="flex items-center gap-4">
                                    <Cog size={16} />
                                    <h4>Gestion de la maintenance</h4>
                                </div>
                                <p>Gain de temps</p>
                            </div>
                        </div>
                        <div className="space-y-6">
                            <div className="card bg-website-secondary mt-20 flex max-w-96 flex-col rounded-md p-6">
                                <div className="flex items-center gap-4">
                                    <Group size={16} />
                                    <h4>Centralisation des données</h4>
                                </div>
                                <p>Fini la multitude de fichiers Excel, de documents éparpillés dans différents dossiers.</p>
                                <p>Retrouvez toutes les informations concernant votre équipement, à un seul et même endroit.</p>
                            </div>
                            <div className="card bg-website-secondary flex w-72 flex-col rounded-md p-6">
                                <div className="flex items-center gap-4">
                                    <QrCode size={16} />
                                    <h4>QR Code</h4>
                                </div>
                                <p>Gain de temps</p>
                            </div>
                        </div>
                    </div>
                    <div className='bg-website-primary p-6 mx-auto w-fit rounded-md'>
                        <p>
                        Découvrez notre FAQ et notre vidéo de présentation
                        </p>
                        <Button variant={'cta'}>FAQ</Button>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
