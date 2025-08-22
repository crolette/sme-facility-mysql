import { Button } from '@/components/ui/button';
import AppLayout from '@/layouts/app-layout';
import { Asset, BreadcrumbItem, Contract, TenantBuilding, TenantFloor, TenantRoom, TenantSite } from '@/types';
import { Head, router } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';

export default function ShowContract({ item, objects }: { item: Contract; objects: [] }) {
    const [contract, setContract] = useState<Contract>(item);
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Show contract`,
            href: `/contract`,
        },
    ];

    console.log(objects);

    const deleteContract = async (contract: Contract) => {
        try {
            const response = await axios.delete(route('api.contracts.destroy', contract.id));
            if (response.data.status === 'success') {
                console.log('Contract deleted');
                router.visit(route('tenant.contracts.index'));
            }
        } catch (error) {
            console.log(error);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Contract" />

            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <div className="flex gap-2">
                    <a href={route(`tenant.contracts.edit`, contract.id)}>
                        <Button>Edit</Button>
                    </a>
                    <Button onClick={() => deleteContract(contract)} variant={'destructive'}>
                        Delete
                    </Button>
                </div>
                <div className="flex items-center gap-2">
                    <div className="flex w-full shrink-0 justify-between rounded-md border border-gray-200 p-4">
                        <div>
                            <p>Name: {contract.name}</p>
                            <p>Internal reference: {contract.internal_reference}</p>
                            <p>Status: {contract.status}</p>
                            <p>Renewal Type: {contract.renewal_type}</p>
                            <p>Start date: {contract.start_date}</p>
                            <p>End date : {contract.end_date}</p>
                            <p>Notes: {contract.notes}</p>
                            <p>Provider: {contract.provider.name}</p>
                            <p>Provider reference: {contract.provider_reference}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <h3>Assets</h3>
                <ul>
                    {objects.map((object: Partial<Asset | TenantBuilding | TenantSite | TenantFloor | TenantRoom>) => (
                        <li key={object.id}>
                            <p>
                                <a href={route('tenant.assets.show', object.reference_code)}>
                                    {object.name} - {object.category}- {object.code}
                                </a>
                            </p>
                        </li>
                    ))}
                </ul>
            </div>
        </AppLayout>
    );
}
