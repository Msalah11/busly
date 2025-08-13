import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { type User } from '@/types';
import { Link } from '@inertiajs/react';
import { 
    Users, 
    UserPlus, 
    Shield, 
    Clock, 
    Bus, 
    Route, 
    Activity, 
    TrendingUp, 
    Calendar,
    MapPin
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

interface Bus {
    id: number;
    bus_code: string;
    type: 'Standard' | 'VIP';
    capacity: number;
    is_active: boolean;
    created_at: string;
    trips?: Array<{
        id: number;
        origin: string;
        destination: string;
        is_active: boolean;
    }>;
}

interface Trip {
    id: number;
    origin: string;
    destination: string;
    departure_time: string;
    price: number;
    bus_id: number;
    is_active: boolean;
    created_at: string;
    bus: {
        id: number;
        bus_code: string;
        type: 'Standard' | 'VIP';
    };
}

interface Stats {
    users: {
        total: number;
        admins: number;
        regular: number;
        recent: number;
    };
    buses: {
        total: number;
        active: number;
        inactive: number;
        standard: number;
        vip: number;
    };
    trips: {
        total: number;
        active: number;
        inactive: number;
        today: number;
        this_week: number;
    };
    reservations?: {
        total: number;
        confirmed: number;
        cancelled: number;
        today: number;
        this_week: number;
    };
}

interface AdminDashboardProps {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
    stats: Stats;
    recentBuses: Bus[];
    recentTrips: Trip[];
}

export default function AdminDashboard({ users, stats, recentBuses, recentTrips }: AdminDashboardProps) {
    const getInitials = useInitials();

    const formatTime = (time: string) => {
        if (!time || !time.includes(':')) return time;
        
        // Create a date object with today's date and the provided time
        const [hours, minutes] = time.split(':');
        const date = new Date();
        date.setHours(parseInt(hours, 10), parseInt(minutes, 10), 0, 0);
        
        return date.toLocaleTimeString('en-EG', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: false
        });
    };

    const formatPrice = (price: number) => {
        return new Intl.NumberFormat('en-EG', {
            style: 'currency',
            currency: 'EGP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(price);
    };

    const breadcrumbs = [
        { title: 'Dashboard', href: route('admin.dashboard') },
        { title: 'Admin Dashboard', href: route('admin.dashboard') },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />
            
            <div className="flex-1 space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Admin Dashboard</h1>
                        <p className="text-muted-foreground">
                            Manage users, buses, trips, and reservations
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Link href={route('admin.buses.index')}>
                            <Button variant="outline">
                                <Bus className="mr-2 h-4 w-4" />
                                Manage Buses
                            </Button>
                        </Link>
                        <Link href={route('admin.trips.index')}>
                            <Button variant="outline">
                                <Route className="mr-2 h-4 w-4" />
                                Manage Trips
                            </Button>
                        </Link>
                        <Link href={route('admin.reservations.index')}>
                            <Button variant="outline">
                                <Calendar className="mr-2 h-4 w-4" />
                                Manage Reservations
                            </Button>
                        </Link>
                        <Link href={route('admin.users.index')}>
                            <Button>
                                <UserPlus className="mr-2 h-4 w-4" />
                                Manage Users
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* User Stats Cards */}
                <div>
                    <h2 className="text-lg font-semibold mb-4">User Statistics</h2>
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Users</CardTitle>
                                <Users className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.users.total}</div>
                                <p className="text-xs text-muted-foreground">
                                    All registered users
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Admin Users</CardTitle>
                                <Shield className="h-4 w-4 text-red-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-red-600">{stats.users.admins}</div>
                                <p className="text-xs text-muted-foreground">
                                    Users with admin privileges
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Regular Users</CardTitle>
                                <Users className="h-4 w-4 text-blue-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-blue-600">{stats.users.regular}</div>
                                <p className="text-xs text-muted-foreground">
                                    Standard user accounts
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Recent Users</CardTitle>
                                <TrendingUp className="h-4 w-4 text-green-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-green-600">{stats.users.recent}</div>
                                <p className="text-xs text-muted-foreground">
                                    New users this week
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Bus Stats Cards */}
                <div>
                    <h2 className="text-lg font-semibold mb-4">Bus Statistics</h2>
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Buses</CardTitle>
                                <Bus className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.buses.total}</div>
                                <p className="text-xs text-muted-foreground">
                                    All buses in fleet
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Active Buses</CardTitle>
                                <Activity className="h-4 w-4 text-green-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-green-600">{stats.buses.active}</div>
                                <p className="text-xs text-muted-foreground">
                                    Ready for service
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Inactive Buses</CardTitle>
                                <Clock className="h-4 w-4 text-orange-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-orange-600">{stats.buses.inactive}</div>
                                <p className="text-xs text-muted-foreground">
                                    Under maintenance
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Standard Buses</CardTitle>
                                <Bus className="h-4 w-4 text-blue-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-blue-600">{stats.buses.standard}</div>
                                <p className="text-xs text-muted-foreground">
                                    Standard comfort
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">VIP Buses</CardTitle>
                                <Shield className="h-4 w-4 text-purple-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-purple-600">{stats.buses.vip}</div>
                                <p className="text-xs text-muted-foreground">
                                    Premium comfort
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Trip Stats Cards */}
                <div>
                    <h2 className="text-lg font-semibold mb-4">Trip Statistics</h2>
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Trips</CardTitle>
                                <Route className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.trips.total}</div>
                                <p className="text-xs text-muted-foreground">
                                    All scheduled trips
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Active Trips</CardTitle>
                                <Activity className="h-4 w-4 text-green-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-green-600">{stats.trips.active}</div>
                                <p className="text-xs text-muted-foreground">
                                    Available for booking
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Inactive Trips</CardTitle>
                                <Clock className="h-4 w-4 text-orange-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-orange-600">{stats.trips.inactive}</div>
                                <p className="text-xs text-muted-foreground">
                                    Cancelled or past
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Today's Trips</CardTitle>
                                <Calendar className="h-4 w-4 text-blue-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-blue-600">{stats.trips.today}</div>
                                <p className="text-xs text-muted-foreground">
                                    Created today
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">This Week</CardTitle>
                                <TrendingUp className="h-4 w-4 text-purple-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-purple-600">{stats.trips.this_week}</div>
                                <p className="text-xs text-muted-foreground">
                                    Created this week
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Reservation Stats Cards */}
                <div>
                    <h2 className="text-lg font-semibold mb-4">Reservation Statistics</h2>
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Reservations</CardTitle>
                                <Calendar className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.reservations?.total ?? 0}</div>
                                <p className="text-xs text-muted-foreground">
                                    All reservations made
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Confirmed</CardTitle>
                                <Activity className="h-4 w-4 text-green-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-green-600">{stats.reservations?.confirmed ?? 0}</div>
                                <p className="text-xs text-muted-foreground">
                                    Active reservations
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Cancelled</CardTitle>
                                <Clock className="h-4 w-4 text-red-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-red-600">{stats.reservations?.cancelled ?? 0}</div>
                                <p className="text-xs text-muted-foreground">
                                    Cancelled bookings
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Today's Reservations</CardTitle>
                                <TrendingUp className="h-4 w-4 text-blue-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-blue-600">{stats.reservations?.today ?? 0}</div>
                                <p className="text-xs text-muted-foreground">
                                    Made today
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">This Week</CardTitle>
                                <Users className="h-4 w-4 text-purple-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-purple-600">{stats.reservations?.this_week ?? 0}</div>
                                <p className="text-xs text-muted-foreground">
                                    Made this week
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Recent Activity Grid */}
                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {/* Recent Users */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Users</CardTitle>
                            <CardDescription>
                                Latest user registrations
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {users.data.slice(0, 5).map((user) => (
                                    <div key={user.id} className="flex items-center space-x-4">
                                        <Avatar className="h-9 w-9">
                                            <AvatarImage src={user.avatar} alt={user.name} />
                                            <AvatarFallback>
                                                {getInitials(user.name)}
                                            </AvatarFallback>
                                        </Avatar>
                                        <div className="flex-1 space-y-1">
                                            <p className="text-sm font-medium leading-none">
                                                {user.name}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {user.email}
                                            </p>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Badge 
                                                variant={user.role === 'admin' ? 'default' : 'secondary'}
                                                className={user.role === 'admin' ? 'bg-red-600' : ''}
                                            >
                                                {user.role}
                                            </Badge>
                                            {user.email_verified_at && (
                                                <Badge variant="outline" className="text-green-600 border-green-600">
                                                    Verified
                                                </Badge>
                                            )}
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-4 pt-4 border-t">
                                <Link href={route('admin.users.index')}>
                                    <Button variant="outline" className="w-full">
                                        <Users className="mr-2 h-4 w-4" />
                                        View All Users
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Recent Buses */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Buses</CardTitle>
                            <CardDescription>
                                Latest bus additions
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recentBuses.map((bus) => (
                                    <div key={bus.id} className="flex items-center space-x-4">
                                        <div className="flex h-9 w-9 items-center justify-center rounded-md bg-primary/10">
                                            <Bus className="h-4 w-4 text-primary" />
                                        </div>
                                        <div className="flex-1 space-y-1">
                                            <p className="text-sm font-medium leading-none">
                                                {bus.bus_code}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {bus.type} • {bus.capacity} seats
                                            </p>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Badge 
                                                variant={bus.type === 'VIP' ? 'default' : 'secondary'}
                                                className={bus.type === 'VIP' ? 'bg-purple-600' : ''}
                                            >
                                                {bus.type}
                                            </Badge>
                                            <Badge 
                                                variant={bus.is_active ? 'outline' : 'secondary'}
                                                className={bus.is_active ? 'text-green-600 border-green-600' : 'text-orange-600'}
                                            >
                                                {bus.is_active ? 'Active' : 'Inactive'}
                                            </Badge>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-4 pt-4 border-t">
                                <Link href={route('admin.buses.index')}>
                                    <Button variant="outline" className="w-full">
                                        <Bus className="mr-2 h-4 w-4" />
                                        View All Buses
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Recent Trips */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Trips</CardTitle>
                            <CardDescription>
                                Latest trip schedules
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recentTrips.map((trip) => (
                                    <div key={trip.id} className="flex items-center space-x-4">
                                        <div className="flex h-9 w-9 items-center justify-center rounded-md bg-primary/10">
                                            <MapPin className="h-4 w-4 text-primary" />
                                        </div>
                                        <div className="flex-1 space-y-1">
                                            <p className="text-sm font-medium leading-none">
                                                {trip.origin} → {trip.destination}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {formatTime(trip.departure_time)} • {trip.bus.bus_code} • {formatPrice(trip.price)}
                                            </p>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Badge 
                                                variant={trip.bus.type === 'VIP' ? 'default' : 'secondary'}
                                                className={trip.bus.type === 'VIP' ? 'bg-purple-600' : ''}
                                            >
                                                {trip.bus.type}
                                            </Badge>
                                            <Badge 
                                                variant={trip.is_active ? 'outline' : 'secondary'}
                                                className={trip.is_active ? 'text-green-600 border-green-600' : 'text-orange-600'}
                                            >
                                                {trip.is_active ? 'Active' : 'Inactive'}
                                            </Badge>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-4 pt-4 border-t">
                                <Link href={route('admin.trips.index')}>
                                    <Button variant="outline" className="w-full">
                                        <Route className="mr-2 h-4 w-4" />
                                        View All Trips
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
} 