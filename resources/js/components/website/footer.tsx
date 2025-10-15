import { Linkedin, Youtube } from 'lucide-react';
import OurSolutions from './our_solutions';
import WhySMESection from './why-sme-section';

export default function Footer() {
    return (
        <>
            <OurSolutions />
            <WhySMESection />

            <footer className="bg-logo flex flex-col items-center justify-center space-y-10 px-4 py-10 text-white md:p-20">
                <div className="container grid gap-12 md:grid-cols-4">
                    <div className="gap flex flex-col gap-10">
                        <img src="images/logo.png" alt="" className="w-40" />
                        <p>Le système de gestion de facility management idéal pour les PME</p>
                        <div className="flex gap-4">
                            <Linkedin></Linkedin>
                            <Youtube></Youtube>
                        </div>
                    </div>
                    <div className="flex flex-col gap-6">
                        <h6>Gérer vos installations</h6>
                        <ul className="text-website-border text-md flex flex-col gap-4">
                            <li>
                                <a href={route('website.features.qrcode')} className="!no-underline">
                                    QR Code
                                </a>
                            </li>
                            <li>
                                <a href={route('website.features.maintenance')} className="!no-underline">
                                    Maintenance
                                </a>
                            </li>
                            <li>
                                <a href={route('website.features.contracts')} className="!no-underline">
                                    Contrats
                                </a>
                            </li>
                            <li>
                                <a href={route('website.features.documents')} className="!no-underline">
                                    Documents
                                </a>
                            </li>
                            <li>
                                <a href={route('website.features.assets')} className="!no-underline">
                                    Inventaire
                                </a>
                            </li>
                            <li>
                                <a href={route('website.features.statistics')} className="!no-underline">
                                    Statistiques
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div className="flex flex-col gap-6">
                        <h6>Pour qui ?</h6>
                        <ul className="text-website-border text-md flex flex-col gap-4">
                            <li>
                                <a href={route('website.who.facility-manager')} className="!no-underline">
                                    Facility Manager
                                </a>
                            </li>
                            <li>
                                <a href={route('website.who.maintenance-manager')} className="!no-underline">
                                    Responsable de maintenance
                                </a>
                            </li>
                            <li>
                                <a href={route('website.who.sme')} className="!no-underline">
                                    PME
                                </a>
                            </li>
                        </ul>
                    </div>
                    <div className="flex flex-col gap-6">
                        <h6>Avec SME-Facility</h6>
                        <ul className="text-website-border text-md flex flex-col gap-4">
                            <li>
                                <a href={route('website.why')} className="!no-underline">
                                    Qui somme-nous ?
                                </a>
                            </li>
                            <li>FAQ</li>
                            <li>Implémentation</li>
                            <li>Recrutement</li>
                            <li>Contact</li>
                        </ul>
                    </div>
                </div>
                <div className="text-website-border flex w-full flex-col justify-between gap-4 md:flex-row">
                    <p>© SME-Facility 2025. SME-Facility est un service de Facility Web Experience srl</p>
                    <ul className="flex flex-col md:flex-row">
                        <li>CGU</li>
                        <span className="hidden md:inline-block">|</span>
                        <li>CGV</li>
                        <span className="hidden md:inline-block">|</span>
                        <li>Mentions légales</li>
                    </ul>
                </div>
            </footer>
        </>
    );
}
