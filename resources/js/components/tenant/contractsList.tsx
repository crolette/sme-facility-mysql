import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { Contract } from '@/types';
import axios from 'axios';
import { useState } from 'react';
import { Button } from '../ui/button';
import Modale from '../Modale';
import { Pill } from '../ui/pill';

export const ContractsList = ({ items, editable = false, removable = false, contractableReference = null, routeName = null }: { items: Contract[]; editable ?: boolean; removable ?: boolean; contractableReference ?: string;  routeName?: string}) => {
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

    console.log(contractableReference);

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
                            {(editable || removable) && <TableHeadData></TableHeadData>}
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
                                            <Pill variant={contract.status}>{contract.status}</Pill>
                                        </TableBodyData>
                                        <TableBodyData>{contract.internal_reference}</TableBodyData>
                                        <TableBodyData>{contract.provider_reference}</TableBodyData>
                                        <TableBodyData>{contract.renewal_type}</TableBodyData>
                                        <TableBodyData>
                                            <a href={route(`tenant.providers.show`, contract.provider.id)}> {contract.provider.name} </a>
                                        </TableBodyData>
                                        <TableBodyData className="bg-">{contract.provider.category}</TableBodyData>
                                        <TableBodyData>{contract.end_date}</TableBodyData>

                                        {(editable || removable) && (
                                                <TableBodyData>
                                                    
                                                    {editable && (
                                                        <>
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
                                                        </>
                                                )}
                                                    {removable && (
                                                        <>
                                                            <Button
                                                            onClick={async () => {
                                                                try {
                                                                    const response = await axios.delete(
                                                                        route(`api.${routeName}.contracts.delete`, contractableReference),
                                                                        { data: { contract_id: contract.id } },
                                                                    );
                                                                    console.log(response.data)
                                                                } catch {
                                                                    console.log(error)
                                                                }
                                                              
                                                                }}
                                                                variant={'destructive'}
                                                            >
                                                                Remove
                                                            </Button>
                                                        </>
                                                    )}

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
