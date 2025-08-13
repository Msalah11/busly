import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';
import { Head, Link } from '@inertiajs/react';
import { Calendar, Clock, MapPin, Plus, Ticket } from 'lucide-react';

interface DashboardProps {
    recentReservations: Array<{
        id: number;
        reservation_code: string;
        seats_count: number;
        total_price: number;
        status: string;
        reserved_at: string;
        trip: {
            id: number;
            departure_time: string;
            price: number;
            origin_city: { name: string };
            destination_city: { name: string };
        };
    }>;
    upcomingReservations: Array<{
        id: number;
        reservation_code: string;
        seats_count: number;
        total_price: number;
        status: string;
        trip: {
            id: number;
            departure_time: string;
            price: number;
            origin_city: { name: string };
            destination_city: { name: string };
        };
    }>;
    availableTrips: Array<{
        id: number;
        departure_time: string;
        arrival_time: string;
        price: number;
        available_seats: number;
        origin_city: { name: string };
        destination_city: { name: string };
        bus: { capacity: number };
    }>;
    stats: {
        total_reservations: number;
        upcoming_reservations: number;
        completed_trips: number;
        cancelled_reservations: number;
    };
}

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

export default function Dashboard({ recentReservations, upcomingReservations, availableTrips, stats }: DashboardProps) {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Dashboard" />
            <div className="flex h-full flex-1 flex-col gap-6 p-4">
                {/* Stats Cards */}
                <div className="grid auto-rows-min gap-4 md:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Reservations</CardTitle>
                            <Ticket className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.total_reservations}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Upcoming Trips</CardTitle>
                            <Calendar className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.upcoming_reservations}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Completed Trips</CardTitle>
                            <MapPin className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.completed_trips}</div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Cancelled</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{stats.cancelled_reservations}</div>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid gap-6 md:grid-cols-2">
                    {/* Upcoming Reservations */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle>Upcoming Reservations</CardTitle>
                                <CardDescription>Your confirmed upcoming trips</CardDescription>
                            </div>
                            <Button asChild size="sm">
                                <Link href={route('user.reservations.index')}>View All</Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            {upcomingReservations.length > 0 ? (
                                <div className="space-y-4">
                                    {upcomingReservations.map((reservation) => (
                                        <div key={reservation.id} className="flex items-center justify-between border-b pb-2 last:border-b-0">
                                            <div>
                                                <p className="font-medium">
                                                    {reservation.trip.origin_city.name} → {reservation.trip.destination_city.name}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {new Date(reservation.trip.departure_time).toLocaleDateString()} at{' '}
                                                    {new Date(reservation.trip.departure_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {reservation.seats_count} seat{reservation.seats_count > 1 ? 's' : ''} • {reservation.reservation_code}
                                                </p>
                                            </div>
                                            <Badge variant="secondary">${reservation.total_price}</Badge>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-muted-foreground">No upcoming reservations</p>
                            )}
                        </CardContent>
                    </Card>

                    {/* Available Trips */}
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <div>
                                <CardTitle>Available Trips</CardTitle>
                                <CardDescription>Book your next journey</CardDescription>
                            </div>
                            <Button asChild size="sm">
                                <Link href={route('user.trips.index')}>
                                    <Plus className="h-4 w-4 mr-1" />
                                    Browse Trips
                                </Link>
                            </Button>
                        </CardHeader>
                        <CardContent>
                            {availableTrips.length > 0 ? (
                                <div className="space-y-4">
                                    {availableTrips.map((trip) => (
                                        <div key={trip.id} className="flex items-center justify-between border-b pb-2 last:border-b-0">
                                            <div>
                                                <p className="font-medium">
                                                    {trip.origin_city.name} → {trip.destination_city.name}
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    {new Date(trip.departure_time).toLocaleDateString()} at{' '}
                                                    {new Date(trip.departure_time).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                                </p>
                                                <p className="text-xs text-muted-foreground">
                                                    {trip.available_seats} seats available
                                                </p>
                                            </div>
                                            <div className="text-right">
                                                <Badge variant="outline">${trip.price}</Badge>
                                                <Button asChild size="sm" className="ml-2">
                                                    <Link href={route('user.trips.show', trip.id)}>Book</Link>
                                                </Button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-muted-foreground">No available trips at the moment</p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                {/* Recent Activity */}
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Activity</CardTitle>
                        <CardDescription>Your latest reservations</CardDescription>
                    </CardHeader>
                    <CardContent>
                        {recentReservations.length > 0 ? (
                            <div className="space-y-4">
                                {recentReservations.map((reservation) => (
                                    <div key={reservation.id} className="flex items-center justify-between">
                                        <div>
                                            <p className="font-medium">
                                                {reservation.trip.origin_city.name} → {reservation.trip.destination_city.name}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                Reserved on {new Date(reservation.reserved_at).toLocaleDateString()}
                                            </p>
                                            <p className="text-xs text-muted-foreground">{reservation.reservation_code}</p>
                                        </div>
                                        <div className="text-right">
                                            <Badge variant={reservation.status === 'confirmed' ? 'default' : 'destructive'}>
                                                {reservation.status}
                                            </Badge>
                                            <p className="text-sm text-muted-foreground mt-1">${reservation.total_price}</p>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <p className="text-muted-foreground">No recent activity</p>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}
