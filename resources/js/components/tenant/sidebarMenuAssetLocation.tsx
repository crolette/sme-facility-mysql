import { cn } from "@/lib/utils";
import { Asset, Contract, TenantBuilding, TenantFloor, TenantRoom, TenantSite } from "@/types";
import { ChevronDown } from "lucide-react";
import { useState } from "react";


export default function SidebarMenuAssetLocation({ item, activeTab, setActiveTab, menu = 'location', isAsset = false }: { item: TenantSite | TenantBuilding | TenantFloor | TenantRoom | Asset |Contract; activeTab: string; setActiveTab: (tab: string) => void; menu?: string;  isAsset? : boolean}) {
   

    let navSidebar = [
        {
            tabName: 'information',
            tabDisplay: 'Infos',
        },
        {
            tabName: 'maintenance',
            tabDisplay: 'Maintenance',
        },
        {
            tabName: 'providers',
            tabDisplay: 'providers',
        },
        {
            tabName: 'warranty',
            tabDisplay: 'warranty',
        },
        {
            tabName: 'pictures',
            tabDisplay: 'pictures',
        },
        {
            tabName: 'contracts',
            tabDisplay: 'contracts',
        },
        {
            tabName: 'documents',
            tabDisplay: 'documents',
        },
        {
            tabName: 'tickets',
            tabDisplay: 'tickets',
        },
        {
            tabName: 'interventions',
            tabDisplay: 'interventions',
        },
    ];

    if (menu === 'location')
        navSidebar = [...navSidebar, {
            tabName: 'assets',
            tabDisplay: 'assets',
        }]
    
    if (menu === 'contract')
        navSidebar = [
            {
                tabName: 'information',
                tabDisplay: 'Infos',
            },
            {
                tabName: 'assets',
                tabDisplay: 'assets',
            },
            {
                tabName: 'documents',
                tabDisplay: 'documents',
            },
        ];

    const [showMobileMenu, setShowMobileMenu] = useState(false);
    // FIXME Do not pass item as props as it is the complete model Asset/Location
    console.log(item);
    return (
        <div className="bg-sidebar border-sidebar-border flex h-fit flex-col gap-2 rounded-md shadow-xl">
            <div className="flex flex-col gap-1 px-4 py-2 text-center">
                <p className="font-semibold">{item.name}</p>

                <p className="text-sm">{item.code ?? item.internal_reference }</p>
                <p className="text-xs">{item.reference_code ?? item.type}</p>
                {isAsset ? (
                    <p className="text-sm">
                        {(item.location_id &&
                            item.is_mobile) ? (
                                <a href={route(`tenant.users.show`, item.location.id)}>{item.location.full_name}</a>
                            ) : (
                                <a href={route(`tenant.${item.location.location_type.level}s.show`, item.location.reference_code)}>
                                    {item.location.name}
                                </a>
                            )}
                    </p>
                ) : (
                        item.level && 
                    <a href={item.level_path}>{item.level.name}</a>
                )}
            </div>
            {/* MOBILE MENU */}
            <ul className="relative mb-2 lg:hidden">
                <li className="bg-sidebar-accent flex cursor-pointer justify-between p-2" onClick={() => setShowMobileMenu(!showMobileMenu)}>
                    {activeTab}
                    <ChevronDown />
                </li>
                {showMobileMenu && (
                    <div className="bg-sidebar absolute w-full">
                        {navSidebar.map((nav, index) => (
                            <li
                                key={index}
                                onClick={() => {
                                    setActiveTab(nav.tabName);
                                    setShowMobileMenu(false);
                                }}
                                className={'hover:bg-accent cursor-pointer p-2'}
                            >
                                {nav.tabDisplay}
                            </li>
                        ))}
                    </div>
                )}
            </ul>
            {/* DESKTOP MENU */}
            <ul className="mb-2 hidden flex-col lg:flex">
                {navSidebar.map((nav, index) => (
                    <li
                        key={index}
                        onClick={() => setActiveTab(nav.tabName)}
                        className={cn(activeTab === nav.tabName ? 'bg-accent' : '', 'cursor-pointer p-2')}
                    >
                        {nav.tabDisplay}
                    </li>
                ))}
            </ul>
        </div>
    );
}