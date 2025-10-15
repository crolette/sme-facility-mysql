import { Button } from '../ui/button';

export default function FaqSection() {
    return (
        <section className="bg-[url('/images/faq1.jpg')] bg-cover bg-scroll bg-center py-20">
            <div className="container mx-auto">
                <div className="bg-website-font/80 text-website-card mx-10 flex h-full flex-col items-center space-y-6 px-4 py-10 text-center text-sm md:max-w-11/12 md:p-10">
                    <h6>Consultez notre Foire Aux Questions, nous répondons à toutes vos questions</h6>
                    <a href={route('website.faq')} className="mx-auto">
                        <Button>Consulter la FAQ</Button>
                    </a>
                </div>
            </div>
        </section>
    );
}
