import { Button } from './ui/button';

export default function Modale({
    title,
    message,
    isOpen,
    onConfirm,
    onCancel,
}: {
    title: string;
    message: string;
    isOpen: boolean;
    onConfirm: () => void;
    onCancel: () => void;
}) {
    return (
        <>
            {isOpen && (
                <>
                    <div className="bg-background/50 fixed inset-0 z-50 overflow-y-auto">
                        <div className="bg-background/20 flex min-h-dvh items-center justify-center">
                            <div className="bg-background flex max-h-[90vh] items-center justify-center overflow-y-auto p-10 text-center md:max-w-1/3">
                                <div className="flex flex-col gap-4">
                                    <p className="text-destructive mx-auto text-3xl font-bold">{title}</p>
                                    <p className="mx-auto w-2/3">{message}</p>
                                    <div className="mx-auto flex gap-4">
                                        <Button variant={'secondary'} onClick={onCancel}>
                                            Cancel
                                        </Button>
                                        <Button variant={'destructive'} onClick={onConfirm}>
                                            Delete
                                        </Button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </>
            )}
        </>
    );
}
