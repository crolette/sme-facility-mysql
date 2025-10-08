import { type BreadcrumbItem,  Company } from '@/types';
import { Head,} from '@inertiajs/react';
import HeadingSmall from '@/components/heading-small';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { useState } from 'react';
import ImageUploadModale from '@/components/ImageUploadModale';
import { Button } from '@/components/ui/button';
import { Trash2, Upload } from 'lucide-react';
import { BiSolidFilePdf } from 'react-icons/bi';
import { useToast } from '@/components/ToastrContext';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Profile settings',
        href: '/settings/profile',
    },
];


export default function CompanySettings({ item }: { item: Company }) {
    const [company, setCompany] = useState(item);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const { showToast } = useToast();
    
    const deleteLogo = async() =>  {

        try {
            const response = await axios.delete(route('api.company.logo.destroy'));
            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
                fetchCompany();
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
            
        }
    }

    const fetchCompany = async () => {

        try {
            const response = await axios.get(route('api.company.logo.show'));
            console.log(response.data);
            if (response.data.status === 'success') {
                setCompany(response.data.data)
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    }


    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Company" />

            <SettingsLayout>
                <div className="w-full space-y-6 space-x-2">
                    <Button onClick={() => setIsModalOpen(true)} variant={'secondary'}>
                        <Upload size={20} />
                        Uploader un logo
                    </Button>
                    <a href={route('tenant.pdf.qr-codes', { type: 'all' })} target="__blank">
                        <Button variant={'secondary'}>
                            <BiSolidFilePdf size={20} />
                            Download QR Codes
                        </Button>
                    </a>
                    <div className="relative gap-4">
                        <HeadingSmall title="Company information" />
                        <div className="space-y-4">
                            <p className="">Name: {company.name}</p>
                            <p className="">VAT number: {company.vat_number}</p>
                            <p className="">Address: {company.address}</p>
                        </div>
                        {/* <div className="flex flex-row gap-6">
                            <div className="flex flex-col justify-evenly bg-amber-100">
                                <p className="font-semibold">Name</p>
                                <p className="font-semibold">VAT number</p>
                                <p className="font-semibold">Address</p>
                            </div>
                            <div className="flex flex-col justify-between gap-2">
                                <p className="bg-accent/25 rounded-md px-4 py-2 font-normal">{company.name}</p>
                                <p className="bg-accent/25 rounded-md px-4 py-2 font-normal">{company.vat_number}</p>
                                <p className="bg-accent/25 rounded-md px-4 py-2 font-normal">{company.address}</p>
                            </div>
                        </div> */}
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
            </SettingsLayout>
        </AppLayout>
    );
}
