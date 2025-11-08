import HeadingSmall from '@/components/heading-small';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { Loader } from 'lucide-react';
import { FormEventHandler, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

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

    const { data, setData, reset } = useForm<TypeFormData>({
        file: null,
    });

    const uploadAssetFile: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);
        try {
            const response = await axios.post(route('api.tenant.import.assets'), data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        } finally {
            reset();
            setData('file', null);
            setIsProcessing(false);
        }
    };

    const uploadProviderFile: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);
        try {
            const response = await axios.post(route('api.tenant.import.providers'), data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        } finally {
            reset();
            setData('file', null);
            setIsProcessing(false);
        }
    };

    const uploadUserFile: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);
        try {
            const response = await axios.post(route('api.tenant.import.users'), data, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                },
            });
            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        } finally {
            reset();
            setData('file', null);
            setIsProcessing(false);
        }
    };

    const [itemsToBeExported, setItemsToBeExported] = useState<string | null>(null);

    const exportItems = async () => {
        setIsProcessing(true);
        try {
            const response = await axios.get(route(`tenant.${itemsToBeExported}.export`));
            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        } finally {
            reset();
            setIsProcessing(false);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Import/Export" />

            <SettingsLayout>
                <div className="w-full space-y-6 space-x-2">
                    <div className="relative gap-4">
                        <HeadingSmall title="Import/Export" />
                    </div>

                    <div className="flex w-fit flex-col gap-4">
                        <select name="" id="" defaultValue={''} onChange={(e) => setItemsToBeExported(e.target.value)}>
                            <option value="" disabled>
                                -- Select items to export --
                            </option>
                            <option value="assets">Assets</option>
                            <option value="providers">Providers</option>
                            <option value="users">Users</option>
                        </select>
                        <Button variant={'secondary'} onClick={exportItems} disabled={isProcessing || !itemsToBeExported}>
                            <BiSolidFilePdf size={20} />
                            Export
                        </Button>
                    </div>
                    <h3>Assets</h3>
                    <form action="" onSubmit={uploadAssetFile}>
                        <input
                            type="file"
                            name=""
                            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            id=""
                            onChange={(e) => (e.target.files && e.target.files?.length > 0 ? setData('file', e.target.files[0]) : null)}
                        />
                        <Button disabled={isProcessing || data.file === null}>
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
                    <h3>Providers</h3>

                    <form action="" onSubmit={uploadProviderFile}>
                        <input
                            type="file"
                            name=""
                            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            id=""
                            onChange={(e) => (e.target.files && e.target.files?.length > 0 ? setData('file', e.target.files[0]) : null)}
                        />
                        <Button disabled={isProcessing || data.file === null}>
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

                    <h3>Users</h3>
                    <form action="" onSubmit={uploadUserFile}>
                        <input
                            type="file"
                            name=""
                            accept="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            id=""
                            onChange={(e) => (e.target.files && e.target.files?.length > 0 ? setData('file', e.target.files[0]) : null)}
                        />
                        <Button disabled={isProcessing || data.file === null}>
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
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
