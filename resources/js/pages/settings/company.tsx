import HeadingSmall from '@/components/heading-small';
import ImageUploadModale from '@/components/ImageUploadModale';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem, Company } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Trash2, Upload } from 'lucide-react';
import { useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

export default function CompanySettings({ item, billingPortal, limits, usage }: { item: Company; billingPortal: string; limits: []; usage: [] }) {
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${t('settings.profile')}`,
            href: '/settings/profile',
        },
        {
            title: `${t('settings.company_title')}`,
            href: '/settings/company',
        },
    ];

    const [company, setCompany] = useState(item);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const { showToast } = useToast();

    const deleteLogo = async () => {
        try {
            const response = await axios.delete(route('api.company.logo.destroy'));
            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
                fetchCompany();
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const fetchCompany = async () => {
        try {
            const response = await axios.get(route('api.company.logo.show'));
            console.log(response.data);
            if (response.data.status === 'success') {
                setCompany(response.data.data);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('settings.company_title')} />

            <SettingsLayout>
                <div className="w-full space-y-6 space-x-2">
                    <div className="relative gap-4">
                        <HeadingSmall title={t('settings.company_title')} description={t('settings.company_description')} />
                    </div>
                    <Button onClick={() => setIsModalOpen(true)} variant={'secondary'}>
                        <Upload size={20} />
                        {t('actions.upload-type', { type: t('common.logo') })}
                    </Button>
                    <a href={route('tenant.pdf.qr-codes', { type: 'all' })} target="__blank">
                        <Button variant={'secondary'}>
                            <BiSolidFilePdf size={20} />
                            {t('actions.download-type', { type: tChoice('common.qr_codes', 2) })}
                        </Button>
                    </a>
                    <div className="relative gap-4">
                        <HeadingSmall title="Company information" />
                        <div className="space-y-4">
                            <p className="">
                                {t('common.name')}: {company.name}
                            </p>
                            <p className="">
                                {t('providers.vat_number')} : {company.vat_number}
                            </p>
                            <p className="">
                                {t('common.address')}:{company.address}
                            </p>
                        </div>

                        {company.logo && (
                            <div className="absolute top-2 right-2 shrink-1">
                                <img src={route('api.image.show', { path: company.logo })} alt="" className="max-h-40 max-w-full object-cover" />
                                <Button className="absolute top-2 right-2" variant={'destructive'} onClick={deleteLogo}>
                                    <Trash2 />
                                </Button>
                            </div>
                        )}
                    </div>

                    <ImageUploadModale
                        isOpen={isModalOpen}
                        onClose={() => setIsModalOpen(false)}
                        uploadUrl={route('api.company.logo.store')}
                        onUploadSuccess={fetchCompany}
                    />
                </div>
                <div>
                    <h3>Subscription Usage</h3>
                    <ul className="flex gap-2">
                        <li>
                            Sites: {usage.current_sites_count} / <span className="font-bold">{limits.max_sites}</span>
                        </li>
                        <span className="">|</span>
                        <li>
                            Users: {usage.current_users_count} / <span className="font-bold">{limits.max_users}</span>
                        </li>
                        <span className="">|</span>
                        <li>
                            Storage GB: {item.disk_size_gb} / <span className="font-bold">{limits.max_storage_gb}</span>
                        </li>
                    </ul>
                </div>

                <a href={billingPortal} target="_blank">
                    <Button>Billing Portal</Button>
                </a>
            </SettingsLayout>
        </AppLayout>
    );
}
