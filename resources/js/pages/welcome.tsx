import WebsiteLayout from '@/layouts/website-layout';

export default function Welcome() {

    return (
        <WebsiteLayout>
            <section className="py-20">
                <div className="mx-auto flex gap-30">
                    <div className="flex max-w-lg flex-col items-center justify-center gap-10">
                        <p className="text-4xl font-semibold">Et si la gestion de vos installations devenait facile?</p>
                        <h1 className="!text-lg">SME-Facility booste la productivité et la croissance de votre entreprise.</h1>
                        <p>
                            Véritable outil de Facility management, SME-Facility centralise l'information sur les équipements, simplifie votre
                            quotidien et renforce la collaboration{' '}
                        </p>
                    </div>
                    <img src="images/home/fm_sm.jpg" alt="" className="blob max-h-72 w-auto shadow-2xl rounded-md" />
                </div>
            </section>
            <section className="py-20">
                <div className="mx-auto flex gap-30">
                    <div className="flex max-w-lg flex-col items-center justify-center gap-10">
                        <p className="text-4xl font-semibold">Et si la gestion de vos installations devenait facile?</p>
                        <h1 className="!text-lg">SME-Facility booste la productivité et la croissance de votre entreprise.</h1>
                        <p>
                            Véritable outil de Facility management, SME-Facility centralise l'information sur les équipements, simplifie votre
                            quotidien et renforce la collaboration{' '}
                        </p>
                    </div>
                    <img src="images/home/fm_sm.jpg" alt="" className="blob h-64 w-auto shadow-2xl" />
                </div>
            </section>
        </WebsiteLayout>
    );
}
