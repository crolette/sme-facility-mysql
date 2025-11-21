import { cn } from '@/lib/utils';
import { useLaravelReactI18n } from 'laravel-react-i18n';
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
    menu?: string;
    infos: InfosProps;
}

export default function SidebarMenuAssetLocation({ activeTab, setActiveTab, menu = 'asset', infos }: SideBarMenuProps) {
    const { t, tChoice } = useLaravelReactI18n();
    const [showMobileMenu, setShowMobileMenu] = useState(false);
    const [activeTabDisplay, setActiveTabDisplay] = useState(t('common.information'));

    const MENUS = {
        interventions: [
            {
                tabName: 'information',
                tabDisplay: t('common.information'),
            },
            {
                tabName: 'pictures',
                tabDisplay: tChoice('common.pictures', 2),
            },

            // {
            //     tabName: 'actions',
            //     tabDisplay: 'actions',
            // },
        ],
        user: [
            {
                tabName: 'information',
                tabDisplay: t('common.information'),
            },
            {
                tabName: 'interventions',
                tabDisplay: tChoice('interventions.title', 2),
            },
            {
                tabName: 'assets',
                tabDisplay: tChoice('assets.title', 2),
            },
        ],
        asset: [
            {
                tabName: 'information',
                tabDisplay: t('common.information'),
            },
            {
                tabName: 'maintenance',
                tabDisplay: tChoice('maintenances.title', 2),
            },
            {
                tabName: 'providers',
                tabDisplay: tChoice('providers.title', 2),
            },
            {
                tabName: 'warranty',
                tabDisplay: t('common.warranty'),
            },
            {
                tabName: 'pictures',
                tabDisplay: tChoice('common.pictures', 2),
            },
            {
                tabName: 'contracts',
                tabDisplay: tChoice('contracts.title', 2),
            },
            {
                tabName: 'documents',
                tabDisplay: tChoice('documents.title', 2),
            },
            {
                tabName: 'tickets',
                tabDisplay: tChoice('tickets.title', 2),
            },
            {
                tabName: 'interventions',
                tabDisplay: tChoice('interventions.title', 2),
            },
        ],
        provider: [
            {
                tabName: 'information',
                tabDisplay: t('common.information'),
            },
            {
                tabName: 'contracts',
                tabDisplay: tChoice('contracts.title', 2),
            },
            {
                tabName: 'interventions',
                tabDisplay: tChoice('interventions.title', 2),
            },
            {
                tabName: 'users',
                tabDisplay: tChoice('contacts.title', 2),
            },
            {
                tabName: 'assets',
                tabDisplay: tChoice('assets.title', 2),
            },
            {
                tabName: 'locations',
                tabDisplay: tChoice('locations.location', 2),
            },
        ],
        ticket: [
            {
                tabName: 'information',
                tabDisplay: t('common.information'),
            },
            // {
            //     tabName: 'pictures',
            //     tabDisplay: 'pictures',
            // },
            // {
            //     tabName: 'interventions',
            //     tabDisplay: tChoice('interventions.title',2),
            // },
        ],
        contract: [
            {
                tabName: 'information',
                tabDisplay: t('common.information'),
            },
            {
                tabName: 'assets',
                tabDisplay: tChoice('assets.title', 2),
            },
            {
                tabName: 'documents',
                tabDisplay: tChoice('documents.title', 2),
            },
        ],
        location: [
            {
                tabName: 'information',
                tabDisplay: t('common.information'),
            },
            {
                tabName: 'maintenance',
                tabDisplay: tChoice('maintenances.title', 2),
            },
            {
                tabName: 'providers',
                tabDisplay: tChoice('providers.title', 2),
            },
            {
                tabName: 'warranty',
                tabDisplay: t('common.warranty'),
            },
            {
                tabName: 'pictures',
                tabDisplay: tChoice('common.pictures', 2),
            },
            {
                tabName: 'contracts',
                tabDisplay: tChoice('contracts.title', 2),
            },
            {
                tabName: 'documents',
                tabDisplay: tChoice('documents.title', 2),
            },
            {
                tabName: 'tickets',
                tabDisplay: tChoice('tickets.title', 2),
            },
            {
                tabName: 'interventions',
                tabDisplay: tChoice('interventions.title', 2),
            },
            {
                tabName: 'assets',
                tabDisplay: tChoice('assets.title', 2),
            },
        ],
    };
    const navSidebar = MENUS[menu];

    return (
        <div className="bg-sidebar border-sidebar-border z-20 flex h-fit flex-col gap-2 rounded-md shadow-xl">
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
                    {activeTabDisplay}
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
                                            setActiveTabDisplay(nav.tabDisplay);
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
                        onClick={() => {
                            setActiveTabDisplay(nav.tabDisplay);
                            setActiveTab(nav.tabName);
                        }}
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
