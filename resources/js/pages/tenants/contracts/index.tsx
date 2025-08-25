import { Button } from '@/components/ui/button';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Contract } from '@/types';
import { Head } from '@inertiajs/react';
import axios from 'axios';
import { useState } from 'react';

export default function IndexContracts({ items }: { contracts: Contract[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index contracts`,
            href: `/contracts`,
        },
    ];

    const [contracts, setContracts] = useState(items);

    const fetchContracts = async () => {
        try {
            const response = await axios.get(route('api.contracts.index'));
            if (response.data.status === 'success') {
                setContracts(response.data.data);
            }
        } catch (error) {
            console.log(error);
        }
    };

    console.log(contracts);
    const deleteContract = async (contract: Contract) => {
        try {
            const response = await axios.delete(route('api.contracts.destroy', contract.id));
            if (response.data.status === 'success') {
                console.log('Contract deleted');
                fetchContracts();
            }
        } catch (error) {
            console.log(error);
        }
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Assets" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route('tenant.contracts.create')}>
                    <Button>Create</Button>
                </a>
                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>Name</TableHeadData>
                            <TableHeadData>Type</TableHeadData>
                            <TableHeadData>Status</TableHeadData>
                            <TableHeadData>Renewal</TableHeadData>
                            <TableHeadData>Provider</TableHeadData>
                            <TableHeadData>Category</TableHeadData>
                            <TableHeadData>End date</TableHeadData>
                            <TableHeadData></TableHeadData>
                        </TableHeadRow>
                    </TableHead>

                    <TableBody>
                        {contracts &&
                            contracts.map((contract) => {
                                return (
                                    <TableBodyRow key={contract.id}>
                                        <TableBodyData>
                                            <a href={route(`tenant.contracts.show`, contract.id)}> {contract.name} </a>
                                        </TableBodyData>
                                        <TableBodyData>{contract.type}</TableBodyData>
                                        <TableBodyData>
                                            <span className="rounded-full bg-gray-500 p-2">{contract.status}</span>
                                        </TableBodyData>
                                        <TableBodyData>{contract.renewal_type}</TableBodyData>
                                        <TableBodyData>{contract.provider.name}</TableBodyData>
                                        <TableBodyData>{contract.provider.category}</TableBodyData>
                                        <TableBodyData>{contract.end_date}</TableBodyData>

                                        <TableBodyData>
                                            <a href={route(`tenant.contracts.show`, contract.id)}>
                                                <Button variant={'outline'}>See</Button>
                                            </a>
                                            <a href={route(`tenant.contracts.edit`, contract.id)}>
                                                <Button>Edit</Button>
                                            </a>
                                            <Button onClick={() => deleteContract(contract)} variant={'destructive'}>
                                                Delete
                                            </Button>
                                        </TableBodyData>
                                    </TableBodyRow>
                                );
                            })}
                    </TableBody>
                </Table>
            </div>
        </AppLayout>
    );
}
