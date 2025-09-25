import { Button } from '@/components/ui/button';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, Provider } from '@/types';
import { Head } from '@inertiajs/react';
import { Pencil, PlusCircle } from 'lucide-react';

export default function ProviderIndex({ providers }: { providers: Provider[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index providers`,
            href: `/providers`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sites" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route(`tenant.providers.create`)}>
                    <Button>
                        <PlusCircle />
                        Create provider</Button>
                </a>
                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>Company name</TableHeadData>
                            <TableHeadData>Category</TableHeadData>
                            <TableHeadData>Phone number</TableHeadData>
                            <TableHeadData>Email</TableHeadData>
                            <TableHeadData></TableHeadData>
                        </TableHeadRow>
                    </TableHead>
                    <TableBody>
                        {providers &&
                            providers.map((item, index) => {
                                return (
                                    <TableBodyRow key={index}>
                                        <TableBodyData>
                                            <a href={route('tenant.providers.show', item.id)}>{item.name}</a>
                                        </TableBodyData>
                                        <TableBodyData>{item.category ?? ''}</TableBodyData>
                                        <TableBodyData>{item.phone_number}</TableBodyData>
                                        <TableBodyData>{item.email}</TableBodyData>

                                        <TableBodyData>
                                            {/* <Button onClick={() => deleteLocation(item.reference_code)} variant={'destructive'}>
                                                Delete
                                            </Button> */}
                                            <a href={route(`tenant.providers.edit`, item.id)}>
                                                <Button><Pencil /></Button>
                                            </a>
                                            {/* <a href={route(`tenant.providers.show`, item.id)}>
                                                <Button variant={'outline'}>See</Button>
                                            </a> */}
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
