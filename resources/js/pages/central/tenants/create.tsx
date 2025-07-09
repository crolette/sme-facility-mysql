import { AddressForm } from '@/components/addressForm';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/central/app-layout';
import { Tenant, type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Create tenant',
        href: '/tenants/create',
    },
];

type TenantFormData = {
    company_name: string;
    email: string;
    first_name: string;
    last_name: string;
    password: string;
    password_confirmation: string;
    vat_number: string;
    domain_name: string;
    company_code: string;
    phone_number: string;
    company: {
        street: string;
        house_number: string;
        zip_code: string;
        city: string;
        country: string;
    };
    same_address_as_company: boolean;
    invoice: {
        street: string;
        house_number: string;
        zip_code: string;
        city: string;
        country: string;
    };
};

export default function CreateTenant({ tenant }: { tenant?: Tenant }) {
    const { data, setData, post, errors } = useForm<TenantFormData>({
        company_name: tenant?.company_name ?? '',
        first_name: '',
        last_name: '',
        password: '',
        password_confirmation: '',
        domain_name: tenant?.domain.domain ?? '',
        company_code: tenant?.company_code ?? '',
        email: tenant?.email ?? '',
        vat_number: tenant?.vat_number ?? '',
        phone_number: tenant?.phone_number ?? '',
        company: {
            street: tenant?.company_address?.street ?? '',
            house_number: tenant?.company_address?.house_number ?? '',
            zip_code: tenant?.company_address?.zip_code ?? '',
            city: tenant?.company_address?.city ?? '',
            country: tenant?.company_address?.country ?? '',
        },
        same_address_as_company: tenant?.invoice_address === undefined || tenant?.invoice_address === null,
        invoice: {
            street: tenant?.invoice_address?.street ?? '',
            house_number: tenant?.invoice_address?.house_number ?? '',
            zip_code: tenant?.invoice_address?.zip_code ?? '',
            city: tenant?.invoice_address?.city ?? '',
            country: tenant?.invoice_address?.country ?? '',
        },
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (tenant) {
            post(route('central.tenants.update', tenant.id), {
                headers: {
                    'Content-Type': 'application/json',
                    'X-HTTP-Method-Override': 'PATCH',
                    Accept: 'application/json',
                },
            });
        } else {
            post(route('central.tenants.store'));
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create tenant" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <form onSubmit={submit}>
                    <Label htmlFor="name">Company name</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autoFocus
                        tabIndex={1}
                        value={data.company_name}
                        onChange={(e) => setData('company_name', e.target.value)}
                        placeholder="Tenant name"
                    />
                    <InputError className="mt-2" message={errors.company_name} />

                    <Label htmlFor="domain">Domain name</Label>
                    <div className="flex items-center gap-2">
                        <Input
                            id="domain"
                            type="text"
                            required
                            tabIndex={2}
                            value={data.domain_name}
                            disabled={tenant ? true : false}
                            onChange={(e) => setData('domain_name', e.target.value)}
                            placeholder="Tenant domain"
                        />
                        <p className="w-fit shrink-0">.sme-facility.com</p>
                    </div>
                    <InputError className="mt-2" message={errors.domain_name} />
                    <Label htmlFor="company_code">Company code</Label>
                    <div className="flex items-center gap-2">
                        <Input
                            id="company_code"
                            type="text"
                            required
                            tabIndex={2}
                            value={data.company_code}
                            disabled={tenant ? true : false}
                            onChange={(e) => setData('company_code', e.target.value)}
                            placeholder="company_code"
                        />
                    </div>
                    <InputError className="mt-2" message={errors.company_code} />

                    <Label htmlFor="vat">Tenant VAT</Label>
                    <Input
                        id="vat"
                        type="text"
                        required
                        tabIndex={4}
                        value={data.vat_number}
                        onChange={(e) => setData('vat_number', e.target.value)}
                        placeholder="BE0123456789"
                    />
                    <InputError className="mt-2" message={errors.vat_number} />

                    <h2>Admin</h2>
                    <Label htmlFor="first_name">Tenant first_name</Label>
                    <Input
                        id="first_name"
                        type="text"
                        required
                        tabIndex={3}
                        value={data.first_name}
                        onChange={(e) => setData('first_name', e.target.value)}
                        placeholder="John"
                    />
                    <InputError className="mt-2" message={errors.first_name} />

                    <Label htmlFor="last_name">Tenant last_name</Label>
                    <Input
                        id="last_name"
                        type="text"
                        required
                        tabIndex={3}
                        value={data.last_name}
                        onChange={(e) => setData('last_name', e.target.value)}
                        placeholder="Doe"
                    />
                    <InputError className="mt-2" message={errors.last_name} />

                    <Label htmlFor="password">Tenant password</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        tabIndex={3}
                        value={data.password}
                        onChange={(e) => setData('password', e.target.value)}
                        placeholder="Password"
                    />
                    <InputError className="mt-2" message={errors.password} />

                    <Label htmlFor="password_confirmation">Tenant password_confirmation</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        tabIndex={3}
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        placeholder="Password confirmation"
                    />
                    <InputError className="mt-2" message={errors.password_confirmation} />

                    <Label htmlFor="email">Tenant email</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        tabIndex={3}
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        placeholder="tenant@company.com"
                    />
                    <InputError className="mt-2" message={errors.email} />

                    <h3>Company Address</h3>
                    <AddressForm idPrefix="company" address={data.company} onChange={(updated) => setData('company', updated)} />

                    <Label htmlFor="phone_number">Tenant phone_number</Label>
                    <Input
                        id="phone_number"
                        type="text"
                        required
                        tabIndex={4}
                        value={data.phone_number}
                        onChange={(e) => setData('phone_number', e.target.value)}
                        placeholder="+32456789123"
                    />
                    <InputError className="mt-2" message={errors.phone_number} />

                    <h3>Invoice Address</h3>
                    <Label htmlFor="invoice">Same as company ? </Label>
                    <Checkbox
                        name="invoice"
                        id="invoice"
                        checked={data.same_address_as_company}
                        onCheckedChange={(val) => setData('same_address_as_company', !!val)}
                    />
                    <br />

                    {/* {!data.same_address_as_company && (
                        <AddressForm idPrefix="invoice" address={data.invoice} onChange={(updated) => setData('invoice', updated)} />
                    )} */}

                    <Button type="submit" tabIndex={4}>
                        Submit
                    </Button>
                </form>
            </div>
        </AppLayout>
    );
}
