import { type BreadcrumbItem, type SharedData, Company } from '@/types';
import { Head, router, usePage } from '@inertiajs/react';
import HeadingSmall from '@/components/heading-small';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { useState } from 'react';
import ImageUploadModale from '@/components/ImageUploadModale';
import { Button } from '@/components/ui/button';
import { Upload } from 'lucide-react';
import { BiSolidFilePdf } from 'react-icons/bi';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];


export default function CompanySettings({ company }: { company: Company }) {
    // const { auth } = usePage<SharedData>().props;
 const [isModalOpen, setIsModalOpen] = useState(false);
    console.log(company);
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Company" />

            <SettingsLayout>
                <div className="space-y-6">
                    <Button onClick={() => setIsModalOpen(true)} variant={'secondary'}>
                        <Upload size={20} />
                        Uploader un logo
                    </Button>
                    <HeadingSmall title="Company information" />
                    <p>Name: {company.name}</p>
                    <p>VAT number: {company.vat_number}</p>
                    <p>Address: {company.address}</p>
                    <p>Logo:</p>
                    <img src={route('api.image.show', { path: company.logo })} alt="" className="h-40 object-cover" />
                    <ImageUploadModale
                        isOpen={isModalOpen}
                        onClose={() => setIsModalOpen(false)}
                        uploadUrl={route('api.company.logo.store')}
                        // onUploadSuccess={handleUploadSuccess}
                    />
                    <a href={route('tenant.pdf.qr-codes', {type: 'all'})} target='__blank'>
                        <Button variant={'secondary'}>
                            <BiSolidFilePdf size={20} />
                            Download QR Codes
                        </Button>
                    </a>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
