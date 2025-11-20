import { useLaravelReactI18n } from 'laravel-react-i18n';
import { Loader } from 'lucide-react';

// Props pour Modal
interface ModaleProps {
    title?: string;
    children: React.ReactNode;
    isUpdating?: boolean;
}

export default function ModaleForm({ title, children, isUpdating = false }: ModaleProps) {
    const { t } = useLaravelReactI18n();
    return (
        <>
            <div className="bg-background/50 fixed inset-0 z-50 overflow-y-auto">
                <div className="bg-background/20 flex min-h-dvh items-center justify-center">
                    <div className="bg-background max-h-[90vh] overflow-y-auto p-10">
                        {isUpdating ? (
                            <>
                                <Loader className="animate-pulse text-center" />
                                <p className="mx-auto animate-pulse text-center text-3xl font-bold">{t('actions.updating')}.</p>
                            </>
                        ) : (
                            <>
                                <h4 className="lowercase first-letter:uppercase">{title}</h4>
                                {children}
                            </>
                        )}
                    </div>
                </div>
            </div>
        </>
    );
}
