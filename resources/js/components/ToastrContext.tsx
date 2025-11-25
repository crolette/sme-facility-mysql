// ToastContext.tsx
import { createContext, ReactNode, useContext, useState } from 'react';

interface ToastData {
    message: string;
    type: 'success' | 'error' | 'info' | 'warning';
}

interface ToastContextType {
    showToast: (message: string, type?: ToastData['type']) => void;
    clearToast: () => void;
    toastData: ToastData | null;
}

const ToastContext = createContext<ToastContextType | undefined>(undefined);

export const useToast = (): ToastContextType => {
    const context = useContext(ToastContext);
    if (!context) {
        throw new Error('useToast must be used within a ToastProvider');
    }
    return context;
};

interface ToastProviderProps {
    children: ReactNode;
}

export const ToastProvider = ({ children }: ToastProviderProps) => {
    const [toastData, setToastData] = useState<ToastData | null>(null);

    const clearToast = () => {
        setToastData(null);
    };

    const showToast = (message: string, type: ToastData['type'] = 'success') => {
        setToastData({ message, type });
    };

    return <ToastContext.Provider value={{ showToast, toastData, clearToast }}>{children}</ToastContext.Provider>;
};
