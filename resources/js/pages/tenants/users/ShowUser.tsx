import ImageUploadModale from '@/components/ImageUploadModale';
import Modale from '@/components/Modale';
import { AssetManager } from '@/components/tenant/assetManager';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import Field from '@/components/ui/field';
import { Pill } from '@/components/ui/pill';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Intervention, User } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Pencil, Trash, Trash2, Upload } from 'lucide-react';
import { useState } from 'react';

export default function ShowUser({ item }: { item: User }) {
    const { t, tChoice } = useLaravelReactI18n();
    const [user, setUser] = useState(item);
    const { showToast } = useToast();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${tChoice('contacts.title', 2)}`,
            href: `/users`,
        },
        {
            title: `${user.full_name} (${user.provider ? user.provider.name : 'Internal'})`,
            href: `/users/${user.id}`,
        },
    ];

    const fetchUser = async () => {
        try {
            const response = await axios.get(route('api.users.show', user.id));
            setUser(response.data.data);
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const deleteUser = async () => {
        try {
            const response = await axios.delete(route('api.users.destroy', user.id));
            if (response.data.status === 'success') {
                router.visit(route('tenant.users.index'));
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const [isModalOpen, setIsModalOpen] = useState(false);
    // const [uploadedImages, setUploadedImages] = useState([]);

    const handleUploadSuccess = (result) => {
        fetchUser();
    };

    const deleteProfilePicture = async () => {
        try {
            const response = await axios.delete(route('api.users.picture.destroy', user.id));
            if (response.data.status === 'success') {
                showToast(response.data.message, response.data.status);
                fetchUser();
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);

    const [activeTab, setActiveTab] = useState('information');

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={user.full_name} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex gap-2">
                    <a href={route(`tenant.users.edit`, user.id)}>
                        <Button>
                            <Pencil />
                            {t('actions.edit')}
                        </Button>
                    </a>
                    <Button onClick={() => setShowDeleteModale(!showDeleteModale)} variant={'destructive'}>
                        <Trash2 />
                        {t('actions.delete')}
                    </Button>
                    <Button onClick={() => setIsModalOpen(true)} variant={'secondary'}>
                        <Upload size={20} />
                        {t('actions.upload-type', { type: t('contacts.profile_picture') })}
                    </Button>
                </div>

                <div className="grid max-w-full gap-4 lg:grid-cols-[1fr_6fr]">
                    <SidebarMenuAssetLocation
                        activeTab={activeTab}
                        setActiveTab={setActiveTab}
                        menu="user"
                        infos={{
                            name: user.full_name,
                            code: user.email ?? '',
                            reference: user.job_position ?? '',
                            levelPath: user.provider?.name ? route('tenant.providers.show', user.provider.id) : '',
                            levelName: user.provider?.name ?? '',
                        }}
                    />
                    <div className="overflow-hidden">
                        {activeTab === 'information' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <div className="grid grid-cols-[1fr_160px] gap-4">
                                    <div className="space-y-2">
                                        <Field label={t('common.name')} text={user.full_name} />
                                        <Field label={t('common.email')} text={user.email} />
                                        {user.job_position && <Field label={t('contacts.job_position')} text={user.job_position} />}
                                        <Field label={t('contacts.can_login')} text={user.can_login ? t('common.yes') : t('common.no')} />
                                        {user.roles?.length > 0 && (
                                            <Field label={t('contacts.role')} text={user.roles?.length > 0 ? user.roles[0].name : ''} />
                                        )}
                                    </div>
                                    <div className="relative w-fit">
                                        {user.avatar && (
                                            <div>
                                                <img
                                                    src={route('api.image.show', { path: user.avatar })}
                                                    alt=""
                                                    className="h-40 w-40 rounded-full object-cover"
                                                />
                                                <Button
                                                    type="button"
                                                    onClick={deleteProfilePicture}
                                                    variant={'destructive'}
                                                    className="absolute top-2 right-2"
                                                >
                                                    <Trash></Trash>
                                                </Button>
                                            </div>
                                        )}
                                    </div>
                                </div>
                            </div>
                        )}
                        {activeTab === 'interventions' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h3>{tChoice('interventions.title', 2)}</h3>

                                {item.assigned_interventions ? (
                                    <div>
                                        {item.assigned_interventions.map((intervention: Intervention) => (
                                            <div>
                                                <Table key={intervention.id} className="table-fixed">
                                                    <TableHead>
                                                        <TableHeadRow>
                                                            <TableHeadData className="w-52">{t('common.description')}</TableHeadData>
                                                            <TableHeadData>{t('common.type')}</TableHeadData>
                                                            <TableHeadData>{t('interventions.priority')}</TableHeadData>
                                                            <TableHeadData>{t('interventions.status')}</TableHeadData>
                                                            <TableHeadData>{t('interventions.assigned_to')}</TableHeadData>
                                                            <TableHeadData>{t('interventions.planned_at')}</TableHeadData>
                                                            <TableHeadData>{t('interventions.repair_delay')}</TableHeadData>
                                                            <TableHeadData>{t('interventions.total_costs')}</TableHeadData>
                                                        </TableHeadRow>
                                                    </TableHead>

                                                    <TableBody>
                                                        <TableBodyRow className="">
                                                            <TableBodyData className="flex max-w-72">
                                                                <a
                                                                    className="overflow-hidden overflow-ellipsis whitespace-nowrap"
                                                                    href={route('tenant.interventions.show', intervention.id)}
                                                                >
                                                                    {intervention.description}
                                                                </a>
                                                                <p className="tooltip tooltip-top">{intervention.description}</p>
                                                            </TableBodyData>
                                                            <TableBodyData>{intervention.type}</TableBodyData>
                                                            <TableBodyData>
                                                                <Pill variant={intervention.priority}>
                                                                    {t(`interventions.priority.${intervention.priority}`)}
                                                                </Pill>
                                                            </TableBodyData>
                                                            <TableBodyData>{t(`interventions.status.${intervention.status}`)}</TableBodyData>
                                                            <TableBodyData>
                                                                {intervention.assignable ? (
                                                                    intervention.assignable.full_name ? (
                                                                        <a href={route('tenant.users.show', intervention.assignable.id)}>
                                                                            {intervention.assignable.full_name}
                                                                        </a>
                                                                    ) : (
                                                                        <a href={route('tenant.providers.show', intervention.assignable.id)}>
                                                                            {intervention.assignable.name}
                                                                        </a>
                                                                    )
                                                                ) : (
                                                                    t('interventions.assigned_not')
                                                                )}
                                                            </TableBodyData>
                                                            <TableBodyData>
                                                                {intervention.planned_at ?? t('interventions.planned_at_no')}
                                                            </TableBodyData>
                                                            <TableBodyData>
                                                                {intervention.repair_delay ?? t('interventions.repair_delay_no')}
                                                            </TableBodyData>
                                                            <TableBodyData>
                                                                {intervention.total_costs ? `${intervention.total_costs} €` : '-'}
                                                            </TableBodyData>
                                                        </TableBodyRow>
                                                    </TableBody>
                                                </Table>
                                            </div>
                                        ))}
                                    </div>
                                ) : (
                                    <p>No interventions</p>
                                )}
                            </div>
                        )}
                        {activeTab === 'assets' && <AssetManager items={user.assets} />}
                    </div>
                </div>
            </div>

            <ImageUploadModale
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                uploadUrl={route('api.users.picture.store', user.id)}
                onUploadSuccess={handleUploadSuccess}
                title={'Upload profile picture'}
            />
            <Modale
                title={'Delete user'}
                message={`Are you sure you want to delete this user ${user?.full_name} ?`}
                isOpen={showDeleteModale}
                onConfirm={deleteUser}
                onCancel={() => {
                    setShowDeleteModale(false);
                }}
            />
        </AppLayout>
    );
}
