import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { Contract } from '@/types';
import { router } from '@inertiajs/core';
import axios from 'axios';
import { Loader, Pencil, PlusCircle, Trash2, Unlink } from 'lucide-react';
import { useEffect, useState } from 'react';
import Modale from '../Modale';
import ModaleForm from '../ModaleForm';
import { PaginationAPI } from '../pagination_api';
import SearchableInput from '../SearchableInput';
import { useToast } from '../ToastrContext';
import { Button } from '../ui/button';
import { Pill } from '../ui/pill';

interface ContractsList {
    getUrl: string;
    // items: ContractsPaginated;
    editable?: boolean;
    removable?: boolean;
    parameter?: string;
    contractableReference?: string | null;
    routeName?: string | null;
    canAdd?: boolean;
    // onContractsChange?: (contracts: Contract[]) => void;
}

export const ContractsList = ({
    getUrl,
    // items,
    editable = false,
    removable = false,
    canAdd = true,
    contractableReference = null,
    routeName = null,
    parameter = '',
    // onContractsChange,
}: ContractsList) => {
    const [items, setItems] = useState(null);
    const [isLoading, setIsLoading] = useState<boolean>();

    const [pageToLoad, setPageToLoad] = useState(1);

    const fetchContracts = async () => {
        setIsLoading(true);
        if (!contractableReference) return;

        try {
            const response = await axios.get(route(getUrl, { [parameter]: contractableReference, page: pageToLoad ?? null }));
            console.log(response);
            if (response.data.status === 'success') {
                setItems(response.data.data);
                setExistingContracts(response.data.data.data);
                setIsLoading(false);
            }
        } catch (error) {
            console.log(error);
            setIsLoading(false);
        }
    };

    useEffect(() => {
        fetchContracts();
    }, []);

    useEffect(() => {
        fetchContracts();
    }, [pageToLoad]);

    const [showDeleteModale, setShowDeleteModale] = useState<boolean>(false);
    const [contractToDelete, setContractToDelete] = useState<Contract | null>(null);

    const deleteContract = async () => {
        if (!contractToDelete) return;

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
    const { showToast } = useToast();

    const removeContract = async (contract_id: number) => {
        if (!contractableReference) return;

        try {
            const response = await axios.delete(route(`api.${routeName}.contracts.delete`, contractableReference), {
                data: { contract_id: contract_id },
            });
            if (response.data.status === 'success') {
                fetchContracts();
            }
        } catch {
            // console.log(error);
        }
    };
    const [existingContracts, setExistingContracts] = useState();

    const [addExistingContractModale, setAddExistingContractModale] = useState<boolean>(false);

    const addExistingContractToAsset = async () => {
        const contracts = {
            existing_contracts: existingContracts.map((elem) => elem.id),
        };

        try {
            const response = await axios.post(route(`api.${routeName}.contracts.delete`, contractableReference), contracts);
            if (response.data.status === 'success') {
                setAddExistingContractModale(false);
                fetchContracts();
                showToast(response.data.message, response.data.status);
            }
        } catch (error) {
            showToast(error.response.data.message, error.response.data.status);
        }
    };

    console.log(existingContracts);

    return (
        <>
            <div className="border-sidebar-border bg-sidebar rounded-md border p-4">
                <div className="flex items-center justify-between gap-2">
                    <h2>Contracts</h2>

                    {canAdd && (
                        <div className="space-y-2 space-x-4 sm:space-y-0">
                            <Button onClick={() => setAddExistingContractModale(true)}>
                                <PlusCircle />
                                Add existing contract
                            </Button>
                            <Button onClick={() => router.get(route('tenant.contracts.create'))}>
                                <PlusCircle />
                                Add new contract
                            </Button>
                        </div>
                    )}
                </div>
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
                            <TableHeadData>End date</TableHeadData>
                            {(editable || removable) && <TableHeadData></TableHeadData>}
                        </TableHeadRow>
                    </TableHead>
                    <TableBody>
                        {isLoading ? (
                            <TableBodyRow>
                                <TableBodyData>
                                    <p className="flex animate-pulse gap-2">
                                        <Loader />
                                        Loading...
                                    </p>
                                </TableBodyData>
                            </TableBodyRow>
                        ) : items !== null && items.data.length > 0 ? (
                            items.data.map((contract) => {
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
                                            <a href={route(`tenant.providers.show`, contract.provider?.id)}> {contract.provider?.name} </a>
                                        </TableBodyData>
                                        <TableBodyData>{contract.end_date}</TableBodyData>

                                        {(editable || removable) && (
                                            <TableBodyData className="flex space-x-2">
                                                {removable && (
                                                    <>
                                                        <Button onClick={() => removeContract(contract.id)} variant={'outline'}>
                                                            <Unlink />
                                                        </Button>
                                                    </>
                                                )}
                                                {editable && (
                                                    <>
                                                        <a href={route(`tenant.contracts.edit`, contract.id)}>
                                                            <Button>
                                                                <Pencil />
                                                            </Button>
                                                        </a>
                                                        <Button
                                                            onClick={() => {
                                                                setContractToDelete(contract);
                                                                setShowDeleteModale(true);
                                                            }}
                                                            variant={'destructive'}
                                                        >
                                                            <Trash2 />
                                                        </Button>
                                                    </>
                                                )}
                                            </TableBodyData>
                                        )}
                                    </TableBodyRow>
                                );
                            })
                        ) : (
                            <TableBodyRow key={0}>
                                <TableBodyData>No results...</TableBodyData>
                            </TableBodyRow>
                        )}
                    </TableBody>
                </Table>
                {items !== null && <PaginationAPI items={items} pageToLoad={setPageToLoad} />}

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
                {addExistingContractModale && (
                    <ModaleForm title={'Add Existing contract'}>
                        <SearchableInput<Contract>
                            multiple={true}
                            searchUrl={route('api.contracts.search')}
                            selectedItems={existingContracts}
                            getDisplayText={(contract) => contract.name}
                            getKey={(contract) => contract.id}
                            onSelect={(contracts) => {
                                setExistingContracts(contracts);
                            }}
                            placeholder="Search contracts..."
                        />
                        <div className="flex gap-4">
                            <Button
                                variant="secondary"
                                onClick={() => {
                                    setAddExistingContractModale(false);
                                    // setExistingContracts(contracts);
                                }}
                            >
                                Cancel
                            </Button>
                            <Button onClick={addExistingContractToAsset}>Add contract</Button>
                        </div>
                    </ModaleForm>
                )}
            </div>
        </>
    );
};
