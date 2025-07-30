import { Button } from '@/components/ui/button';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, TenantBuilding, TenantFloor, TenantSite } from '@/types';
import { Head, useForm } from '@inertiajs/react';

export default function IndexSites({ locations, routeName }: { locations: TenantSite[] | TenantBuilding[] | TenantFloor[]; routeName: string }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index ${routeName}`,
            href: `/${routeName}`,
        },
    ];

    const { delete: destroy } = useForm();

    const deleteLocation = (locationCode: string) => {
        destroy(route(`tenant.${routeName}.destroy`, locationCode));
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
                                        <TableBodyData>{item.maintainable.name}</TableBodyData>
                                        <TableBodyData>{item.maintainable.description}</TableBodyData>

                                        <TableBodyData>
                                            <Button onClick={() => deleteLocation(item.reference_code)} variant={'destructive'}>
                                                Delete
                                            </Button>
                                            <a href={route(`tenant.${routeName}.edit`, item.reference_code)}>
                                                <Button>Edit</Button>
                                            </a>
                                            <a href={route(`tenant.${routeName}.show`, item.reference_code)}>
                                                <Button variant={'outline'}>See</Button>
                                            </a>
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
