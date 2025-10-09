import { type BreadcrumbItem,  Company } from '@/types';
import { Head, useForm,} from '@inertiajs/react';
import HeadingSmall from '@/components/heading-small';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { FormEventHandler, useState } from 'react';
import ImageUploadModale from '@/components/ImageUploadModale';
import { Button } from '@/components/ui/button';
import { Loader, Trash2, Upload } from 'lucide-react';
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
}

export default function ImportExportSettings() {
    // const [isModalOpen, setIsModalOpen] = useState(false);
    const { showToast } = useToast();
    const [isProcessing, setIsProcessing] = useState<boolean>(false);
    
    const { data, setData} = useForm<TypeFormData>({
        file: null
    })
    

    const uploadFile: FormEventHandler = async (e) => {
        e.preventDefault()
        setIsProcessing(true);
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
        } finally {
            setData('file', null);
            setIsProcessing(false);
        }
    }

    const exportAssets: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);

        try {
            const response = await axios.get(route('tenant.assets.export'));
            console.log(response.data);
            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            console.log(error);
            showToast(error.response.data.message, error.response.data.status);
        } finally {
            setIsProcessing(false);
        }
    };

    console.log(data);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Company" />

            <SettingsLayout>
                <div className="w-full space-y-6 space-x-2">
                    <div className="relative gap-4">
                        <HeadingSmall title="Import/Export" />
                        <Button variant={'secondary'} onClick={exportAssets} disabled={isProcessing}>
                            <BiSolidFilePdf size={20} />
                            Exporter les assets
                        </Button>
                    </div>
                    <form action="" onSubmit={uploadFile}>
                        <input
                            type="file"
                            name=""
                            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            id=""
                            onChange={(e) => (e.target.files && e.target.files?.length > 0 ? setData('file', e.target.files[0]) : null)}
                        />
                        <Button disabled={isProcessing}>
                            {isProcessing ? (
                                <>
                                    <Loader className="animate-pulse" />
                                    <span>Submitting...</span>
                                </>
                            ) : (
                                <span>Submit</span>
                            )}
                        </Button>
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
