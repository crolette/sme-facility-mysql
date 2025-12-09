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

export interface Error {
    [key: string]: [];
}

export interface PaginatedData {
    // data: Asset[] | Contract[];
    data: [];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: {
        url: string | null;
        label: string;
        active: boolean;
    }[];
}

export interface ProvidersPaginated extends PaginatedData {
    data: Provider[];
}

export interface AssetsPaginated extends PaginatedData {
    data: Asset[];
}

export interface ContractsPaginated extends PaginatedData {
    data: Contract[];
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon | null;
    isActive?: boolean;
    count?: number;
    canView?: boolean;
}

type FlashType = 'success' | 'error' | 'warning' | 'info';

export interface Flash {
    status: string;
    message: string;
    type: FlashType;
    errors?: string[];
}

export type LocationLevel = 'site' | 'building' | 'floor' | 'room';
export type TicketStatus = 'open' | 'ongoing' | 'closed';
export type CategoryTypeEnum = 'document' | 'intervention' | 'asset' | 'provider' | 'outdoor_materials' | 'floor_materials' | 'wall_materials';
export type PriorityLevel = 'low' | 'middle' | 'high' | 'urgent';
export type InterventionStatus = 'draft' | 'planned' | 'in_progress' | 'waiting_parts' | 'completed' | 'cancelled';

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
    need_maintenance: boolean;
    maintenance_frequency: string;
    next_maintenance_date: string;
    last_maintenance_date: string;

    maintenance_manager_id: number;
    maintainable: TenantSite | TenantBuilding | TenantFloor | TenantRoom | Asset;
    manager?: User;
    providers?: Provider[];
}

export interface TenantSite {
    id: number;
    name: string;
    address: string;
    description: string;
    code: string;
    label: string;
    qr_code: string;
    reference_code: string;
    surface_walls: number;
    wall_material: string;
    wall_material_id: number;
    wall_material_other: string;
    surface_floor: number;
    floor_material: string;
    floor_material_id: number;
    floor_material_other: string;
    category: string;
    level_id: number;
    location_type: LocationType;
    location_route: string;
    maintainable: Maintainable;
    contracts: Contract[];
    tickets: Ticket[];
    documents: Documents[];
}

export interface Company {
    logo: string;
    address: string;
    vat_number: string;
    phone_number: string;
    name: string;
}

export interface TenantBuilding extends TenantSite {
    site: TenantSite;
    level: TenantSite;
    level_path: string;
    surface_outdoor: number;
    outdoor_material: string;
    outdoor_material_id: number;
    outdoor_material_other: string;
}

export interface TenantFloor extends TenantSite {
    building: TenantBuilding;
    level: TenantBuilding;
    level_path: string;
}

export interface TenantRoom extends TenantSite {
    floor: TenantFloor;
    level: TenantFloor;
    level_path: string;
    height: number;
}

export interface Contract {
    id: number;
    name: string;
    type: string;
    internal_reference: string;
    provider_reference: string;
    start_date: string;
    end_date: string;
    renewal_type: string;
    status: string;
    notes: string;
    provider_id: number;
    provider_name: string;
    contract_duration: string;
    notice_period: string;
    notice_date: string;
    provider: Provider;
    documents?: Documents[];
}

export interface Asset {
    id: number;
    is_mobile: boolean;
    name: string;
    description: string;
    code: string;
    surface: number;
    qr_code: string;
    reference_code: string;
    location_id: number;
    location_type: string;
    model: string;
    brand: string;
    serial_number: string;
    has_meter_readings: boolean;
    meter_number: string;
    meter_unit: string;
    category: string;
    asset_category_id: number;
    tickets?: Ticket[];
    pictures?: Picture[];
    depreciable: boolean;
    depreciation_start_date: string;
    depreciation_end_date: string;
    depreciation_duration: number;
    contract_end_date: string;
    residual_value: number;
    documents?: Documents[];
    deleted_at?: string;
    created_at: string;
    updated_at?: string;
    level_path?: string;
    location_route: string;
    contracts: Contract[];
    asset_category: AssetCategory;
    maintainable: Maintainable;
    location: Partial<TenantSite | TenantBuilding | TenantFloor | TenantRoom | User>;
    meter_readings?: MeterReadings[];
}

export interface MeterReadings {
    id: number;
    meter: number;
    meter_date: string;
    user_id: number;
    user: User;
    asset_id: number;
    asset: Asset;
    notes: string;
}

export interface Picture {
    id: number;
    mime_type: string;
    filename: string;
    size: number;
    sizeMo: number;
    path: string;
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
    path: string;
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
    asset_code: string;
    description: string;
    reported_by?: number;
    closer?: User;
    reporter?: User;
    reporter_email?: string;
    being_notified: boolean;
    code: string;
    closed_at?: string;
    ticketable_route: string;
    created_at: string;
    updated_at: string;
    pictures?: Picture[];
    ticketable: TenantSite | TenantBuilding | TenantFloor | TenantRoom | Asset;
}

export interface Intervention {
    id: number;
    intervention_type_id: number;
    intervention_type: CentralType;
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
    maintainable: Maintainable;
    type: string;
    created_at: string;
    updated_at: string;
    assignable: Provider | User;
    assignable_id?: number;
    assignable_type?: string;
    interventionable_type: string;
    interventionable: TenantSite | TenantBuilding | TenantFloor | TenantRoom | Asset;
}

export interface InterventionAction {
    id: number;
    description: string;
    intervention_date?: string;
    action_type: CentralType;
    started_at?: string;
    finished_at?: string;
    updated_at: string;
    intervention_costs?: number;
    created_by?: User;
    updated_by?: User;
    creator?: User;
    updater?: User;
    creator_email?: string;
    type: string;
}

export interface AssetCategory {
    id: number;
    slug: string;
    label: string;
    translations: Translation[];
}

export interface Country {
    id: number;
    iso_code: string;
    name: string;
    label: string;
}

export interface CentralCountry {
    id: number;
    iso_code_a3: string;
    iso_code_a2: string;
    name: string;
}

export interface Provider {
    id: number;
    name: string;
    email: string;
    website: string;
    street: string;
    house_number?: string;
    postal_code: string;
    city: string;
    address: string;
    country_code: string;
    country_id: number;
    country: Country;
    vat_number: string;
    phone_number: string;
    logo?: string;
    logo_path?: string;
    categories: CentralType[];
    // category_type_id: number;
    users?: User[];
    assigned_interventions?: Intervention[];
    contracts?: Contract[];
}

export interface User {
    id: number;
    first_name: string;
    last_name: string;
    username: string;
    full_name: string;
    job_position?: string;
    phone_number: string;
    email: string;
    avatar?: string;
    provider_id?: number;
    provider?: Provider;
    can_login: boolean;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    roles: [];
    assets?: Asset[];
    assigned_interventions?: Intervention[];
    [key: string]: unknown;
}

export interface NotificationPreference {
    id: number;
    asset_type: string;
    notification_type: string;
    notification_delay_days: number;
    enabled: boolean;
    user_id: number;
}

export interface Role {
    id: number;
    name: string;
}

export interface Tenant {
    id: string;
    data: Array;
    first_name: string;
    last_name: string;
    company_name: string;
    email: string;
    vat_number: string;
    company_code: string;
    company_address: Address;
    invoice_address?: Address;
    full_company_address: string;
    full_invoice_address?: string;
    phone_number: string;
    stripe_id: string;
    verified_vat_status: string;
    trial_ends_at: string;
    domain: Domain;
    domain_address: string;
    max_sites: number;
    max_users: number;
    max_storage_gb: number;
    has_statistics: boolean;
    current_sites_count: number;
    current_users_count: number;
    current_storage_bytes: number;
    disk_size_gb: number;
    disk_size_mb: number;
    subscription_name?: string;
    subscription_plan?: string;
    active_subscription?: Subscription;
}

export interface Subscription {
    [key: string]: string | number;
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
    version: string;
    tenant: { name: string; logo: string };
    auth: Auth;
    flash: Flash;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    [key: string]: unknown;
}
