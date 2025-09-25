import { AddressForm } from '@/components/addressForm';
import InputError from '@/components/input-error';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/central/app-layout';
import { Tenant, type BreadcrumbItem } from '@/types';
import { Head, router, useForm } from '@inertiajs/react';
import axios from 'axios';
import { Loader } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

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

export default function CreateTenant({ company }: { company?: Tenant }) {
    const [isProcessing, setIsProcessing] = useState<boolean>(false);
    const { showToast } = useToast();

    const { data, setData, post, errors } = useForm<TenantFormData>({
        company_name: company?.company_name ?? '',
        first_name: company?.first_name ?? '',
        last_name: company?.last_name ?? '',
        password: '',
        password_confirmation: '',
        domain_name: company?.domain?.domain ?? '',
        company_code: company?.company_code ?? '',
        email: company?.email ?? '',
        vat_number: company?.vat_number ?? '',
        phone_number: company?.phone_number ?? '',
        company: {
            street: company?.company_address?.street ?? '',
            house_number: company?.company_address?.house_number ?? '',
            zip_code: company?.company_address?.zip_code ?? '',
            city: company?.company_address?.city ?? '',
            country: company?.company_address?.country ?? '',
        },
        same_address_as_company: company?.invoice_address === undefined || company?.invoice_address === null,
        invoice: {
            street: company?.invoice_address?.street ?? '',
            house_number: company?.invoice_address?.house_number ?? '',
            zip_code: company?.invoice_address?.zip_code ?? '',
            city: company?.invoice_address?.city ?? '',
            country: company?.invoice_address?.country ?? '',
        },
    });

    const submit: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);
        
        if (company) {
            try {
                const response = await axios.patch(route('central.tenants.update', company.id), data, {
                    headers: {
                        'Content-Type': 'application/json',
                        'X-HTTP-Method-Override': 'PATCH',
                        Accept: 'application/json',
                    },
                });
                if (response.data.status === 'success') {  
                    setIsProcessing(false);
                    showToast(response.data.message, response.data.type);
                }
            } catch (error) {
                setIsProcessing(false);
                showToast(error.response.data.message, error.response.data.type);
            }
            
        } else {
            try {
                const response = await axios.post(route('central.tenants.store'), data);
                console.log(response);
                if (response.data.status === 'success') {
                    setIsProcessing(false);
                    router.visit(route('central.tenants.index'));
                }
            } catch (error) {
                console.log(error);
                setIsProcessing(false);
                showToast(error.response.data.message, error.response.data.type);
                
            }
            
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
                            minLength={4}
                            value={data.domain_name}
                            disabled={company ? true : false}
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
                            maxLength={4}
                            minLength={4}
                            value={data.company_code}
                            disabled={company ? true : false}
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
                        tabIndex={3}
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
                        tabIndex={4}
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
                        tabIndex={5}
                        value={data.last_name}
                        onChange={(e) => setData('last_name', e.target.value)}
                        placeholder="Doe"
                    />
                    <InputError className="mt-2" message={errors.last_name} />

                    <Label htmlFor="email">Tenant email</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        tabIndex={6}
                        value={data.email}
                        onChange={(e) => setData('email', e.target.value)}
                        placeholder="tenant@company.com"
                    />
                    <InputError className="mt-2" message={errors.email} />

                    {/* <Label htmlFor="password">Tenant password</Label> */}
                    {/* <Input
                        id="password"
                        type="password"
                        required
                        tabIndex={7}
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
                        tabIndex={8}
                        value={data.password_confirmation}
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        placeholder="Password confirmation"
                    />
                    <InputError className="mt-2" message={errors.password_confirmation} /> */}

                    <h3>Company Address</h3>
                    <AddressForm idPrefix="company" address={data.company} onChange={(updated) => setData('company', updated)} />

                    <Label htmlFor="phone_number">Tenant phone_number</Label>
                    <Input
                        id="phone_number"
                        type="text"
                        required
                        tabIndex={9}
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

                    {!data.same_address_as_company && (
                        <AddressForm idPrefix="invoice" address={data.invoice} onChange={(updated) => setData('invoice', updated)} />
                    )}

                    <Button type="submit" tabIndex={10}>
                        Submit
                    </Button>
                </form>
            </div>
            {isProcessing && (
                <div className="bg-background/50 fixed inset-0 z-50">
                    <div className="bg-background/20 flex h-dvh items-center justify-center">
                        <div className="bg-background flex items-center justify-center p-4 text-center md:w-1/3">
                            <div className="flex flex-col items-center gap-4">
                                <Loader size={48} className="animate-pulse" />
                                <p className="mx-auto animate-pulse text-3xl font-bold">Processing...</p>
                                <p className="mx-auto">Tenant is being created...</p>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </AppLayout>
    );
}
