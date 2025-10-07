import { type BreadcrumbItem,  Company } from '@/types';
import { Head, useForm,} from '@inertiajs/react';
import HeadingSmall from '@/components/heading-small';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { FormEventHandler, useState } from 'react';
import ImageUploadModale from '@/components/ImageUploadModale';
import { Button } from '@/components/ui/button';
import { Trash2, Upload } from 'lucide-react';
import { BiSolidFilePdf } from 'react-icons/bi';
import { useToast } from '@/components/ToastrContext';
import axios from 'axios';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Settings',
        href: '/settings/profile',
    },
    {
        title: 'Import/Export',
        href: '/settings/import-export',
    },
];

interface TypeFormData {
    file: File | null;
    [key: string] : any
}

export default function ImportExportSettings() {
    // const [isModalOpen, setIsModalOpen] = useState(false);
    const { showToast } = useToast();
    
    const { data, setData } = useForm<TypeFormData>({
        file: null
    })
    

    const uploadFile: FormEventHandler = async (e) => {
        e.preventDefault()

        console.log("UPLOAD FILE");
        try {
             const response = await axios.post(route('api.tenant.import.assets'), data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                }
            });
            console.log(response.data);
            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            console.log(error);
            showToast(error.response.data.message, error.response.data.status);
        }
    }

    console.log(data);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Company" />

            <SettingsLayout>
                <div className="w-full space-y-6 space-x-2">
                    <div className="relative gap-4">
                        <HeadingSmall title="Import/Export" />
                        <a href={route('tenant.assets.export')} >
                        <Button variant={'secondary'}>
                            <BiSolidFilePdf size={20} />
                            Exporter les assets
                        </Button>
                    </a>
                    </div>
                    <form action="" onSubmit={uploadFile}>
                        <input
                            type="file"
                            name=""
                            id=""
                            onChange={(e) => (
                                (e.target.files && e.target.files?.length > 0) ? setData('file', e.target.files[0]) : null
                            )}
                        />
                        <Button> Submit </Button>
                    </form>

                    {/* <ImageUploadModale
                        isOpen={isModalOpen}
                        onClose={() => setIsModalOpen(false)}
                        uploadUrl={route('api.company.logo.store')}
                        onUploadSuccess={fetchCompany}
                    /> */}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
