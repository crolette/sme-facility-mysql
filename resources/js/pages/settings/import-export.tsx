import HeadingSmall from '@/components/heading-small';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { usePermissions } from '@/hooks/usePermissions';
import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import { type BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Loader } from 'lucide-react';
import { FormEventHandler, useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

interface TypeFormData {
    file: File | null;
}

export default function ImportExportSettings() {
    const { hasPermission } = usePermissions();
    // const [isModalOpen, setIsModalOpen] = useState(false);
    const { t, tChoice } = useLaravelReactI18n();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${t('settings.profile')}`,
            href: '/settings/profile',
        },
        {
            title: `${t('settings.import_export')}`,
            href: '/settings/import-export',
        },
    ];
    const { showToast } = useToast();
    const [isProcessing, setIsProcessing] = useState<boolean>(false);

    const { data, setData, reset } = useForm<TypeFormData>({
        file: null,
        template: false,
    });

    const uploadImportFile: FormEventHandler = async (e) => {
        e.preventDefault();
        setIsProcessing(true);
        try {
            const response = await axios.post(route('api.tenant.import'), data, {
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
            const response = await axios.post(route(`tenant.${itemsToBeExported}.export`));
            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        } finally {
            reset();
            setIsProcessing(false);
            setItemsToBeExported(null);
        }
    };

    const [templateToBeExported, setTemplateToBeExported] = useState<string | null>(null);

    const exportTemplate = async () => {
        setIsProcessing(true);
        try {
            const response = await axios.post(route(`tenant.${templateToBeExported}.export`), { template: true });
            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        } finally {
            reset();
            setIsProcessing(false);
            setTemplateToBeExported(null);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={t('settings.import_export')} />

            <SettingsLayout>
                <div className="w-full space-y-6 space-x-2">
                    <div className="relative gap-4">
                        <HeadingSmall title={t('settings.import_export')} description={t('settings.import_export_description')} />
                    </div>
                    <h4>Export datas</h4>
                    {hasPermission('export excel') && (
                        <div className="flex w-fit items-center gap-4">
                            <select
                                name=""
                                id=""
                                defaultValue={''}
                                value={itemsToBeExported ?? ''}
                                onChange={(e) => setItemsToBeExported(e.target.value)}
                            >
                                <option value="" disabled>
                                    -- Select items to export --
                                </option>
                                <option value="assets">{tChoice('assets.title', 2)}</option>
                                <option value="users">{tChoice('contacts.title', 2)}</option>
                                <option value="providers">{tChoice('providers.title', 2)}</option>
                                <option value="contracts">{tChoice('contracts.title', 2)}</option>
                            </select>
                            <Button variant={'secondary'} onClick={exportItems} disabled={isProcessing || !itemsToBeExported}>
                                <BiSolidFilePdf size={20} />
                                {t('actions.export')}
                            </Button>
                        </div>
                    )}
                    <h4>Export template</h4>
                    {hasPermission('export excel') && (
                        <div className="flex w-fit items-center gap-4">
                            <select
                                name=""
                                id=""
                                defaultValue={''}
                                value={templateToBeExported ?? ''}
                                onChange={(e) => setTemplateToBeExported(e.target.value)}
                            >
                                <option value="" disabled>
                                    -- Select template to export --
                                </option>
                                <option value="assets">{tChoice('assets.title', 2)}</option>
                                <option value="users">{tChoice('contacts.title', 2)}</option>
                                <option value="providers">{tChoice('providers.title', 2)}</option>
                                <option value="contracts">{tChoice('contracts.title', 2)}</option>
                            </select>
                            <Button variant={'secondary'} onClick={exportTemplate} disabled={isProcessing || !templateToBeExported}>
                                <BiSolidFilePdf size={20} />
                                {t('actions.export')}
                            </Button>
                        </div>
                    )}
                    {hasPermission('import excel') && (
                        <>
                            <h4>Import</h4>
                            <p className="font-bold">Remarque</p>
                            <p>
                                Pour pouvoir importer des données, il faut avoir au préalable télécharger un template ou avoir exporté des données.
                                <br />
                                Choisissez le fichier à importer. Le nom du fichier doit contenir le type de données à importer.
                            </p>

                            <form action="" onSubmit={uploadImportFile}>
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
                                            <span>{t('actions.processing')}</span>
                                        </>
                                    ) : (
                                        <span>{t('actions.submit')}</span>
                                    )}
                                </Button>
                            </form>
                        </>
                    )}
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
