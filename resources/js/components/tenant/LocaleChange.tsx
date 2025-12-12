import { router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';
import { useState } from 'react';
import Modale from '../Modale';

export default function LocaleChange({ url = 'tenant.locale', withModale = false }) {
    const { t, getLocales, currentLocale } = useLaravelReactI18n();

    const handleLocaleChange = (locale: string) => {
        router.visit(route(url, locale));
    };

    const [isProcessing, setIsProcessing] = useState(false);

    return (
        <>
            <label htmlFor="language" className="hidden">
                {t('common.language')}
            </label>

            <select
                id="language"
                value={currentLocale()}
                aria-label="Language change"
                onChange={(e) => {
                    setIsProcessing(true);
                    handleLocaleChange(e.target.value);
                }}
            >
                {getLocales().map((locale) => (
                    <option key={locale} value={locale} className="hover:bg-primary-50 block px-4 py-2">
                        {locale.toUpperCase()}
                    </option>
                ))}
            </select>
            {withModale && isProcessing && (
                <Modale
                    // message={
                    //     asset
                    //         ? t('actions.type-being-updated', { type: tChoice('assets.title', 1) })
                    //         : t('actions.type-being-submitted', { type: tChoice('assets.title', 1) })
                    // }
                    isOpen={isProcessing}
                    isProcessing={isProcessing}
                />
            )}
        </>
    );
}
