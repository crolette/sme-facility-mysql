import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import WebsiteLayout from '@/layouts/website-layout';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { BadgeCheck, Loader } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

export default function Demo() {
    const { t } = useLaravelReactI18n();

    const { data, setData, reset } = useForm({
        honey: '',
        email: '',
        company: '',
        phone_number: '',
        subject: 'appointment',
        message: '',
        consent: false,
        // 'g-recaptcha-response': '',
    });

    const [emailSent, setEmailSent] = useState<boolean>(false);
    const [isProcessing, setIsProcessing] = useState<boolean>(false);
    const [errors, setErrors] = useState(null);

    const handleForm: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);
        try {
            const response = await axios.post(route('website.demo.post'), data);
            if (response.data.status === 'success') {
                setEmailSent(true);
                reset();
            }
        } catch (error) {
            setErrors(error.response.data.errors);
        } finally {
            setIsProcessing(false);
        }
    };

    return (
        <WebsiteLayout>
            <Head title={t('website_demo.meta_title')}>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content={t('website_demo.meta_title') + ' | ' + import.meta.env.VITE_APP_NAME} />
                <meta name="description" itemProp="description" property="description" content={t('website_demo.meta_description')} />

                <meta property="og:title" content={t('website_demo.meta_title_og')} />
                <meta property="og:description" content={t('website_demo.meta_description_og')} />
            </Head>

            {!emailSent && (
                <section className="bg-logo text-website-card -mt-28 flex min-h-screen w-full flex-col items-center justify-center py-20 md:-mt-38">
                    <div className="container mx-auto">
                        <div className="mx-auto grid h-full gap-10 px-4 py-20 md:grid-cols-[1fr_1fr] md:px-10 md:py-16 lg:max-w-11/12">
                            <div className="space-y-10">
                                <h1>{t('website_demo.title')}</h1>
                                <p className="leading-8">{t('website_demo.description')}</p>
                            </div>

                            <div className="container mx-auto">
                                <div className="mx-auto flex flex-col gap-10 p-4 md:p-10 lg:max-w-11/12">
                                    <form onSubmit={handleForm} className="space-y-4">
                                        <input
                                            type="text"
                                            name="honey"
                                            style={{ display: 'none' }}
                                            onChange={(e) => setData('honey', e.target.value)}
                                            tabIndex={-1}
                                            autoComplete="off"
                                        />

                                        <div className="w-full">
                                            <Label htmlFor={'email'}>{t('common.email')}</Label>
                                            <Input
                                                type="text"
                                                id="email"
                                                required
                                                className="text-logo"
                                                placeholder={t('common.email_placeholder')}
                                                onChange={(e) => setData('email', e.target.value)}
                                            />
                                            <InputError message={errors?.email} />
                                        </div>

                                        <div className="w-full">
                                            <Label htmlFor={'phone_number'}>{t('common.phone')}</Label>
                                            <Input
                                                type="text"
                                                id="phone_number"
                                                maxLength={16}
                                                className="text-logo"
                                                placeholder={t('common.phone_placeholder')}
                                                onChange={(e) => setData('phone_number', e.target.value)}
                                            />
                                            <InputError message={errors?.phone_number} />
                                        </div>
                                        <div className="w-full">
                                            <Label htmlFor={'company'}>{t('providers.company_name')}</Label>
                                            <Input
                                                type="text"
                                                id="company"
                                                minLength={3}
                                                maxLength={100}
                                                required
                                                className="text-logo"
                                                placeholder={t('providers.company_name_placeholder')}
                                                onChange={(e) => setData('company', e.target.value)}
                                            />
                                            <InputError message={errors?.company} />
                                        </div>

                                        <div>
                                            <Label htmlFor={'message'}>{t('website_contact.message')}</Label>
                                            <Textarea
                                                id="message"
                                                minLength={50}
                                                maxLength={500}
                                                className="text-logo"
                                                required
                                                placeholder={t('website_demo.message_placeholder')}
                                                onChange={(e) => setData('message', e.target.value)}
                                            />
                                            <InputError message={errors?.message} />
                                        </div>
                                        <div className="flex items-center gap-2 text-xs">
                                            <Checkbox
                                                id="consent"
                                                required
                                                checked={data.consent}
                                                onClick={() => {
                                                    setData('consent', !data.consent);
                                                }}
                                            />
                                            <label htmlFor="consent">
                                                {t('website_contact.newsletter.consent_description')}
                                                <a href={route('website.confidentiality')}>{t('website_common.footer.confidentiality')}.</a>
                                            </label>
                                        </div>

                                        <Button disabled={isProcessing} className="bg-cta mt-2">
                                            {isProcessing ? (
                                                <span className="flex animate-pulse items-center gap-2">
                                                    <Loader />
                                                    {t('actions.processing')}
                                                </span>
                                            ) : (
                                                t('actions.send')
                                            )}
                                        </Button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            )}
            {emailSent && (
                <section className="contact-success bg-logo text-website-card -mt-28 flex min-h-screen w-full flex-col items-center justify-center py-20 md:-mt-38">
                    <div className="mx-auto flex flex-col gap-10 p-4 text-center md:p-10 lg:max-w-1/2">
                        <div className="flex flex-col items-center gap-4">
                            <BadgeCheck size={48} className="" />
                            <p className="mx-auto text-3xl font-bold">{t('common.thank_you')}</p>
                            <p className="mx-auto">{t('website_demo.thank_you_message')}</p>
                            <div className="mx-auto flex gap-4"></div>
                        </div>
                    </div>
                </section>
            )}
        </WebsiteLayout>
    );
}
