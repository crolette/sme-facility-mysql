import { LucideIcon } from 'lucide-react';
import type { Config } from 'ziggy-js';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

type FlashType = 'success' | 'error' | 'warning' | 'info';

export interface Flash {
    status?: string;
    message: string;
    type: FlashType;
}

export type LocationLevel = 'site' | 'building' | 'floor' | 'room';
export type TicketStatus = 'open' | 'ongoing' | 'closed';
export type CategoryTypeEnum = 'document' | 'intervention' | 'asset';
export type PriorityLevel = 'low' | 'middle' | 'high' | 'urgent';
export type InterventionStatus = 'draft' | 'planned' | 'in progress' | 'waiting for parts' | 'completed' | 'cancelled';

export interface Maintainable {
    id: number;
    name: string;
    description: string;
    purchase_date: string;
    purchase_cost: number;
    under_warranty: boolean;
    end_warranty_date: string;
    brand: string;
    model: string;
    serial_number: string;
}

export interface TenantSite {
    id: number;
    code: string;
    reference_code: string;
    category: string;
    level_id: number;
    location_type: LocationType;
    maintainable: Maintainable;
    tickets: Ticket[];
    documents: Documents[];
}

export interface TenantBuilding extends TenantSite {
    site: TenantSite;
}

export interface TenantFloor extends TenantSite {
    building: TenantBuilding;
}

export interface TenantRoom extends TenantSite {
    floor: TenantFloor;
}

export interface Asset {
    id: number;
    code: string;
    reference_code: string;
    location_id: number;
    location_type: string;
    model: string;
    brand: string;
    serial_number: string;
    category: string;
    asset_category_id: number;
    tickets?: Ticket[];
    pictures?: Picture[];
    documents?: Documents[];
    deleted_at?: string;
    created_at: string;
    updated_at?: string;
    asset_category: AssetCategory;
    maintainable: Maintainable;
    location: TenantSite | TenantBuilding | TenantFloor | TenantRoom;
}

export interface Picture {
    id: number;
    mime_type: string;
    filename: string;
    size: number;
    sizeMo: number;
    fullPath: string;
    created_at: string;
    uploaded_by?: User;
    uploaded_email?: string;
}

export interface Documents {
    id: number;
    name: string;
    mime_type: string;
    category: string;
    filename: string;
    description: string;
    sizeMo: number;
    category_type_id: number;
    created_at: string;
}

export interface CentralType {
    id: number;
    slug: string;
    category: string;
    label: string;
    translations: Translation[];
}

export interface Translation {
    id: number;
    label: string;
    locale: string;
}

export interface LocationType {
    id: number;
    slug: string;
    prefix: string;
    label: string;
    level: LocationLevel;
    translations: Translation[];
}

export interface Ticket {
    id: number;
    status: TicketStatus;
    description: string;
    reported_by?: number;
    closer?: User;
    reporter?: User;
    reporter_email?: string;
    being_notified: boolean;
    code: string;
    closed_at?: string;
    created_at: string;
    updated_at: string;
    pictures?: Picture[];
    ticketable: TenantSite | TenantBuilding | TenantFloor | TenantRoom | Asset;
}

export interface Intervention {
    id: number;
    intervention_type_id: number;
    priority: PriorityLevel;
    status: InterventionStatus;
    planned_at?: string;
    description: string;
    repair_delay?: string;
    total_costs?: number;
    ticket: Ticket;
    ticket_id?: number;
    interventionable_id?: number;
    actions?: InterventionAction[];
}

export interface InterventionAction {
    id: number;
    description: string;
    intervention_date?: string;
    started_at?: string;
    finished_at?: string;
    intervention_costs?: number;
    created_by?: User;
    updated_by?: User;
    creator?: User;
    updater?: User;
    creator_email?: string;
}

export interface AssetCategory {
    id: number;
    slug: string;
    label: string;
    translations: Translation[];
}

export interface User {
    id: number;
    first_name: string;
    last_name: string;
    username: string;
    full_name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown;
}

export interface Tenant {
    id: string;
    data: Array;
    company_name: string;
    email: string;
    vat_number: string;
    company_code: string;
    company_address: Address;
    invoice_address?: Address;
    full_company_address: string;
    full_invoice_address?: string;
    phone_number: string;
    domain: Domain;
}

export interface Domain {
    id: string;
    domain: string;
    tenant_id: string;
}

export interface Address {
    id?: string;
    street: string;
    house_number: string;
    zip_code: string;
    city: string;
    country: string;
}

export interface SharedData {
    name: string;
    tenantName: string;
    auth: Auth;
    flash: Flash;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    [key: string]: unknown;
}
