import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import WebsiteLayout from '@/layouts/website-layout';
import { cn } from '@/lib/utils';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { BadgeCheck, Loader } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

export default function Contact({ reasons }: { reasons: string[] }) {
    const { t } = useLaravelReactI18n();

    const { data, setData, reset } = useForm({
        honey: '',
        first_name: '',
        last_name: '',
        email: '',
        company: '',
        phone_number: '',
        vat_number: '',
        website: '',
        subject: 'other',
        consent: false,
        message: '',
        // 'g-recaptcha-response': '',
    });

    const [emailSent, setEmailSent] = useState<boolean>(false);
    const [isProcessing, setIsProcessing] = useState<boolean>(false);
    const [errors, setErrors] = useState(null);

    const handleForm: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);
        try {
            const response = await axios.post(route('website.contact.post'), data);
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
            <Head title={t('website_contact.meta-title')}>
                <meta name="robots" content="index, follow"></meta>
                <meta property="title" content={t('website_contact.meta-title') + ' | ' + import.meta.env.VITE_APP_NAME} />
                <meta name="description" itemProp="description" property="description" content={t('website_contact.meta-description')} />

                <meta property="og:title" content={t('website_contact.meta-title-og')} />
                <meta property="og:description" content={t('website_contact.meta-description-og')} />
            </Head>

            {!emailSent && (
                <section className="text-website-font w-full">
                    <div className="container mx-auto">
                        <div className="mx-auto flex flex-col gap-10 p-4 md:p-10 lg:max-w-11/12">
                            <h1>{t('website_contact.title')}</h1>
                            <p>{t('website_contact.description')}</p>
                            <form onSubmit={handleForm} className="space-y-4">
                                <input
                                    type="text"
                                    name="honey"
                                    style={{ display: 'none' }}
                                    onChange={(e) => setData('honey', e.target.value)}
                                    tabIndex={-1}
                                    autoComplete="off"
                                />
                                <div className="flex w-full gap-4">
                                    <div className="w-full">
                                        <Label htmlFor={'first_name'}>{t('common.first_name')}</Label>
                                        <Input
                                            type="text"
                                            id="first_name"
                                            minLength={3}
                                            maxLength={100}
                                            required
                                            placeholder={t('common.first_name_placeholder')}
                                            onChange={(e) => setData('first_name', e.target.value)}
                                        />
                                        <InputError message={errors?.first_name} />
                                    </div>
                                    <div className="w-full">
                                        <Label htmlFor={'last_name'}>{t('common.last_name')}</Label>
                                        <Input
                                            type="text"
                                            id="last_name"
                                            minLength={3}
                                            maxLength={100}
                                            required
                                            placeholder={t('common.last_name_placeholder')}
                                            onChange={(e) => setData('last_name', e.target.value)}
                                        />
                                        <InputError message={errors?.last_name} />
                                    </div>
                                </div>
                                <div className="flex w-full gap-4">
                                    <div className="w-full">
                                        <Label htmlFor={'email'}>{t('common.email')}</Label>
                                        <Input
                                            type="email"
                                            id="email"
                                            required
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
                                            required
                                            placeholder={t('common.phone_placeholder')}
                                            onChange={(e) => setData('phone_number', e.target.value)}
                                        />
                                        <InputError message={errors?.phone_number} />
                                    </div>
                                </div>
                                <div className="flex w-full gap-4">
                                    <div className="w-full">
                                        <Label htmlFor={'company'}>{t('providers.company_name')}</Label>
                                        <Input
                                            type="text"
                                            id="company"
                                            minLength={3}
                                            maxLength={100}
                                            required
                                            placeholder={t('providers.company_name_placeholder')}
                                            onChange={(e) => setData('company', e.target.value)}
                                        />
                                        <InputError message={errors?.company} />
                                    </div>

                                    <div className="w-full">
                                        <Label htmlFor={'vat_number'}>{t('providers.vat_number')}</Label>
                                        <Input
                                            type="text"
                                            id="vat_number"
                                            maxLength={14}
                                            placeholder={t('providers.vat_number_placeholder')}
                                            onChange={(e) => setData('vat_number', e.target.value)}
                                        />
                                        <InputError message={errors?.vat_number} />
                                    </div>
                                </div>
                                <div>
                                    <Label htmlFor={'website'}>{t('providers.website')}</Label>
                                    <Input
                                        type="text"
                                        id="website"
                                        placeholder={t('providers.website_placeholder')}
                                        onChange={(e) => setData('website', e.target.value)}
                                    />
                                    <InputError message={errors?.website} />
                                </div>
                                <div>
                                    <Label htmlFor={'subject'}>{t('website_contact.subject')}</Label>
                                    <select
                                        name="subject"
                                        id="subject"
                                        required
                                        value={data.subject}
                                        onChange={(e) => setData('subject', e.target.value)}
                                        className={cn(
                                            'border-input placeholder:text-muted-foreground flex h-9 w-full min-w-0 rounded-md border bg-transparent px-3 py-1 text-base shadow-xs transition-[color,box-shadow] outline-none disabled:cursor-not-allowed disabled:opacity-50 md:text-sm',
                                            'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                                            'aria-invalid:ring-destructive/20 dark:aria-invalid:ring-destructive/40 aria-invalid:border-destructive',
                                        )}
                                    >
                                        {reasons.map((reason) => (
                                            <option value={reason}>{t(`website_contact.reason.${reason}`)}</option>
                                        ))}
                                    </select>
                                    <InputError message={errors?.subject} />
                                </div>

                                <div>
                                    <Label htmlFor={'message'}>{t('website_contact.message')}</Label>
                                    <Textarea
                                        id="message"
                                        minLength={50}
                                        maxLength={500}
                                        required
                                        placeholder={t('website_contact.message_placeholder')}
                                        onChange={(e) => setData('message', e.target.value)}
                                    />
                                    <InputError message={errors?.message} />
                                </div>
                                <div className="text-logo flex items-center gap-2 text-xs">
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
                </section>
            )}
            {emailSent && (
                <section className="contact-success container mx-auto p-24 text-center">
                    <div className="mx-auto flex flex-col gap-10 p-4 text-center md:p-10 lg:max-w-1/2">
                        <div className="flex flex-col items-center gap-4">
                            <BadgeCheck size={48} className="" />
                            <p className="mx-auto text-3xl font-bold">{t('common.thank_you')}</p>
                            <p className="mx-auto">{t('website_contact.thank_you_message')}</p>
                            <div className="mx-auto flex gap-4"></div>
                        </div>
                    </div>
                </section>
            )}
        </WebsiteLayout>
    );
}
