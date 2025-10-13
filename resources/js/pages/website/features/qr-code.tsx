import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';

export default function FeaturesQrCode() {
    return (
        <WebsiteLayout>
            <section className="bg-website-primary -mt-20 flex min-h-screen w-full items-center justify-center py-20 md:-mt-40">
                <div className="container mx-auto grid h-full gap-10 px-4 py-20 md:max-w-2/3 md:grid-cols-[2fr_1fr] md:gap-30 md:p-10">
                    <div className="flex flex-col items-center justify-center gap-10 md:max-w-lg">
                        <h1 className="">
                            Inventoriez vos équipements dans <span className="font-extrabold"> un seul endroit </span>
                        </h1>
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
            <section className="flex min-h-screen w-full flex-col items-center justify-center text-black">
                <div className="container mx-auto flex h-full flex-col gap-10 px-4 py-20 md:max-w-2/3 md:gap-30 md:p-10">
                    <div className="grid gap-6 md:grid-cols-3">
                        <div className="grid grid-rows-2 rounded-md bg-yellow-100 p-6">
                            <h6 className="font-semibold">Fini la multitude de fichiers</h6>
                            <p>Centralisez toutes les informations concernant vos équipements dans un seul endroit.</p>
                        </div>
                        <div className="grid grid-rows-2 rounded-md bg-yellow-100 p-6">
                            <h6 className="font-semibold">Fini la multitude de fichiers</h6>
                            <p>Centralisez toutes les informations concernant vos équipements dans un seul endroit.</p>
                        </div>
                        <div className="grid grid-rows-2 rounded-md bg-yellow-100 p-6">
                            <h6 className="font-semibold">Fini la multitude de fichiers</h6>
                            <p>Centralisez toutes les informations concernant vos équipements dans un seul endroit.</p>
                        </div>
                    </div>
                    <img src="../images/Group 20.png" alt="" className="w-full" />

                    <div className="border-website-border w-full rounded-md border p-6">
                        <details className="" open>
                            <summary className="text-2xl font-bold">
                                Gérez vos tickets
                                <hr className="mt-3" />
                            </summary>
                            <p className="mt-3 text-lg">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed viverra, purus eget ullamcorper ullamcorper, tellus magna
                                interdum magna, et lacinia nisl purus vel dui. Nullam vel pulvinar diam, vitae aliquam nisi. Aliquam id arcu nec diam
                                bibendum malesuada vel nec purus. Nunc semper, mi quis porttitor euismod, enim justo dictum felis, at elementum arcu
                                odio id tellus. Donec molestie lacinia egestas. Quisque in odio et turpis iaculis egestas. Vivamus imperdiet
                                vestibulum mauris, ac accumsan dui volutpat id. Sed vitae nibh ligula.
                            </p>
                        </details>
                        <details className="">
                            <summary className="text-2xl font-bold">
                                Gérez vos tickets
                                <hr className="mt-3" />
                            </summary>
                            <p className="mt-3 text-lg">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed viverra, purus eget ullamcorper ullamcorper, tellus magna
                                interdum magna, et lacinia nisl purus vel dui. Nullam vel pulvinar diam, vitae aliquam nisi. Aliquam id arcu nec diam
                                bibendum malesuada vel nec purus. Nunc semper, mi quis porttitor euismod, enim justo dictum felis, at elementum arcu
                                odio id tellus. Donec molestie lacinia egestas. Quisque in odio et turpis iaculis egestas. Vivamus imperdiet
                                vestibulum mauris, ac accumsan dui volutpat id. Sed vitae nibh ligula.
                            </p>
                        </details>
                        <details className="">
                            <summary className="text-2xl font-bold">
                                Gérez vos tickets
                                <hr className="mt-3" />
                            </summary>
                            <p className="mt-3 text-lg">
                                Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed viverra, purus eget ullamcorper ullamcorper, tellus magna
                                interdum magna, et lacinia nisl purus vel dui. Nullam vel pulvinar diam, vitae aliquam nisi. Aliquam id arcu nec diam
                                bibendum malesuada vel nec purus. Nunc semper, mi quis porttitor euismod, enim justo dictum felis, at elementum arcu
                                odio id tellus. Donec molestie lacinia egestas. Quisque in odio et turpis iaculis egestas. Vivamus imperdiet
                                vestibulum mauris, ac accumsan dui volutpat id. Sed vitae nibh ligula.
                            </p>
                        </details>
                    </div>
                </div>
            </section>
        </WebsiteLayout>
    );
}
