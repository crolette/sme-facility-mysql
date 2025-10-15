import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Head } from '@inertiajs/react';
import { Building, Building2, Check, Factory, X } from 'lucide-react';

export default function Pricing() {
    return (
        <WebsiteLayout>
            <Head title={'Tarifs'}>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content="Tarification application facility management | SME-Facility" />
                <meta
                    name="description"
                    itemProp="description"
                    property="description"
                    content="Optimisez la gestion de vos équipements et la maintenance de votre PME avec SME-Facility, la solution cloud simple, rapide et prête à l’emploi."
                />

                <meta property="og:title" content="Simplifiez la gestion de vos équipements avec SME-Facility" />
                <meta
                    property="og:description"
                    content="SME-Facility aide les PME à centraliser la maintenance, suivre les contrats et automatiser les rappels. Un outil complet pour gagner du temps et booster la productivité."
                />
            </Head>
            <section className="text-website-font w-full">
                <div className="container mx-auto">
                    {/* <div className="mx-auto grid h-full gap-10 md:grid-cols-2 md:p-10 lg:max-w-11/12"></div> */}
                    <div className="mx-auto flex flex-col gap-10 p-4 md:p-10 lg:max-w-11/12">
                        <h1>Notre tarification est aussi simple que notre produit</h1>
                        <h2>Blabla</h2>
                        {/* <p>Un peu de patience... Plus d'informations très bientôt</p> */}

                        <div className="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
                            <div className="flex flex-col gap-6 rounded-md border p-6 lg:p-10">
                                <Building size={36} className="mx-auto" />
                                <h3 className="text-center">Starter</h3>
                                <div className="text-center">
                                    <p>A partir de</p>
                                    <p className={'text-2xl font-extrabold'}>149€ / mois</p>
                                </div>
                                <ul className="flex flex-col gap-6">
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>1 site</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>Assets illimités</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>Jusqu'à 5 utilisateurs</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>20 GB d'espace de stockage</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <X />
                                        <p>Gestion des prestataires</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <X />
                                        <p>Gestion des contrats</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <X />
                                        <p>Statistiques</p>
                                    </li>
                                </ul>
                                <Button variant={'cta'} className="">
                                    Démarrer aujourd'hui
                                </Button>
                            </div>
                            <div className="flex flex-col gap-6 rounded-md border p-6 lg:p-10">
                                <Building2 size={36} className="mx-auto" />
                                <h3 className="text-center">Premium</h3>
                                <div className="text-center">
                                    <p>A partir de</p>
                                    <p className={'text-2xl font-extrabold'}>299€ / mois</p>
                                </div>
                                <ul className="flex flex-col gap-6">
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>2 sites</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>Assets illimités</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>Jusqu'à 15 utilisateurs</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>50 GB d'espace de stockage</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>Gestion des prestataires</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>Gestion des contrats</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>Statistiques</p>
                                    </li>
                                </ul>
                                <Button variant={'cta'} className="">
                                    Démarrer aujourd'hui
                                </Button>
                            </div>
                            <div className="flex flex-col gap-6 rounded-md border p-6 sm:col-span-2 lg:col-auto lg:p-10">
                                <Factory size={36} className="mx-auto" />
                                <h3 className="text-center">Entreprise</h3>
                                <div className="text-center">
                                    <p>Offre</p>
                                    <p className={'text-2xl font-extrabold'}>sur demande</p>
                                </div>
                                <ul className="flex flex-col gap-6">
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>Vous devez gérer plus de 2 sites ?</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>Vous avez besoin de plus d'espace de stockage ?</p>
                                    </li>
                                    <li className="flex gap-4">
                                        <Check />
                                        <p>Support personnalisé</p>
                                    </li>
                                </ul>
                                <Button variant={'cta'} className="">
                                    Discutons-en
                                </Button>
                            </div>
                        </div>
                        {/* <div className="mx-auto flex flex-col items-center gap-4">
                            <p>Vous n’êtes pas encore convaincu que SME-Facility soit fait pour vous ?</p>
                            <p>
                                Pas de problème, nous pouvons vous proposer une démo afin de vous montrer la facilité d’utilisation de notre outil.
                                Prenez rendez-vous avec nous afin de convenir d’un rendez-vous.
                            </p>
                            <a href={route('website.contact')}>
                                <Button variant={'cta'}>Prendre rendez-vous pour une démo</Button>
                            </a>
                        </div> */}
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
