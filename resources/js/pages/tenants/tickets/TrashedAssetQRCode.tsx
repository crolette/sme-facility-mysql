import { Asset } from '@/types';
import { Head } from '@inertiajs/react';
import { useLaravelReactI18n } from 'laravel-react-i18n';

export default function TrashedAssetQRCode({ item }: { item: Asset }) {
    const { t, tChoice } = useLaravelReactI18n();

    return (
        <>
            <Head title={t('actions.create-type', { type: tChoice('tickets.title', 1) })}>
                <meta name="robots" content="noindex, nofollow, noarchive, nosnippet" />
            </Head>
            <div className="bg-accent flex min-h-dvh items-center justify-center">
                <div className="border-sidebar-border bg-sidebar mx-auto flex w-11/12 flex-col gap-4 rounded-md border p-4 shadow-xl md:w-1/2">
                    <div>
                        <h1>{t('assets.trashed_description')}</h1>
                        <div>
                            <h3>{item.name}</h3>
                            <p>{item.description}</p>
                            <p>{item.category}</p>
                        </div>
                    </div>
                </div>
            </div>
        </>
    );
}
