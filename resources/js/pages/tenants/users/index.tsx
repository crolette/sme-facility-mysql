import { Button } from '@/components/ui/button';
import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import AppLayout from '@/layouts/app-layout';
import { BreadcrumbItem, User } from '@/types';
import { Head } from '@inertiajs/react';

export default function UserIndex({ users }: { users: User[] }) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: `Index users`,
            href: `/users`,
        },
    ];

    console.log(users);

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Sites" />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
                <a href={route(`tenant.users.create`)}>
                    <Button>Create user</Button>
                </a>
                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>Company name</TableHeadData>
                            <TableHeadData>Email</TableHeadData>
                            <TableHeadData>Can login</TableHeadData>
                            <TableHeadData>Provider</TableHeadData>
                        </TableHeadRow>
                    </TableHead>
                    <TableBody>
                        {users &&
                            users.map((item, index) => {
                                return (
                                    <TableBodyRow key={index}>
                                        <TableBodyData>
                                            <a href={route('tenant.users.show', item.id)}>{item.full_name}</a>
                                        </TableBodyData>
                                        <TableBodyData>{item.email}</TableBodyData>
                                        <TableBodyData>{item.can_login}</TableBodyData>
                                        <TableBodyData>{item.provider?.name}</TableBodyData>
                                    </TableBodyRow>
                                );
                            })}
                    </TableBody>
                </Table>
            </div>
        </AppLayout>
    );
}
