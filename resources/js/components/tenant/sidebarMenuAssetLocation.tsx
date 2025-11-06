import { cn } from '@/lib/utils';
import { ChevronDown } from 'lucide-react';
import { useState } from 'react';
import { Pill } from '../ui/pill';

interface InfosProps {
    name: string;
    code: string;
    status?: string;
    reference?: string;
    levelPath: string;
    levelName: string;
    priority?: string;
}
interface SideBarMenuProps {
    activeTab: string;
    setActiveTab: (tab: string) => void;
    menu?: keyof typeof MENUS;
    infos: InfosProps;
}

const MENUS = {
    interventions: [
        {
            tabName: 'information',
            tabDisplay: 'Information',
        },
        {
            tabName: 'pictures',
            tabDisplay: 'pictures',
        },

        // {
        //     tabName: 'actions',
        //     tabDisplay: 'actions',
        // },
    ],
    user: [
        {
            tabName: 'information',
            tabDisplay: 'Information',
        },
        {
            tabName: 'interventions',
            tabDisplay: 'interventions',
        },
        {
            tabName: 'assets',
            tabDisplay: 'assets',
        },
    ],
    asset: [
        {
            tabName: 'information',
            tabDisplay: 'Information',
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
    ],
    provider: [
        {
            tabName: 'information',
            tabDisplay: 'Information',
        },
        {
            tabName: 'contracts',
            tabDisplay: 'contracts',
        },
        {
            tabName: 'interventions',
            tabDisplay: 'interventions',
        },
        {
            tabName: 'users',
            tabDisplay: 'users',
        },
        {
            tabName: 'assets',
            tabDisplay: 'assets',
        },
        {
            tabName: 'locations',
            tabDisplay: 'locations',
        },
    ],
    ticket: [
        {
            tabName: 'information',
            tabDisplay: 'Information',
        },
        // {
        //     tabName: 'pictures',
        //     tabDisplay: 'pictures',
        // },
        // {
        //     tabName: 'interventions',
        //     tabDisplay: 'interventions',
        // },
    ],
    contract: [
        {
            tabName: 'information',
            tabDisplay: 'Information',
        },
        {
            tabName: 'assets',
            tabDisplay: 'assets',
        },
        {
            tabName: 'documents',
            tabDisplay: 'documents',
        },
    ],
    location: [
        {
            tabName: 'information',
            tabDisplay: 'Information',
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
        {
            tabName: 'assets',
            tabDisplay: 'assets',
        },
    ],
};

export default function SidebarMenuAssetLocation({ activeTab, setActiveTab, menu = 'asset', infos }: SideBarMenuProps) {
    const navSidebar = MENUS[menu];

    const [showMobileMenu, setShowMobileMenu] = useState(false);
    return (
        <div className="bg-sidebar border-sidebar-border z-50 flex h-fit flex-col gap-2 rounded-md shadow-xl">
            <div className="flex flex-col items-center gap-1 px-4 py-2 text-center">
                <p className="font-semibold">{infos.name}</p>

                <p className="text-sm">{infos.code ?? ''}</p>

                {infos.status && <Pill variant={infos.status}>{infos.status}</Pill>}
                {infos.priority && <Pill variant={infos.priority}>{infos.priority}</Pill>}

                <p className="text-xs">{infos.reference ?? ''}</p>

                <a href={infos.levelPath} className="text-sm">
                    {infos.levelName}
                </a>
            </div>

            {/* MOBILE MENU */}
            <ul className="relative mb-2 lg:hidden">
                <li
                    className="bg-sidebar-accent flex cursor-pointer justify-between p-2 first-letter:uppercase"
                    onClick={() => setShowMobileMenu(!showMobileMenu)}
                >
                    {activeTab}
                    <ChevronDown />
                </li>
                {showMobileMenu && (
                    <div className="bg-sidebar shadow-accent absolute w-full rounded-b-md shadow-xl">
                        {navSidebar.map(
                            (nav, index) =>
                                nav.tabName !== activeTab && (
                                    <li
                                        key={index}
                                        onClick={() => {
                                            setActiveTab(nav.tabName);
                                            setShowMobileMenu(false);
                                        }}
                                        className={'hover:bg-accent cursor-pointer p-2 first-letter:uppercase'}
                                    >
                                        {nav.tabDisplay}
                                    </li>
                                ),
                        )}
                    </div>
                )}
            </ul>
            {/* DESKTOP MENU */}
            <ul className="mb-2 hidden flex-col lg:flex">
                {navSidebar.map((nav, index) => (
                    <li
                        key={index}
                        onClick={() => setActiveTab(nav.tabName)}
                        className={cn(
                            activeTab === nav.tabName ? 'bg-accent first-letter:uppercase' : '',
                            'cursor-pointer p-2 first-letter:uppercase',
                        )}
                    >
                        {nav.tabDisplay}
                    </li>
                ))}
            </ul>
        </div>
    );
}
