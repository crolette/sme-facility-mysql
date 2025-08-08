import ImageUploadModale from '@/components/ImageUploadModale';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, User } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { Upload } from 'lucide-react';
import { useState } from 'react';

export default function UserShow({ item }: { item: User }) {
    const [user, setUser] = useState(item);
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${user.full_name}`,
            href: `/users/${user.id}`,
        },
    ];

    console.log(user);

    const fetchUser = async () => {
        try {
            const response = await axios.get(route('api.users.show', user.id));
            setUser(response.data.data);
        } catch (error) {
            console.log(error);
        }
    };

    const deleteUser = async () => {
        try {
            const response = await axios.delete(route('api.users.destroy', user.id));
            if (response.data.status === 'success') {
                window.location.href = route('tenant.users.index');
            }
        } catch (error) {
            console.log(error);
        }
    };

    const [isModalOpen, setIsModalOpen] = useState(false);
    // const [uploadedImages, setUploadedImages] = useState([]);

    const handleUploadSuccess = (result) => {
        // Ajouter l'image uploadée à la liste
        // setUploadedImages((prev) => [...prev, result]);
        console.log('Avatar uploadée avec succès:', result);
        fetchUser();
    };

    console.log(user.roles);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={user.full_name} />
            <div>
                <a href={route(`tenant.users.edit`, user.id)}>
                    <Button>Edit</Button>
                </a>
                <Button onClick={() => deleteUser()} variant={'destructive'}>
                    Delete
                </Button>
                <Button onClick={() => setIsModalOpen(true)} variant={'secondary'}>
                    <Upload size={20} />
                    Upload profile picture
                </Button>
            </div>
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <p>Name : {user.full_name}</p>
                <p>Email : {user.email}</p>
                <p>John position: {user.job_position}</p>
                <p>Can login : {user.can_login ? 'YES' : 'NO'}</p>
                <p>Role: {item.roles?.length > 0 ? item.roles[0].name : ''}</p>
                {user.provider && (
                    <p>
                        Provider: <a href={route('tenant.providers.show', user.provider?.id)}>{user.provider?.name}</a>
                    </p>
                )}
                {user.avatar && (
                    <div>
                        <img src={route('api.image.show', { path: user.avatar })} alt="" className="h-auto w-40 object-cover" />
                        {/* <Button type="button" onClick={deleteLogo} variant={'destructive'}>
                            Remove logo
                        </Button> */}
                    </div>
                )}
                <div>
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
            </div>

            <ImageUploadModale
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                uploadUrl={route('api.users.picture.store', user.id)}
                onUploadSuccess={handleUploadSuccess}
                title={'Upload profile picture'}
            />
        </AppLayout>
    );
}
