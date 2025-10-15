import { SharedData } from '@/types';
import { usePage } from '@inertiajs/react';

export default function AppLogo() {
    const tenant = usePage<SharedData>().props.tenant;
    console.log(tenant);
    return (
        <>
            {tenant.logo ? (
                <img src={route('api.image.show', { path: tenant.logo })} className="h-full object-cover" />
            ) : (
                <img src="/images/logo.png" alt="" className="h-full object-cover" />
                // <AppLogoIcon className="size-5 fill-current text-white dark:text-black" />
            )}
            {/* <div className="ml-1 grid flex-1 text-left text-sm"> */}
            <p className="block">{tenant.name}</p>
        </>
    );
}
