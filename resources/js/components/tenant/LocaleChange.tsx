import { router } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function LocaleChange() {
    const { t, getLocales, currentLocale } = useLaravelReactI18n();

    const handleLocaleChange = (locale) => {
        router.visit(route('tenant.locale', locale));
    };

    console.log(currentLocale());

    return (
        <>
            <label htmlFor="language" className="hidden">
                {t('common.language')}
            </label>

            <select value={currentLocale()} onChange={(e) => handleLocaleChange(e.target.value)}>
                {getLocales().map((locale) => (
                    <option key={locale} value={locale} className="hover:bg-primary-50 block px-4 py-2">
                        {locale.toUpperCase()}
                    </option>
                ))}
            </select>
        </>
    );
}
