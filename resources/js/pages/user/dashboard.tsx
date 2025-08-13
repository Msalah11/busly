import { Head, Link } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { 
    Calendar, 
    MapPin, 
    Bus, 
    Users,
    Activity,
    Route as RouteIcon,
    Ticket,
    CheckCircle,
    XCircle
} from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

interface Trip {
    id: number;
    origin_city: { id: number; name: string } | null;
    destination_city: { id: number; name: string } | null;
    departure_time: string;
    price: string;
    bus: { id: number; bus_code: string; capacity: number; type: 'Standard' | 'VIP' } | null;
    available_seats?: number;
}

interface Reservation {
    id: number;
    reservation_code: string;
    seats_count: number;
    total_price: string;
    status: 'confirmed' | 'cancelled';
    reserved_at: string;
    trip: {
        id: number;
        origin_city: { id: number; name: string } | null;
        destination_city: { id: number; name: string } | null;
        departure_time: string;
        price: string;
        bus: { id: number; bus_code: string; type: 'Standard' | 'VIP' } | null;
    } | null;
}

interface Stats {
    total_reservations: number;
    upcoming_reservations: number;
    completed_trips: number;
    cancelled_reservations: number;
}

interface UserDashboardProps {
    recentReservations: Reservation[];
    upcomingReservations: Reservation[];
    availableTrips: Trip[];
    stats: Stats;
}

export default function UserDashboard({ 
    recentReservations, 
    upcomingReservations, 
    availableTrips, 
    stats 
}: UserDashboardProps) {
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

    const formatPrice = (price: string | number) => {
        return new Intl.NumberFormat('en-EG', {
            style: 'currency',
            currency: 'EGP',
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }).format(typeof price === 'string' ? parseFloat(price) : price);
    };

    const getStatusBadge = (status: string) => {
        switch (status) {
            case 'confirmed':
                return <Badge variant="default" className="bg-green-100 text-green-800 hover:bg-green-100">Confirmed</Badge>;
            case 'cancelled':
                return <Badge variant="destructive">Cancelled</Badge>;
            default:
                return <Badge variant="secondary">{status}</Badge>;
        }
    };

    const breadcrumbs = [
        { title: 'Dashboard', href: route('user.dashboard') },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            
            <div className="flex-1 space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Dashboard</h1>
                        <p className="text-muted-foreground">
                            Welcome back! Here's an overview of your travel activity.
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Link href={route('user.trips.index')}>
                            <Button variant="outline">
                                <RouteIcon className="mr-2 h-4 w-4" />
                                Browse Trips
                            </Button>
                        </Link>
                        <Link href={route('user.reservations.index')}>
                            <Button>
                                <Ticket className="mr-2 h-4 w-4" />
                                My Reservations
                            </Button>
                        </Link>
                    </div>
                </div>

                {/* Stats Cards */}
                <div>
                    <h2 className="text-lg font-semibold mb-4">Travel Statistics</h2>
                    <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Total Reservations</CardTitle>
                                <Ticket className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">{stats.total_reservations}</div>
                                <p className="text-xs text-muted-foreground">
                                    All your bookings
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Upcoming Trips</CardTitle>
                                <Calendar className="h-4 w-4 text-blue-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-blue-600">{stats.upcoming_reservations}</div>
                                <p className="text-xs text-muted-foreground">
                                    Confirmed future trips
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Completed Trips</CardTitle>
                                <CheckCircle className="h-4 w-4 text-green-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-green-600">{stats.completed_trips}</div>
                                <p className="text-xs text-muted-foreground">
                                    Successfully traveled
                                </p>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                                <CardTitle className="text-sm font-medium">Cancelled</CardTitle>
                                <XCircle className="h-4 w-4 text-red-600" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold text-red-600">{stats.cancelled_reservations}</div>
                                <p className="text-xs text-muted-foreground">
                                    Cancelled bookings
                                </p>
                            </CardContent>
                        </Card>
                    </div>
                </div>

                {/* Activity Grid */}
                <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                    {/* Upcoming Reservations */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Upcoming Trips</CardTitle>
                            <CardDescription>
                                Your confirmed upcoming reservations
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {upcomingReservations.slice(0, 5).map((reservation) => (
                                    <div key={reservation.id} className="flex items-center space-x-4">
                                        <div className="flex h-9 w-9 items-center justify-center rounded-md bg-primary/10">
                                            <MapPin className="h-4 w-4 text-primary" />
                                        </div>
                                        <div className="flex-1 space-y-1">
                                            <p className="text-sm font-medium leading-none">
                                                {reservation.trip?.origin_city?.name} → {reservation.trip?.destination_city?.name}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {reservation.trip?.departure_time && formatTime(reservation.trip.departure_time)} • {reservation.seats_count} seat{reservation.seats_count > 1 ? 's' : ''}
                                            </p>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            {reservation.trip?.bus && (
                                                <Badge 
                                                    variant={reservation.trip.bus.type === 'VIP' ? 'default' : 'secondary'}
                                                    className={reservation.trip.bus.type === 'VIP' ? 'bg-purple-600' : ''}
                                                >
                                                    {reservation.trip.bus.type}
                                                </Badge>
                                            )}
                                            <span className="text-sm font-medium">{formatPrice(reservation.total_price)}</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-4 pt-4 border-t">
                                <Link href={route('user.reservations.index')}>
                                    <Button variant="outline" className="w-full">
                                        <Ticket className="mr-2 h-4 w-4" />
                                        View All Reservations
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Available Trips */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Available Trips</CardTitle>
                            <CardDescription>
                                Book your next journey
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {availableTrips.slice(0, 5).map((trip) => (
                                    <div key={trip.id} className="flex items-center space-x-4">
                                        <div className="flex h-9 w-9 items-center justify-center rounded-md bg-primary/10">
                                            <Bus className="h-4 w-4 text-primary" />
                                        </div>
                                        <div className="flex-1 space-y-1">
                                            <p className="text-sm font-medium leading-none">
                                                {trip.origin_city?.name} → {trip.destination_city?.name}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {formatTime(trip.departure_time)} • {trip.bus?.bus_code} • {formatPrice(trip.price)}
                                            </p>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            {trip.bus && (
                                                <Badge 
                                                    variant={trip.bus.type === 'VIP' ? 'default' : 'secondary'}
                                                    className={trip.bus.type === 'VIP' ? 'bg-purple-600' : ''}
                                                >
                                                    {trip.bus.type}
                                                </Badge>
                                            )}
                                            <div className="flex items-center space-x-1">
                                                <Users className="h-3 w-3 text-muted-foreground" />
                                                <span className="text-xs text-muted-foreground">{trip.available_seats}</span>
                                            </div>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-4 pt-4 border-t">
                                <Link href={route('user.trips.index')}>
                                    <Button variant="outline" className="w-full">
                                        <RouteIcon className="mr-2 h-4 w-4" />
                                        Browse All Trips
                                    </Button>
                                </Link>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Recent Activity */}
                    <Card>
                        <CardHeader>
                            <CardTitle>Recent Activity</CardTitle>
                            <CardDescription>
                                Your latest reservations
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div className="space-y-4">
                                {recentReservations.slice(0, 5).map((reservation) => (
                                    <div key={reservation.id} className="flex items-center space-x-4">
                                        <div className="flex h-9 w-9 items-center justify-center rounded-md bg-primary/10">
                                            <Activity className="h-4 w-4 text-primary" />
                                        </div>
                                        <div className="flex-1 space-y-1">
                                            <p className="text-sm font-medium leading-none">
                                                {reservation.reservation_code}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {reservation.trip?.origin_city?.name} → {reservation.trip?.destination_city?.name}
                                            </p>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            {getStatusBadge(reservation.status)}
                                            <span className="text-sm font-medium">{formatPrice(reservation.total_price)}</span>
                                        </div>
                                    </div>
                                ))}
                            </div>
                            <div className="mt-4 pt-4 border-t">
                                <Link href={route('user.reservations.index')}>
                                    <Button variant="outline" className="w-full">
                                        <Activity className="mr-2 h-4 w-4" />
                                        View Activity History
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