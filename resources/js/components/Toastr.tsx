import { FlashType, SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { BiCheck, BiError, BiInfoCircle, BiXCircle } from 'react-icons/bi';

interface ToastData {
    message: string;
    type: 'success' | 'error' | 'info' | 'warning';
}

interface ToastrProps {
    toastData?: ToastData | null;
}

export default function Toastr({ toastData }: ToastrProps) {
    const [visible, setVisible] = useState(true);
    const { flash } = usePage<SharedData>().props;

    console.log('TOASTR', toastData);

    useEffect(() => {
        if (flash?.message) {
            setVisible(true);
            const timer = setTimeout(() => setVisible(false), 4000);
            return () => clearTimeout(timer);
        }
    }, [flash?.message]);

    // Gérer les messages du context
    useEffect(() => {
        if (toastData?.message) {
            setVisible(true);
            const timer = setTimeout(() => setVisible(false), 4000);
            return () => clearTimeout(timer);
        }
    }, [toastData]);

    const currentMessage = toastData?.message || flash?.message;
    const currentType = toastData?.type || flash?.type;

    if (!visible || !currentMessage) return null; // Don't render if no message

    const typeClasses: Record<FlashType, string> = {
        success: ' bg-green-500 text-white ',
        error: ' bg-red-500 text-white ',
        warning: ' bg-yellow-500 text-gray-900 ',
        info: ' bg-blue-500 text-white ',
    };

    const icon = {
        success: <BiCheck />,
        error: <BiError />,
        warning: <BiXCircle />,
        info: <BiInfoCircle />,
    };

    return (
        <div
            id="notification"
            className={
                'fixed top-4 right-4 mb-4 flex transform items-center rounded-lg p-4 text-sm shadow-lg transition-all duration-500 ease-in-out ' +
                typeClasses[flash.type]
            }
            role="alert"
            key={flash?.message}
        >
            <span id="icon" className="mr-2">
                {icon[flash.type]}
            </span>
            <span id="message">{flash.message}</span>
        </div>
    );
}
