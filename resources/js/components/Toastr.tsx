import { FlashType, SharedData } from '@/types';
import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import { BiCheck, BiError, BiInfoCircle, BiXCircle } from 'react-icons/bi';

export default function Toastr() {
    const [visible, setVisible] = useState(true);
    const { flash } = usePage<SharedData>().props;

    useEffect(() => {
        if (flash?.message) {
            setVisible(true);

            // Hide after 3 seconds
            const timer = setTimeout(() => setVisible(false), 3000);

            return () => clearTimeout(timer); // Cleanup on unmount
        }
    }, [flash]);

    if (!visible || !flash?.message) return null; // Don't render if no message

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

    console.log(flash);
    console.log('TOASTR');

    return (
        <>
            <div
                id="notification"
                className={
                    'fixed top-4 right-4 mb-4 flex transform items-center rounded-lg p-4 text-sm shadow-lg transition-all duration-500 ease-in-out ' +
                    typeClasses[flash.type]
                }
                role="alert"
            >
                <span id="icon" className="mr-2">
                    {icon[flash.type]}
                </span>
                <span id="message">{flash.message}</span>
            </div>
        </>
    );
}
