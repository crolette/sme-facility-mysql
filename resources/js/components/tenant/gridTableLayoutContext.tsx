import { createContext, ReactNode, useContext, useEffect, useState } from 'react';

type GridTableLayoutContextType = {
    layout: 'grid' | 'table';
    setLayout: (layout: 'grid' | 'table') => void;
};

type GridTableLayoutProviderProps = {
    children: ReactNode;
};

const SIDEBAR_COOKIE_NAME = 'index_layout';
const SIDEBAR_COOKIE_MAX_AGE = 60 * 60 * 24 * 7;

const GridTableLayoutContext = createContext<GridTableLayoutContextType | undefined>(undefined);

export const GridTableLayoutProvider = ({ children }: GridTableLayoutProviderProps) => {
    const cookieValue = document.cookie
        .split('; ')
        .find((row) => row.startsWith('index_layout='))
        ?.split('=')[1];

    const [layout, setLayout] = useState<'grid' | 'table'>(cookieValue === 'table' || cookieValue === 'grid' ? cookieValue : 'table');

    useEffect(() => {
        document.cookie = `${SIDEBAR_COOKIE_NAME}=${layout}; path=/; max-age=${SIDEBAR_COOKIE_MAX_AGE}`;
    }, [layout]);

    return <GridTableLayoutContext.Provider value={{ layout, setLayout }}>{children}</GridTableLayoutContext.Provider>;
};

export const useGridTableLayoutContext = () => {
    const context = useContext(GridTableLayoutContext);
    if (!context) throw new Error('Hook must be used within Provider');
    return context;
};
