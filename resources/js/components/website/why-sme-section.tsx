import { BadgeCheck, BadgeEuro, User } from 'lucide-react';

export default function WhySMESection() {
    return (
        <section className="bg-website-border py-20">
            <div className="container mx-auto">
                <div className="text-website-secondary mx-auto h-full space-y-10 px-4 py-10 md:max-w-11/12 md:p-10">
                    <h2>Pourquoi choisir SME-Facility ?</h2>
                    <h3>Une solution conçue pour les PME</h3>
                    <ul className="ml-6 flex flex-col gap-4">
                        <li className="flex gap-4">
                            <BadgeEuro className="mr-6 inline-block" />
                            <p>Coût maîtrisé : abonnement simple et abordable</p>
                        </li>
                        <li className="flex gap-4">
                            <User className="mr-6 inline-block" />
                            <p>Démarrage immédiat : aucune installation, aucun paramétrage complexe</p>
                        </li>
                        <li className="flex gap-4">
                            <BadgeCheck className="mr-6 inline-block" />
                            <p>Accompagnement humain : aide à l’import de données, conseil en Facility Management</p>
                        </li>
                        <li className="flex gap-4">
                            <BadgeEuro className="mr-6 inline-block" />
                            <p>Proximité : développée par une PME, pour les PME</p>
                        </li>
                    </ul>
                    <a href={route('website.why')} className="!text-white">
                        En savoir plus
                    </a>
                </div>
            </div>
        </section>
    );
}
