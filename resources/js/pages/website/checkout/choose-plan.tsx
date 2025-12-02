import { Button } from '@/components/ui/button';
import WebsiteLayout from '@/layouts/website-layout';
import { Tenant } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { FormEventHandler } from 'react';

interface FormDataProps {
    user: number;
    vat_number: string;
    product: null | string;
    plan: null | string;
}

export default function ChoosePlan({ tenant }: { tenant: Tenant }) {
    const { t } = useLaravelReactI18n();

    const { data, setData, post } = useForm<FormDataProps>({
        user: tenant.id,
        vat_number: tenant.vat_number,
        product: null,
        plan: null,
    });

    const handleSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('checkout'));
    };

    return (
        <WebsiteLayout>
            <Head title={t('website_pricing.meta_title')}></Head>
            <section className="text-website-font w-full">
                <h1>Welcome {tenant.company_name}</h1>
                <form action="" onSubmit={handleSubmit}>
                    <Button
                        type="button"
                        onClick={() => {
                            setData('product', 'prod_TWaytwcuX4Mb03');
                            setData('plan', 'price_1SZXnhFHXryfbBkbXL0omY5n');
                        }}
                        variant={'cta'}
                    >
                        Premium - Year (prod_TWaytwcuX4Mb03 , price_1SZXnhFHXryfbBkbXL0omY5n)
                    </Button>
                    <Button
                        type="button"
                        variant={'cta'}
                        onClick={() => {
                            setData('product', 'prod_TWaytwcuX4Mb03');
                            setData('plan', 'price_1SZXmvFHXryfbBkbFnFMYnTJ');
                        }}
                    >
                        Premium - Month (prod_TWaytwcuX4Mb03 , price_1SZXmvFHXryfbBkbFnFMYnTJ)
                    </Button>
                    <Button type="submit">Submit</Button>
                </form>
                Un instant, nous vous redirigeons vers notre partenaire pour le paiement.
            </section>
        </WebsiteLayout>
    );
}
