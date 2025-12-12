import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Loader } from 'lucide-react';
import { Button } from './ui/button';

export default function Modale({
    title,
    message,
    isOpen,
    isProcessing = false,
    isUpdating = false,
    onConfirm,
    onCancel,
}: {
    title?: string;
    message?: string;
    isOpen: boolean;
    isProcessing?: boolean;
    isUpdating?: boolean;
    onConfirm?: () => void;
    onCancel?: () => void;
}) {
    const { t } = useLaravelReactI18n();
    return (
        <>
            {isOpen && (
                <>
                    <div className="bg-background/50 fixed inset-0 z-50 overflow-y-auto">
                        <div className="bg-background/20 flex min-h-dvh items-center justify-center">
                            <div className="bg-background flex max-h-[90vh] items-center justify-center overflow-y-auto p-10 text-center md:max-w-1/3">
                                <div className="flex flex-col items-center justify-center gap-4 text-center">
                                    {isProcessing && (
                                        <>
                                            <Loader className="animate-pulse" />
                                            <p className="mx-auto animate-pulse text-3xl font-bold">{t('actions.processing')}</p>
                                            <p className="mx-auto w-2/3">{message}</p>
                                        </>
                                    )}
                                    {isUpdating && (
                                        <>
                                            <Loader className="animate-pulse text-center" />
                                            <p className="mx-auto animate-pulse text-3xl font-bold">{t('actions.updating')}</p>
                                            <p className="mx-auto w-2/3">{message}</p>
                                        </>
                                    )}
                                    {!isProcessing && !isUpdating && (
                                        <>
                                            <p className="text-destructive mx-auto text-3xl font-bold lowercase first-letter:uppercase">{title}</p>
                                            <p className="mx-auto w-2/3">{message}</p>
                                            <div className="mx-auto flex gap-4">
                                                <Button variant={'secondary'} onClick={onCancel}>
                                                    {t('actions.cancel')}
                                                </Button>
                                                <Button variant={'destructive'} onClick={onConfirm}>
                                                    {t('actions.delete')}
                                                </Button>
                                            </div>
                                        </>
                                    )}
                                </div>
                            </div>
                        </div>
                    </div>
                </>
            )}
        </>
    );
}
