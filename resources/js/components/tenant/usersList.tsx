import { Table, TableBody, TableBodyData, TableBodyRow, TableHead, TableHeadData, TableHeadRow } from '@/components/ui/table';
import { User } from '@/types';

export const UsersList = ({items
}: {items: User[] | undefined}) => {
        return (
        <>
            {items && items.length > 0 && (
                <Table>
                    <TableHead>
                        <TableHeadRow>
                            <TableHeadData>Name</TableHeadData>
                            <TableHeadData>Job Position</TableHeadData>
                            <TableHeadData>Email</TableHeadData>
                        </TableHeadRow>
                    </TableHead>
                    <TableBody>
                        {items &&
                            items.map((user) => {
                                return (
                                    <TableBodyRow key={user.id}>
                                        <TableBodyData>
                                            <a href={route(`tenant.users.show`, user.id)}> {user.full_name} </a>
                                        </TableBodyData>
                                        <TableBodyData>{user.job_position}</TableBodyData>
                                        <TableBodyData>
                                            <a href={`mailto:${user.email}`}>{user.email}</a>
                                        </TableBodyData>
                                    </TableBodyRow>
                                );
                            })}
                    </TableBody>
                </Table>
            )}
            
        </>
    );
};
