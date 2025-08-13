import { NavFooter } from '@/components/nav-footer';
import { NavMain } from '@/components/nav-main';
import { NavUser } from '@/components/nav-user';
import { Sidebar, SidebarContent, SidebarFooter, SidebarHeader, SidebarMenu, SidebarMenuButton, SidebarMenuItem } from '@/components/ui/sidebar';
import { type NavItem, type SharedData } from '@/types';
import { Link, usePage } from '@inertiajs/react';
import { BookOpen, Bus, Calendar, Folder, LayoutGrid, MapPin, Route, Shield, Ticket, Users } from 'lucide-react';
import AppLogo from './app-logo';

const getMainNavItems = (isAdmin: boolean): NavItem[] => {
    const items: NavItem[] = [
        {
            title: 'Dashboard',
            href: route(isAdmin ? 'admin.dashboard' : 'user.dashboard'),
            icon: LayoutGrid,
        },
    ];

    if (isAdmin) {
        items.push({
            title: 'Users Management',
            href: route('admin.users.index'),
            icon: Users,
        });
        items.push({
            title: 'Bus Management',
            href: route('admin.buses.index'),
            icon: Bus,
        });
        items.push({
            title: 'City Management',
            href: route('admin.cities.index'),
            icon: MapPin,
        });
        items.push({
            title: 'Trip Management',
            href: route('admin.trips.index'),
            icon: Route,
        });
        items.push({
            title: 'Reservation Management',
            href: route('admin.reservations.index'),
            icon: Calendar,
        });
    } else {
        items.push({
            title: 'Browse Trips',
            href: route('user.trips.index'),
            icon: Route,
        });
        items.push({
            title: 'My Reservations',
            href: route('user.reservations.index'),
            icon: Ticket,
        });
    }

    return items;
};

const footerNavItems: NavItem[] = [
    {
        title: 'Repository',
        href: 'https://github.com/laravel/react-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#react',
        icon: BookOpen,
    },
];

export function AppSidebar() {
    const { auth } = usePage<SharedData>().props;
    const isAdmin = auth.user.role === 'admin';
    const mainNavItems = getMainNavItems(isAdmin);

    return (
        <Sidebar collapsible="icon" variant="inset">
            <SidebarHeader>
                <SidebarMenu>
                    <SidebarMenuItem>
                        <SidebarMenuButton size="lg" asChild>
                            <Link href={route(isAdmin ? 'admin.dashboard' : 'user.dashboard')} prefetch>
                                <AppLogo />
                            </Link>
                        </SidebarMenuButton>
                    </SidebarMenuItem>
                </SidebarMenu>
            </SidebarHeader>

            <SidebarContent>
                <NavMain items={mainNavItems} />
            </SidebarContent>

            <SidebarFooter>
                <NavFooter items={footerNavItems} className="mt-auto" />
                <NavUser />
            </SidebarFooter>
        </Sidebar>
    );
}
