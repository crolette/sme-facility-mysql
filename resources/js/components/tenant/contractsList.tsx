import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { Contract } from '@/types';
import axios from 'axios';
import { useState } from 'react';
import { Button } from '../ui/button';
import Modale from '../Modale';

export const ContractsList = ({ items, editable = false }: { items: Contract[]; editable?: boolean }) => {
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

    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
    const [contractToDelete, setContractToDelete] = useState<Contract | null>(null);

    const deleteContract = async () => {
        if (!contractToDelete)
            return;

        try {
            const response = await axios.delete(route('api.contracts.destroy', contractToDelete.id));
            if (response.data.status === 'success') {
                fetchContracts();
                setShowDeleteModale(false);
            }
        } catch (error) {
            console.log(error);
        }
    };

    return (
        <>
            {contracts && contracts.length > 0 && (
                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>Name</TableHeadData>
                            <TableHeadData>Type</TableHeadData>
                            <TableHeadData>Status</TableHeadData>
                            <TableHeadData>Internal #</TableHeadData>
                            <TableHeadData>Provider #</TableHeadData>
                            <TableHeadData>Renewal</TableHeadData>
                            <TableHeadData>Provider</TableHeadData>
                            <TableHeadData>Category</TableHeadData>
                            <TableHeadData>End date</TableHeadData>
                            {editable && <TableHeadData></TableHeadData>}
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
                                        <TableBodyData>{contract.internal_reference}</TableBodyData>
                                        <TableBodyData>{contract.provider_reference}</TableBodyData>
                                        <TableBodyData>{contract.renewal_type}</TableBodyData>
                                        <TableBodyData>
                                            <a href={route(`tenant.providers.show`, contract.provider.id)}> {contract.provider.name} </a>
                                        </TableBodyData>
                                        <TableBodyData>{contract.provider.category}</TableBodyData>
                                        <TableBodyData>{contract.end_date}</TableBodyData>

                                        {editable && (
                                            <TableBodyData>
                                                <a href={route(`tenant.contracts.show`, contract.id)}>
                                                    <Button variant={'outline'}>See</Button>
                                                </a>
                                                <a href={route(`tenant.contracts.edit`, contract.id)}>
                                                    <Button>Edit</Button>
                                                </a>
                                                <Button
                                                    onClick={() => {
                                                        setContractToDelete(contract);
                                                        setShowDeleteModale(true);
                                                    }}
                                                    variant={'destructive'}
                                                >
                                                    Delete
                                                </Button>
                                            </TableBodyData>
                                        )}
                                    </TableBodyRow>
                                );
                            })}
                    </TableBody>
                </Table>
            )}
             <Modale
                            title={'Delete contract'}
                            message={`Are you sure you want to delete this contract ${contractToDelete?.name} ?`}
                            isOpen={showDeleteModale}
                            onConfirm={deleteContract}
                            onCancel={() => {
                                setShowDeleteModale(false);
                                setContractToDelete(null);
                            }}
                        />
        </>
    );
};
