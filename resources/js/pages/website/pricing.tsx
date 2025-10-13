import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Check } from 'lucide-react';

export default function Pricing() {
    return (
        <WebsiteLayout>
            <section className="text-website-font w-full">
                <div className="container mx-auto md:max-w-2/3 md:p-10">
                    <h1>Notre tarification est aussi simple que notre produit</h1>
                    <div className="grid grid-cols-3 gap-10">
                        <div className="flex flex-col gap-6 rounded-md border p-10">
                            <h2 className="text-center">Starter</h2>
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
                                    <p>QR codes illimités</p>
                                </li>
                                <li className="flex gap-4">
                                    <Check />
                                    <p>20 GB d'espace de stockage</p>
                                </li>
                            </ul>
                            <Button variant={'cta'} className="">
                                Démarrer aujourd'hui
                            </Button>
                        </div>
                        <div className="flex flex-col gap-6 rounded-md border p-10">
                            <h2 className="text-center">Premium</h2>
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
                                    <p>QR codes illimités</p>
                                </li>
                                <li className="flex gap-4">
                                    <Check />
                                    <p>50 GB d'espace de stockage</p>
                                </li>
                            </ul>
                            <Button variant={'cta'} className="">
                                Démarrer aujourd'hui
                            </Button>
                        </div>
                        <div className="flex flex-col gap-6 rounded-md border p-10">
                            <h2 className="text-center">Entreprise</h2>
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
                </div>
            </section>
            <section className="text-website-font w-full">
                <div className="container mx-auto grid h-full gap-10 px-4 py-20 md:max-w-2/3">
                    <h2 className="mx-auto">Foire Aux Questions</h2>
                    <div className="website-details mx-auto flex w-full flex-col rounded-md border">
                        <div className="rounded-md">
                            <details className="bg-website-card rounded-md">
                                <summary className="border-b px-6 py-4">Combien de temps faut-il pour démarrer ?</summary>
                                <p className="bg-white p-6">
                                    Véritable outil de Facility management, SME-Facility centralise l'information sur les équipements, simplifie votre
                                    quotidien et renforce la collaboration. SME-Facility booste la productivité et la croissance de votre entreprise.
                                </p>
                            </details>
                        </div>
                        <div className="rounded-md">
                            <details className="bg-website-card rounded-md">
                                <summary className="border-b px-6 py-4">Combien de temps faut-il pour démarrer ?</summary>
                                <p className="bg-white p-6">
                                    Véritable outil de Facility management, SME-Facility centralise l'information sur les équipements, simplifie votre
                                    quotidien et renforce la collaboration. SME-Facility booste la productivité et la croissance de votre entreprise.
                                </p>
                            </details>
                        </div>
                        <div className="rounded-md">
                            <details className="bg-website-card rounded-md">
                                <summary className="border-b px-6 py-4">Combien de temps faut-il pour démarrer ?</summary>
                                <p className="bg-white p-6">
                                    Véritable outil de Facility management, SME-Facility centralise l'information sur les équipements, simplifie votre
                                    quotidien et renforce la collaboration. SME-Facility booste la productivité et la croissance de votre entreprise.
                                </p>
                            </details>
                        </div>
                        <div className="bg-website-card rounded-md">
                            <details className="">
                                <summary className="px-6 py-4">Combien de temps faut-il pour démarrer ?</summary>
                                <p className="rounded-md bg-white p-6">
                                    Véritable outil de Facility management, SME-Facility centralise l'information sur les équipements, simplifie votre
                                    quotidien et renforce la collaboration. SME-Facility booste la productivité et la croissance de votre entreprise.
                                </p>
                            </details>
                        </div>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
