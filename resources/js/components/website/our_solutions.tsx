import { Boxes, ChartLine, QrCode, ReceiptText, Settings } from 'lucide-react';

export default function OurSolutions() {
    return (
        <section className="bg-website-secondary min-h-screen py-40">
            <div className="text-website-font container mx-auto h-full space-y-10 px-4 py-10 md:max-w-2/3 md:p-10">
                <h2>Nos solutions</h2>
                <div className="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
                    <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                        <div className="flex gap-4">
                            <Boxes size={24} className="shrink-0" />
                            <h6 className="font-semibold">Inventaire de vos équipements</h6>
                        </div>
                        <p>Ayez une vue d’ensemble sur vos équipements et installations en répertoriant tout dans une seule application.</p>
                        <a href="" className="text-website-primary">
                            En savoir plus
                        </a>
                    </div>
                    <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                        <div className="flex gap-4">
                            <Settings size={24} className="shrink-0" />
                            <h6 className="font-semibold">Gestion de la maintenance et de vos interventions</h6>
                        </div>
                        <p>Planifiez et gérez vos maintenances préventives et actives ainsi que les interventions sur vos équipements.</p>
                        <a href="" className="text-website-primary">
                            En savoir plus
                        </a>
                    </div>
                    <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                        <div className="flex gap-4">
                            <ReceiptText size={24} className="shrink-0" />
                            <h6 className="font-semibold">Gestion des contrats et des garanties</h6>
                        </div>
                        <p>
                            Gérez vos contrats et les garanties pour ne plus oublier quand il faut arrêter ou changer un contrat ou étendre une
                            garantie.
                        </p>
                        <a href="" className="text-website-primary">
                            En savoir plus
                        </a>
                    </div>
                    <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                        <div className="flex gap-4">
                            <QrCode size={24} className="shrink-0" />
                            <h6 className="font-semibold">QR Code/Ticketing</h6>
                        </div>
                        <p>Soyez mis rapidement au courant lorsqu’un de vos équipements est défectueux et intervenez rapidement.</p>
                        <a href="" className="text-website-primary">
                            En savoir plus
                        </a>
                    </div>
                    <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                        <div className="flex gap-4">
                            <ChartLine size={24} className="shrink-0" />
                            <h6 className="font-semibold">Statistiques</h6>
                        </div>
                        <p>Visualisez les rapports et statistiques sur vos installations pour encore mieux planifiez.</p>
                        <a href="" className="text-website-primary">
                            En savoir plus
                        </a>
                    </div>
                    <div className="relative grid grid-cols-[2fr_1fr]">
                        <img src="../images/pexels-edmond-dantes-4342352.jpg" alt="" className="h-full w-auto object-cover" />

                        <div className="relative">
                            <div className="bg-logo text-website-secondary absolute top-6 -left-10 rounded-2xl p-2 text-sm">
                                <p className="font-semibold">SME-Facility me permet de pouvoir tout gérer dans un seul endroit.</p>
                                <p className="text-right italic">Christine, Facility Manager</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
