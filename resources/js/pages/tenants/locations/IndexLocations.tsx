import Modale from '@/components/Modale';
import { Pagination } from '@/components/pagination';
import { useToast } from '@/components/ToastrContext';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, PaginatedData, TenantBuilding, TenantFloor, TenantRoom, TenantSite } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { Pencil, PlusCircle, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { BiSolidFilePdf } from 'react-icons/bi';

export default function IndexSites({ items, routeName }: { items: PaginatedData; routeName: string }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${routeName}`,
            href: `/${routeName}`,
        },
    ];

    console.log(items);

    const [locations, setLocations] = useState(items.data);
    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
    const [locationToDelete, setLocationToDelete] = useState<TenantSite | TenantBuilding | TenantFloor | TenantRoom | null>(null);
    const { showToast } = useToast();

    const deleteLocation = async () => {
        try {
            const response = await axios.delete(route(`api.${routeName}.destroy`, locationToDelete?.reference_code));
            if (response.data.status === 'success') {
                setShowDeleteModale(false);
                setLocationToDelete(null);
                fetchLocations();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            console.log(error);
            setShowDeleteModale(false);
            showToast(error.response.data.message, error.response.data.status);
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
                <div className="flex space-x-2">
                    <a href={route(`tenant.${routeName}.create`)}>
                        <Button>
                            <PlusCircle />
                            Create
                        </Button>
                    </a>
                    <a href={route('tenant.pdf.qr-codes', { type: routeName })} target="__blank">
                        <Button variant={'secondary'}>
                            <BiSolidFilePdf size={20} />
                            Download QR Codes
                        </Button>
                    </a>
                </div>
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

                                        <TableBodyData className="space-x-2">
                                            <a href={route(`tenant.${routeName}.edit`, item.reference_code)}>
                                                <Button>
                                                    <Pencil />
                                                </Button>
                                            </a>
                                            <Button
                                                onClick={() => {
                                                    setShowDeleteModale(true);
                                                    setLocationToDelete(item);
                                                }}
                                                variant={'destructive'}
                                            >
                                                <Trash2 />
                                            </Button>
                                        </TableBodyData>
                                    </TableBodyRow>
                                );
                            })}
                    </TableBody>
                </Table>
                <Pagination items={items} />
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
