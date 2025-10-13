import { Settings } from 'lucide-react';

export default function OurSolutions() {
    return (
        <section className="min-h-screen py-40">
            <div className="text-website-font container mx-auto h-full space-y-10 px-4 py-10 md:max-w-2/3 md:p-10">
                <h2>Nos solutions</h2>
                <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div className="bg-website-card flex flex-col justify-between gap-4 rounded-md p-6">
                        <div className="flex gap-4">
                            <Settings size={24} />
                            <h6 className="font-semibold">Inventaire de vos équipements</h6>
                        </div>
                        <p>Centralisation des données</p>
                        <a href="" className="text-website-primary">
                            En savoir plus
                        </a>
                    </div>
                    <div className="bg-website-card flex flex-col justify-between gap-4 rounded-md p-6">
                        <div className="flex gap-4">
                            <Settings size={24} />
                            <h6 className="font-semibold">Inventaire de vos équipements</h6>
                        </div>
                        <p>Centralisation des données</p>
                        <a href="" className="text-website-primary">
                            En savoir plus
                        </a>
                    </div>
                    <div className="bg-website-card flex flex-col justify-between gap-4 rounded-md p-6">
                        <div className="flex gap-4">
                            <Settings size={24} />
                            <h6 className="font-semibold">Inventaire de vos équipements</h6>
                        </div>
                        <p>Centralisation des données</p>
                        <a href="" className="text-website-primary">
                            En savoir plus
                        </a>
                    </div>
                    <div className="bg-website-card flex flex-col justify-between gap-4 rounded-md p-6">
                        <div className="flex gap-4">
                            <Settings size={24} />
                            <h6 className="font-semibold">Inventaire de vos équipements</h6>
                        </div>
                        <p>Centralisation des données</p>
                        <a href="" className="text-website-primary">
                            En savoir plus
                        </a>
                    </div>
                    <div className="bg-website-card flex flex-col justify-between gap-4 rounded-md p-6">
                        <div className="flex gap-4">
                            <Settings size={24} />
                            <h6 className="font-semibold">Inventaire de vos équipements</h6>
                        </div>
                        <p>Centralisation des données</p>
                        <a href="" className="text-website-primary">
                            En savoir plus
                        </a>
                    </div>
                    <div className="relative grid grid-cols-[2fr_1fr]">
                        <img src="images/pexels-edmond-dantes-4342352.jpg" alt="" className="h-full" />

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
