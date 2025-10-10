export default function Footer() {

    return (
        <footer className="bg-logo flex flex-col items-center justify-center p-20 text-white space-y-10">
            <div className="container grid grid-cols-4">
                <div>
                    <img src="images/logo.png" alt="" className="w-40" />
                    <p>Le système de gestion de facility management idéal pour les PME</p>
                </div>
                <div>Gérer vos installations</div>
                <div>Pour qui ?</div>
                <div>Avec SME-Facility</div>
            </div>
            <div className="flex justify-between w-full text-border">
                <p>© SME-Facility 2025. SME-Facility est un service de Facility Web Experience srl</p>
                <ul className="flex">
                    <li>
                        CGU
                    </li>
                    |
                    <li>
                        CGV
                    </li>
                    |
                    <li>
                        Mentions légales
                    </li>
                </ul>
            </div>
        </footer>
    );
}