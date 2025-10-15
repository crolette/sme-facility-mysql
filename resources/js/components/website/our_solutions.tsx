import { Boxes, ChartLine, FileStack, QrCode, ReceiptText, Settings } from 'lucide-react';

export default function OurSolutions() {
    return (
        <section className="bg-website-secondary min-h-screen py-20">
            <div className="container mx-auto">
                <div className="text-website-font mx-auto h-full space-y-10 px-4 py-10 text-sm md:max-w-11/12 md:p-10">
                    <h2>Nos solutions</h2>
                    <h3>Découvrez tout ce que SME-Facility peut faire pour vous </h3>
                    <div className="grid grid-cols-1 gap-10 sm:grid-cols-2 lg:grid-cols-3">
                        <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                            <div className="flex gap-4">
                                <Boxes size={24} className="shrink-0" />
                                <h6 className="font-semibold">Inventaire de vos équipements</h6>
                            </div>
                            <p>Une vue claire et structurée de votre parc technique.</p>
                            <a href={route('website.features.assets')} className="text-website-primary">
                                En savoir plus
                            </a>
                        </div>
                        <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                            <div className="flex gap-4">
                                <Settings size={24} className="shrink-0" />
                                <h6 className="font-semibold">Maintenance et interventions </h6>
                            </div>
                            <p>Planifiez, suivez et analysez vos opérations.</p>
                            <a href={route('website.features.maintenance')} className="text-website-primary">
                                En savoir plus
                            </a>
                        </div>
                        <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                            <div className="flex gap-4">
                                <ReceiptText size={24} className="shrink-0" />
                                <h6 className="font-semibold">Contrats et garanties </h6>
                            </div>
                            <p>Anticipez vos échéances et maîtrisez vos engagements.</p>
                            <a href="" className="text-website-primary">
                                En savoir plus
                            </a>
                        </div>
                        <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                            <div className="flex gap-4">
                                <QrCode size={24} className="shrink-0" />
                                <h6 className="font-semibold">QR Code & ticketing </h6>
                            </div>
                            <p>Signalez et suivez les problèmes en un scan.</p>
                            <a href={route('website.features.qrcode')} className="text-website-primary">
                                En savoir plus
                            </a>
                        </div>
                        <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                            <div className="flex gap-4">
                                <FileStack size={24} className="shrink-0" />
                                <h6 className="font-semibold">Centralisation des documents </h6>
                            </div>
                            <p>Regroupez tous vos fichiers techniques et rapports.</p>
                            <a href={route('website.features.documents')} className="text-website-primary">
                                En savoir plus
                            </a>
                        </div>
                        <div className="bg-website-card border-website-border flex flex-col justify-between gap-4 rounded-md border p-6">
                            <div className="flex gap-4">
                                <ChartLine size={24} className="shrink-0" />
                                <h6 className="font-semibold">Statistiques </h6>
                            </div>
                            <p>Analysez vos données et améliorez vos performances.</p>
                            <a href={route('website.features.statistics')} className="text-website-primary">
                                En savoir plus
                            </a>
                        </div>
                        <div className="relative grid grid-cols-[2fr_1fr] sm:col-span-2 lg:col-span-3">
                            <img src="/images/pexels-edmond-dantes-4347366.jpg" alt="" className="h-full w-auto object-cover" />

                            <div className="relative">
                                <div className="bg-logo text-website-secondary absolute top-6 -left-10 rounded-2xl p-2 text-sm">
                                    <p className="font-semibold">SME-Facility me permet de pouvoir tout gérer dans un seul endroit.</p>
                                    <p className="text-right italic">Christine, Facility Manager</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <p className="text-website-font text-center text-xl italic">Une solution unique, pensée pour les PME.</p>
            </div>
        </section>
    );
}
