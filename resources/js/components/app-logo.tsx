import { SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

export default function AppLogo() {
    const tenant = usePage<SharedData>().props.tenant;

    return (
        <>
                {tenant.logo ? (
                    <img src={route('api.image.show', { path: tenant.logo })} className="h-full object-cover" />
                ) : (
                    <img src={tenant.logo} alt="" className="h-full object-cover" />
                    // <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
                )}
            {/* <div className="ml-1 grid flex-1 text-left text-sm"> */}
                <span className="mb-0.5 truncate leading-none font-semibold">{tenant.name}</span>
        </>
    );
}
