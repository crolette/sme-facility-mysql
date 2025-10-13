import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';

export default function FeaturesQrCode() {
    return (
        <WebsiteLayout>
            <section className="bg-website-primary -mt-20 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container mx-auto grid h-full gap-10 px-4 py-20 md:max-w-2/3 md:grid-cols-[2fr_1fr] md:gap-30 md:p-10">
                    <div className="flex flex-col items-center justify-center gap-10 md:max-w-lg">
                        <h1 className="">Inventoriez vos équipements dans un seul endroit</h1>
                        <p className="text-4xl font-semibold">Et si la gestion de vos installations devenait facile?</p>
                        <p>Centralisation des informations de tous vos équipements</p>
                        <div className="flex flex-col gap-6 md:flex-row md:gap-10">
                            <Button variant={'cta'}>Prendre rendez-vous pour une démo</Button>
                            <Button variant={'transparent'}>Découvrir les formules</Button>
                        </div>
                    </div>
                    <div className="mx-auto my-auto">
                        <img src="images/home/fm_sm.jpg" alt="" className="blob max-h-72 w-auto rounded-md shadow-2xl" />
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
