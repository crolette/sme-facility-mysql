import Modale from '@/components/Modale';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, TenantBuilding, TenantFloor, TenantRoom, TenantSite } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';

export default function IndexSites({ items, routeName }: { locations: TenantSite[] | TenantBuilding[] | TenantFloor[]; routeName: string }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${routeName}`,
            href: `/${routeName}`,
        },
    ];

    const [locations, setLocations] = useState(items);
    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
    const [locationToDelete, setLocationToDelete] = useState<TenantSite | TenantBuilding | TenantFloor | TenantRoom | null>(null);

    const deleteLocation = async () => {
        try {
            const response = await axios.delete(route(`api.${routeName}.destroy`, locationToDelete?.reference_code));
            if (response.data.status === 'success') {
                setShowDeleteModale(false);
                setLocationToDelete(null);
                fetchLocations();
            }
        } catch (error) {
            console.log(error);
        }
    };

    const fetchLocations = async () => {
        try {
            const response = await axios.get(route(`api.${routeName}.index`));
            if (response.data.status === 'success') {
                setLocations(response.data.data);
            }
        } catch (error) {
            console.log(error);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sites" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route(`tenant.${routeName}.create`)}>
                    <Button>Create</Button>
                </a>
                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>Reference code</TableHeadData>
                            <TableHeadData>Code</TableHeadData>
                            <TableHeadData>Category</TableHeadData>
                            <TableHeadData>Name</TableHeadData>
                            <TableHeadData>Description</TableHeadData>
                            <TableHeadData></TableHeadData>
                        </TableHeadRow>
                    </TableHead>
                    <TableBody>
                        {locations &&
                            locations.map((item, index) => {
                                return (
                                    <TableBodyRow key={index}>
                                        <TableBodyData>
                                            <a href={route(`tenant.${routeName}.show`, item.reference_code)}> {item.reference_code} </a>
                                        </TableBodyData>
                                        <TableBodyData>{item.code}</TableBodyData>
                                        <TableBodyData>{item.category}</TableBodyData>
                                        <TableBodyData>{item.name}</TableBodyData>
                                        <TableBodyData>{item.description}</TableBodyData>

                                        <TableBodyData>
                                            <a href={route(`tenant.${routeName}.show`, item.reference_code)}>
                                                <Button variant={'outline'}>See</Button>
                                            </a>

                                            <a href={route(`tenant.${routeName}.edit`, item.reference_code)}>
                                                <Button>Edit</Button>
                                            </a>
                                            <Button
                                                onClick={() => {
                                                    setShowDeleteModale(true);
                                                    setLocationToDelete(item);
                                                }}
                                                variant={'destructive'}
                                            >
                                                Delete
                                            </Button>
                                        </TableBodyData>
                                    </TableBodyRow>
                                );
                            })}
                    </TableBody>
                </Table>
            </div>
            <Modale
                title={`Delete ${routeName}`}
                message={`Are you sure you want to delete ${locationToDelete?.name}`}
                isOpen={showDeleteModale}
                onConfirm={deleteLocation}
                onCancel={() => {
                    setLocationToDelete(null);
                    setShowDeleteModale(false);
                }}
            />
        </AppLayout>
    );
}
