import { FlashType, SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { BiCheck, BiError, BiInfoCircle, BiXCircle } from 'react-icons/bi';
import { useToast } from './ToastrContext';

export default function Toastr() {
    const { toastData, clearToast } = useToast();
    const [visible, setVisible] = useState(true);
    const { flash } = usePage<SharedData>().props;
    const [currentMessage, setCurrentMessage] = useState<string | null>(null);

    console.log('TOAST');
    console.log(toastData);
    console.log(flash);

    useEffect(() => {
        if (flash?.message || toastData) {
            setCurrentMessage(flash?.message ?? toastData?.message);
            setVisible(true);
            const timer = setTimeout(() => {
                setVisible(false);
                setCurrentMessage(null);
                clearToast();
            }, 2000);

            return () => clearTimeout(timer);
        }
    }, [flash?.message, toastData]);

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
                'fixed top-4 right-4 z-50 mb-4 flex transform items-center rounded-lg p-4 text-sm shadow-lg transition-all duration-500 ease-in-out ' +
                typeClasses[flash.type ?? toastData?.type]
            }
            role="alert"
            key={currentMessage}
        >
            <span id="icon" className="mr-2">
                {icon[currentType]}
            </span>
            <span id="message">{currentMessage}</span>
        </div>
    );
}
