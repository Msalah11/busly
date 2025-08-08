import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import InputError from '@/components/input-error';
import { ArrowLeft, MapPin, Clock, Bus, Edit, AlertCircle, Users } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type User, type Trip, type Reservation } from '@/types';

interface EditReservationProps {
    reservation: Reservation;
    users: User[];
    trips: Trip[];
    statusOptions: Record<string, string>;
}

export default function EditReservation({ reservation, users, trips, statusOptions }: EditReservationProps) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };
    const { data, setData, put, processing, errors } = useForm({
        user_id: reservation.user_id.toString(),
        trip_id: reservation.trip_id.toString(),
        seats_count: reservation.seats_count.toString(),
        total_price: reservation.total_price,
        status: reservation.status,
        reserved_at: reservation.reserved_at ? new Date(reservation.reserved_at).toISOString().slice(0, 16) : '',
        cancelled_at: reservation.cancelled_at ? new Date(reservation.cancelled_at).toISOString().slice(0, 16) : '',
    });

    const [selectedTrip, setSelectedTrip] = useState<Trip | null>(null);

    // Update selected trip when trip_id changes
    useEffect(() => {
        if (data.trip_id) {
            const trip = trips.find(t => t.id.toString() === data.trip_id);
            setSelectedTrip(trip || null);
            
            // Calculate total price based on trip price and seat count
            if (trip && data.seats_count) {
                const totalPrice = parseFloat(trip.price) * parseInt(data.seats_count);
                setData('total_price', totalPrice.toFixed(2));
            }
        } else {
            setSelectedTrip(null);
        }
    }, [data.trip_id, data.seats_count, trips, setData]);

    // Set initial selected trip
    useEffect(() => {
        const trip = trips.find(t => t.id === reservation.trip_id);
        setSelectedTrip(trip || null);
    }, [trips, reservation.trip_id]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('admin.reservations.update', reservation.id));
    };

    const formatDateTime = (dateTime: string) => {
        return new Date(dateTime).toLocaleString('en-EG', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        });
    };

    const formatTime = (time: string) => {
        if (!time || !time.includes(':')) return time;
        
        // Create a date object with today's date and the provided time
        const [hours, minutes] = time.split(':');
        const date = new Date();
        date.setHours(parseInt(hours, 10), parseInt(minutes, 10), 0, 0);
        
        return date.toLocaleTimeString('en-EG', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        });
    };

    const formatPrice = (price: string) => {
        return new Intl.NumberFormat('en-EG', {
            style: 'currency',
            currency: 'EGP',
        }).format(parseFloat(price));
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
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Admin Dashboard', href: route('admin.dashboard') },
        { title: 'Reservation Management', href: route('admin.reservations.index') },
        { title: 'Edit Reservation', href: route('admin.reservations.edit', reservation.id) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Reservation - ${reservation.reservation_code}`} />
            
            <div className="flex-1 space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <div className="flex items-center space-x-2">
                            <Link href={route('admin.reservations.index')}>
                                <Button variant="outline" size="icon">
                                    <ArrowLeft className="h-4 w-4" />
                                </Button>
                            </Link>
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">Edit Reservation</h1>
                                <p className="text-muted-foreground">
                                    Update reservation details for {reservation.reservation_code}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {flash?.error && (
                    <Alert variant="destructive" className="mb-6">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>{flash.error}</AlertDescription>
                    </Alert>
                )}

                <div className="grid gap-6 md:grid-cols-3">
                    <div className="md:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center justify-between">
                                    <span>Reservation Details</span>
                                    <div className="flex items-center space-x-2">
                                        {getStatusBadge(reservation.status)}
                                        <span className="text-sm text-muted-foreground">{reservation.reservation_code}</span>
                                    </div>
                                </CardTitle>
                                <CardDescription>
                                    Update the reservation information and booking details.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={submit} className="space-y-6">
                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="user_id">Customer</Label>
                                            <Select
                                                value={data.user_id}
                                                onValueChange={(value) => setData('user_id', value)}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select customer" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {users.map((user) => (
                                                        <SelectItem key={user.id} value={user.id.toString()}>
                                                            <div className="flex flex-col">
                                                                <span>{user.name}</span>
                                                                <span className="text-xs text-muted-foreground">{user.email}</span>
                                                            </div>
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.user_id} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="trip_id">Trip</Label>
                                            <Select
                                                value={data.trip_id}
                                                onValueChange={(value) => setData('trip_id', value)}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select trip" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {trips.map((trip) => (
                                                        <SelectItem key={trip.id} value={trip.id.toString()}>
                                                            <div className="flex flex-col">
                                                                <div className="flex items-center space-x-1">
                                                                    <MapPin className="h-3 w-3" />
                                                                    <span>{trip.route || `${trip.origin_city?.name || 'Unknown'} → ${trip.destination_city?.name || 'Unknown'}`}</span>
                                                                </div>
                                                                <div className="flex items-center space-x-2 text-xs text-muted-foreground">
                                                                    <div className="flex items-center space-x-1">
                                                                        <Clock className="h-3 w-3" />
                                                                        <span>{formatTime(trip.departure_time)}</span>
                                                                    </div>
                                                                    <span>•</span>
                                                                    <span>{formatPrice(trip.price)}</span>
                                                                    {trip.bus && (
                                                                        <>
                                                                            <span>•</span>
                                                                            <span>Bus: {trip.bus.bus_code}</span>
                                                                        </>
                                                                    )}
                                                                    {trip.available_seats !== undefined && (
                                                                        <>
                                                                            <span>•</span>
                                                                            <div className="flex items-center space-x-1">
                                                                                <Users className="h-3 w-3" />
                                                                                <span className={trip.available_seats === 0 ? 'text-red-500 font-medium' : trip.available_seats <= 5 ? 'text-orange-500 font-medium' : ''}>
                                                                                    {trip.available_seats} seats available
                                                                                </span>
                                                                            </div>
                                                                        </>
                                                                    )}
                                                                </div>
                                                            </div>
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.trip_id} />
                                        </div>
                                    </div>

                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="seats_count">Number of Seats</Label>
                                            <Input
                                                id="seats_count"
                                                type="number"
                                                min="1"
                                                max="10"
                                                value={data.seats_count}
                                                onChange={(e) => setData('seats_count', e.target.value)}
                                                placeholder="Enter number of seats"
                                                required
                                            />
                                            <InputError message={errors.seats_count} />
                                            <p className="text-sm text-muted-foreground">
                                                Maximum 10 seats per reservation.
                                            </p>
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="total_price">Total Price (EGP)</Label>
                                            <Input
                                                id="total_price"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                value={data.total_price}
                                                onChange={(e) => setData('total_price', e.target.value)}
                                                placeholder="Total price"
                                                required
                                            />
                                            <InputError message={errors.total_price} />
                                            <p className="text-sm text-muted-foreground">
                                                Auto-calculated based on trip price and seat count.
                                            </p>
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="status">Status</Label>
                                        <Select
                                            value={data.status}
                                            onValueChange={(value) => setData('status', value as 'confirmed' | 'cancelled')}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select status" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {Object.entries(statusOptions).map(([value, label]) => (
                                                    <SelectItem key={value} value={value}>
                                                        {label}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.status} />
                                    </div>

                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="reserved_at">Reserved Date</Label>
                                            <Input
                                                id="reserved_at"
                                                type="datetime-local"
                                                value={data.reserved_at}
                                                onChange={(e) => setData('reserved_at', e.target.value)}
                                            />
                                            <InputError message={errors.reserved_at} />
                                        </div>

                                        {data.status === 'cancelled' && (
                                            <div className="space-y-2">
                                                <Label htmlFor="cancelled_at">Cancellation Date</Label>
                                                <Input
                                                    id="cancelled_at"
                                                    type="datetime-local"
                                                    value={data.cancelled_at}
                                                    onChange={(e) => setData('cancelled_at', e.target.value)}
                                                />
                                                <InputError message={errors.cancelled_at} />
                                            </div>
                                        )}
                                    </div>

                                    <div className="flex items-center justify-end space-x-2">
                                        <Link href={route('admin.reservations.index')}>
                                            <Button variant="outline" type="button">
                                                Cancel
                                            </Button>
                                        </Link>
                                        <Button type="submit" disabled={processing}>
                                            <Edit className="mr-2 h-4 w-4" />
                                            {processing ? 'Updating...' : 'Update Reservation'}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Current Details</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Code:</span>
                                        <span className="text-sm font-medium">{reservation.reservation_code}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Customer:</span>
                                        <span className="text-sm font-medium">{reservation.user?.name}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Seats:</span>
                                        <span className="text-sm font-medium">{reservation.seats_count}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Total:</span>
                                        <span className="text-sm font-medium">{formatPrice(reservation.total_price)}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Reserved:</span>
                                        <span className="text-sm font-medium">{formatDateTime(reservation.reserved_at)}</span>
                                    </div>
                                    {reservation.cancelled_at && (
                                        <div className="flex justify-between">
                                            <span className="text-sm text-muted-foreground">Cancelled:</span>
                                            <span className="text-sm font-medium">{formatDateTime(reservation.cancelled_at)}</span>
                                        </div>
                                    )}
                                </div>
                            </CardContent>
                        </Card>

                        {selectedTrip && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Trip Details</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-4">
                                    <div className="space-y-2">
                                        <div className="flex items-center space-x-2">
                                            <MapPin className="h-4 w-4 text-muted-foreground" />
                                            <span className="font-medium">{selectedTrip.route || `${selectedTrip.origin_city?.name || 'Unknown'} → ${selectedTrip.destination_city?.name || 'Unknown'}`}</span>
                                        </div>
                                        <div className="flex items-center space-x-2">
                                            <Clock className="h-4 w-4 text-muted-foreground" />
                                            <span className="text-sm">{formatTime(selectedTrip.departure_time)}</span>
                                        </div>
                                        {selectedTrip.bus && (
                                            <div className="flex items-center space-x-2">
                                                <Bus className="h-4 w-4 text-muted-foreground" />
                                                <span className="text-sm">{selectedTrip.bus.bus_code} ({selectedTrip.bus.type})</span>
                                            </div>
                                        )}
                                        {selectedTrip.available_seats !== undefined && (
                                            <div className="flex items-center space-x-2">
                                                <Users className="h-4 w-4 text-muted-foreground" />
                                                <span className="text-sm text-muted-foreground">Available seats:</span>
                                                <span className={`font-medium ${selectedTrip.available_seats === 0 ? 'text-red-500' : selectedTrip.available_seats <= 5 ? 'text-orange-500' : 'text-green-600'}`}>
                                                    {selectedTrip.available_seats}
                                                </span>
                                            </div>
                                        )}
                                        <div className="flex items-center space-x-2">
                                            <span className="text-sm text-muted-foreground">Price per seat:</span>
                                            <span className="font-medium">{formatPrice(selectedTrip.price)}</span>
                                        </div>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        <Card>
                            <CardHeader>
                                <CardTitle>Guidelines</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Status Changes</h4>
                                    <ul className="text-sm text-muted-foreground space-y-1">
                                        <li>• Changing to cancelled will require a cancellation date</li>
                                        <li>• Changing from cancelled to confirmed will clear cancellation date</li>
                                    </ul>
                                </div>
                                
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Trip Changes</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Changing the trip will automatically recalculate the total price based on the new trip's pricing.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}