import { useCallback } from 'react';

export function useInitials() {
    return useCallback((first_name: string, last_name: string): string => {
        const firstInitial = first_name.charAt(0);
        const lastInitial = last_name.charAt(0);

        return `${firstInitial}${lastInitial}`.toUpperCase();
    }, []);
}
