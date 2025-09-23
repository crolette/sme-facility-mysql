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
                            <TableHeadData>Name</TableHeadData>
                            <TableHeadData>Job position</TableHeadData>
                            <TableHeadData>Email</TableHeadData>
                            <TableHeadData>Can login</TableHeadData>
                            <TableHeadData>Role</TableHeadData>
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
                                        <TableBodyData>{item.job_position}</TableBodyData>
                                        <TableBodyData>
                                            <a href={`mailto:${item.email}`}>{item.email}</a>
                                        </TableBodyData>
                                        <TableBodyData>{item.can_login ? 'YES' : 'NO'}</TableBodyData>
                                        <TableBodyData>{(item.roles && item.roles.length > 0) ? item.roles[0].name : ''}</TableBodyData>
                                        <TableBodyData>
                                            {item.provider ? (
                                                <a href={route('tenant.providers.show', item.provider?.id)}>{item.provider?.name}</a>
                                            ) : (
                                                <p>Internal</p>
                                            )}
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
