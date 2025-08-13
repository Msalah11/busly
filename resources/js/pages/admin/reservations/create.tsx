import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Alert, AlertDescription } from '@/components/ui/alert';
import InputError from '@/components/input-error';
import { ArrowLeft, Calendar, MapPin, Clock, Bus, AlertCircle, Users } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type User, type Trip } from '@/types';

interface CreateReservationProps {
    users: User[];
    trips: Trip[];
    statusOptions: Record<string, string>;
}

export default function CreateReservation({ users, trips, statusOptions }: CreateReservationProps) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };
    const { data, setData, post, processing, errors, reset } = useForm({
        user_id: '',
        trip_id: '',
        seats_count: '1',
        total_price: '',
        status: 'confirmed',
        reserved_at: '',
        cancelled_at: '',
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
            setData('total_price', '');
        }
    }, [data.trip_id, data.seats_count, trips, setData]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('admin.reservations.store'), {
            onSuccess: () => reset(),
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

    const breadcrumbs = [
        { title: 'Dashboard', href: route('admin.dashboard') },
        { title: 'Admin Dashboard', href: route('admin.dashboard') },
        { title: 'Reservation Management', href: route('admin.reservations.index') },
        { title: 'Create Reservation', href: route('admin.reservations.create') },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Reservation" />
            
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
                                <h1 className="text-2xl font-bold tracking-tight">Create Reservation</h1>
                                <p className="text-muted-foreground">
                                    Create a new reservation for a customer
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
                                <CardTitle>Reservation Details</CardTitle>
                                <CardDescription>
                                    Enter the reservation information and booking details.
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
                                            onValueChange={(value) => setData('status', value)}
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

                                    <div className="flex items-center justify-end space-x-2">
                                        <Link href={route('admin.reservations.index')}>
                                            <Button variant="outline" type="button">
                                                Cancel
                                            </Button>
                                        </Link>
                                        <Button type="submit" disabled={processing}>
                                            <Calendar className="mr-2 h-4 w-4" />
                                            {processing ? 'Creating...' : 'Create Reservation'}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
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
                                    <h4 className="text-sm font-medium">Reservation Status</h4>
                                    <ul className="text-sm text-muted-foreground space-y-1">
                                        <li>• <strong>Confirmed:</strong> Seats are reserved and payment is confirmed</li>
                                        <li>• <strong>Cancelled:</strong> Reservation has been cancelled</li>
                                    </ul>
                                </div>
                                
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Seat Limits</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Maximum 10 seats can be reserved per booking. For larger groups, create multiple reservations.
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