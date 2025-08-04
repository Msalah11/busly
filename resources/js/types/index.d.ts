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

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    ziggy: Config & { location: string };
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    role: 'admin' | 'user';
    avatar?: string;
    email_verified_at: string | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}

export interface Bus {
    id: number;
    bus_code: string;
    capacity: number;
    type: 'Standard' | 'VIP';
    is_active: boolean;
    created_at: string;
    updated_at: string;
}

export interface Trip {
    id: number;
    origin: string;
    destination: string;
    departure_time: string;
    arrival_time: string;
    price: string;
    bus_id: number;
    is_active: boolean;
    available_seats?: number;
    created_at: string;
    updated_at: string;
    bus?: Bus;
}

export interface Reservation {
    id: number;
    reservation_code: string;
    user_id: number;
    trip_id: number;
    seats_count: number;
    total_price: string;
    status: 'confirmed' | 'cancelled';
    reserved_at: string;
    cancelled_at: string | null;
    created_at: string;
    updated_at: string;
    user?: User;
    trip?: Trip;
}
