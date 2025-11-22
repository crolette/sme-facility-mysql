import { usePage } from '@inertiajs/react';

export const usePermissions = () => {
    const { permissions } = usePage().props.auth;

    const hasPermission = (permission: string) => {
        return permissions.includes(permission);
    };

    const hasAnyPermission = (neededPermissions: string[]) => {
        return permissions.find((elem) => neededPermissions.includes(elem));
    };

    return { hasPermission, hasAnyPermission };
};
