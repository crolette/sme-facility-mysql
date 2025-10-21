import { Loader } from 'lucide-react';

// Props pour Modal
interface ModaleProps {
    title?: string;
    children: React.ReactNode;
    isUpdating?: boolean;
}

export default function ModaleForm({ title, children, isUpdating = false }: ModaleProps) {
    return (
        <>
            <div className="bg-background/50 fixed inset-0 z-50 overflow-y-auto">
                <div className="bg-background/20 flex min-h-dvh items-center justify-center">
                    <div className="bg-background max-h-[90vh] overflow-y-auto p-10">
                        {isUpdating ? (
                            <>
                                <Loader className="animate-pulse text-center" />
                                <p className="mx-auto animate-pulse text-center text-3xl font-bold">Updating...</p>
                            </>
                        ) : (
                            <>
                                <h4>{title}</h4>
                                {children}
                            </>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
