import ImageUploadModale from '@/components/ImageUploadModale';
import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Provider } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { Upload } from 'lucide-react';
import { useState } from 'react';

export default function ProviderShow({ item }: { item: Provider }) {
    const [provider, setProvider] = useState(item);
    console.log(item);

    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `${provider.name}`,
            href: `/providers/${provider.id}`,
        },
    ];

    const deleteProvider = (provider: Provider) => {
        console.log('delete provider : ' + provider.name);
    };

    const fetchProvider = async () => {
        try {
            const response = await axios.get(route('api.providers.show', provider.id));
            setProvider(response.data.data);
        } catch (error) {
            console.log(error);
        }
    };

    const deleteLogo = async () => {
        try {
            const response = await axios.delete(route('api.providers.logo.destroy', provider.id));
            console.log(response);
            fetchProvider();
        } catch (error) {
            console.log(error);
        }
    };

    const [isModalOpen, setIsModalOpen] = useState(false);
    // const [uploadedImages, setUploadedImages] = useState([]);

    const handleUploadSuccess = (result) => {
        // Ajouter l'image uploadée à la liste
        // setUploadedImages((prev) => [...prev, result]);
        console.log('Image uploadée avec succès:', result);
        fetchProvider();
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sites" />
            <div>
                <a href={route(`tenant.providers.edit`, provider.id)}>
                    <Button>Edit</Button>
                </a>
                <Button onClick={() => deleteProvider(provider)} variant={'destructive'}>
                    Delete
                </Button>
                <Button onClick={() => setIsModalOpen(true)} variant={'secondary'}>
                    <Upload size={20} />
                    Uploader un logo
                </Button>
            </div>
            <p>Name : {provider.name}</p>
            <p>Address : {provider.address}</p>
            <p>Phone number : {provider.phone_number}</p>
            <p>VAT Number : {provider.vat_number}</p>
            <p>Email : {provider.email}</p>
            {provider.logo && (
                <div>
                    <img src={route('api.image.show', { path: provider.logo })} alt="" className="h-auto w-40 object-cover" />
                    <Button type="button" onClick={deleteLogo} variant={'destructive'}>
                        Remove logo
                    </Button>
                </div>
            )}
            <ImageUploadModale
                isOpen={isModalOpen}
                onClose={() => setIsModalOpen(false)}
                uploadUrl={route('api.providers.logo.store', provider.id)}
                onUploadSuccess={handleUploadSuccess}
            />
            <h2>Users</h2>
        </AppLayout>
    );
}
