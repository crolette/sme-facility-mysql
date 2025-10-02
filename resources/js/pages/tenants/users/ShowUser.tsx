import ImageUploadModale from '@/components/ImageUploadModale';
import Modale from '@/components/Modale';
import SidebarMenuAssetLocation from '@/components/tenant/sidebarMenuAssetLocation';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, User } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { Pencil, Trash, Trash2, Upload } from 'lucide-react';
import { useState } from 'react';

export default function ShowUser({ item }: { item: User }) {
    const [user, setUser] = useState(item);
    const { showToast } = useToast();
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index users`,
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
                            Edit
                        </Button>
                    </a>
                    <Button onClick={() => setShowDeleteModale(!showDeleteModale)} variant={'destructive'}>
                        <Trash2 />
                        Delete
                    </Button>
                    <Button onClick={() => setIsModalOpen(true)} variant={'secondary'}>
                        <Upload size={20} />
                        Upload profile picture
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
                                    <div>
                                        <p>Name : {user.full_name}</p>
                                        <p>Email : {user.email}</p>
                                        <p>Job position: {user.job_position}</p>
                                        <p>Can login : {user.can_login ? 'YES' : 'NO'}</p>
                                        <p>Role: {item.roles?.length > 0 ? item.roles[0].name : ''}</p>
                                        {user.provider && (
                                            <p>
                                                Provider: <a href={route('tenant.providers.show', user.provider?.id)}>{user.provider?.name}</a>
                                            </p>
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
                        {activeTab === 'assets' && (
                            <div className="border-sidebar-border bg-sidebar rounded-md border p-4 shadow-xl">
                                <h3>Assets</h3>
                                {user.assets &&
                                    user.assets.map((asset) => (
                                        <div key={asset.id}>
                                            <p>
                                                Code: <a href={route('tenant.assets.show', asset.reference_code)}>{asset.code}</a>
                                            </p>
                                            <p>Name: {asset.name}</p>
                                            <p>Description: {asset.description}</p>
                                            <p>
                                                Model: {asset.brand} - {asset.model}
                                            </p>
                                            <p>Last maintenance date: {asset.maintainable.last_maintenance_date}</p>
                                            <p>Next maintenance date: {asset.maintainable.next_maintenance_date}</p>
                                        </div>
                                    ))}
                            </div>
                        )}
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
