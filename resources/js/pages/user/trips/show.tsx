import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import InputError from '@/components/input-error';
import { ArrowLeft, Bus, Calendar, Clock, CreditCard, MapPin, Ticket, Users, AlertCircle } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type Trip } from '@/types';

interface TripShowProps {
    trip: Trip;
}

export default function TripShow({ trip }: TripShowProps) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };
    const { data, setData, post, processing, errors, reset } = useForm({
        seats_count: '1',
        total_price: '',
    });

    // Calculate total price when seats count changes
    useEffect(() => {
        if (data.seats_count) {
            const totalPrice = parseFloat(trip.price) * parseInt(data.seats_count);
            setData('total_price', totalPrice.toFixed(2));
        }
    }, [data.seats_count, trip.price, setData]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('user.reservations.store'), {
            onSuccess: () => reset(),
        });
    };

    const formatPrice = (price: string | number) => {
        return new Intl.NumberFormat('en-EG', {
            style: 'currency',
            currency: 'EGP',
        }).format(typeof price === 'string' ? parseFloat(price) : price);
    };

    const formatDateTime = (dateTime: string) => {
        return new Date(dateTime).toLocaleString('en-EG', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        });
    };

    const getAvailabilityStatus = () => {
        const seats = trip.available_seats || 0;
        if (seats === 0) {
            return { color: 'text-red-600', message: 'Sold Out' };
        } else if (seats <= 5) {
            return { color: 'text-orange-600', message: 'Few Seats Left' };
        } else {
            return { color: 'text-green-600', message: 'Available' };
        }
    };

    const availabilityStatus = getAvailabilityStatus();

    const breadcrumbs = [
        { title: 'Dashboard', href: route('user.dashboard') },
        { title: 'Browse Trips', href: route('user.trips.index') },
        { title: 'Trip Details', href: route('user.trips.show', trip.id) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Trip - ${trip.origin_city?.name} to ${trip.destination_city?.name}`} />
            
            <div className="flex-1 space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <div className="flex items-center space-x-2">
                            <Link href={route('user.trips.index')}>
                                <Button variant="outline" size="icon">
                                    <ArrowLeft className="h-4 w-4" />
                                </Button>
                            </Link>
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">Trip Details</h1>
                                <p className="text-muted-foreground">
                                    {trip.origin_city?.name} → {trip.destination_city?.name}
                                </p>
                            </div>
                        </div>
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
                        <Badge 
                            variant="outline" 
                            className={availabilityStatus.color}
                        >
                            {availabilityStatus.message}
                        </Badge>
                    </div>
                </div>

                {flash?.error && (
                    <Alert variant="destructive">
                        <AlertCircle className="h-4 w-4" />
                        <AlertDescription>{flash.error}</AlertDescription>
                    </Alert>
                )}

                <div className="grid gap-6 md:grid-cols-3">
                    <div className="md:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Trip Information</CardTitle>
                                <CardDescription>
                                    Complete details about this trip
                                </CardDescription>
                            </CardHeader>
                            <CardContent className="space-y-6">
                                <div className="grid gap-6 md:grid-cols-2">
                                    <div className="space-y-4">
                                        <div className="space-y-2">
                                            <div className="flex items-center space-x-2">
                                                <MapPin className="h-4 w-4 text-muted-foreground" />
                                                <span className="text-sm font-medium">Route</span>
                                            </div>
                                            <p className="text-lg font-semibold">
                                                {trip.origin_city?.name} → {trip.destination_city?.name}
                                            </p>
                                            <p className="text-sm text-muted-foreground">
                                                {trip.origin_city?.code} → {trip.destination_city?.code}
                                            </p>
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center space-x-2">
                                                <Calendar className="h-4 w-4 text-muted-foreground" />
                                                <span className="text-sm font-medium">Departure</span>
                                            </div>
                                            <p className="text-lg">
                                                {formatDateTime(trip.departure_time)}
                                            </p>
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center space-x-2">
                                                <CreditCard className="h-4 w-4 text-muted-foreground" />
                                                <span className="text-sm font-medium">Price per Seat</span>
                                            </div>
                                            <p className="text-2xl font-bold text-primary">
                                                {formatPrice(trip.price)}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="space-y-4">
                                        {trip.bus && (
                                            <div className="space-y-2">
                                                <div className="flex items-center space-x-2">
                                                    <Bus className="h-4 w-4 text-muted-foreground" />
                                                    <span className="text-sm font-medium">Bus Information</span>
                                                </div>
                                                <div className="space-y-1">
                                                    <p className="text-lg font-semibold">{trip.bus.bus_code}</p>
                                                    <p className="text-sm text-muted-foreground">
                                                        {trip.bus.type} • {trip.bus.capacity} seats total
                                                    </p>
                                                </div>
                                            </div>
                                        )}

                                        <div className="space-y-2">
                                            <div className="flex items-center space-x-2">
                                                <Users className="h-4 w-4 text-muted-foreground" />
                                                <span className="text-sm font-medium">Seat Availability</span>
                                            </div>
                                            <div className="space-y-1">
                                                <p className={`text-lg font-semibold ${availabilityStatus.color}`}>
                                                    {trip.available_seats || 0} seats available
                                                </p>
                                                <p className="text-sm text-muted-foreground">
                                                    out of {trip.bus?.capacity} total seats
                                                </p>
                                            </div>
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center space-x-2">
                                                <Clock className="h-4 w-4 text-muted-foreground" />
                                                <span className="text-sm font-medium">Status</span>
                                            </div>
                                            <Badge 
                                                variant={trip.is_active ? 'outline' : 'secondary'}
                                                className={trip.is_active ? 'text-green-600 border-green-600' : 'text-orange-600'}
                                            >
                                                {trip.is_active ? 'Active' : 'Inactive'}
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        {(trip.available_seats || 0) > 0 && trip.is_active ? (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Book This Trip</CardTitle>
                                    <CardDescription>
                                        Reserve your seats for this journey
                                    </CardDescription>
                                </CardHeader>
                                <CardContent>
                                    <form onSubmit={submit} className="space-y-6">
                                        <input type="hidden" name="trip_id" value={trip.id} />
                                        
                                        <div className="space-y-2">
                                            <Label htmlFor="seats_count">Number of Seats</Label>
                                            <Input
                                                id="seats_count"
                                                type="number"
                                                min="1"
                                                max={Math.min(trip.available_seats || 0, 10)}
                                                value={data.seats_count}
                                                onChange={(e) => setData('seats_count', e.target.value)}
                                                placeholder="Enter number of seats"
                                                required
                                            />
                                            <InputError message={errors.seats_count} />
                                            <p className="text-sm text-muted-foreground">
                                                Maximum {Math.min(trip.available_seats || 0, 10)} seats per reservation.
                                            </p>
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="total_price">Total Price (EGP)</Label>
                                            <Input
                                                id="total_price"
                                                type="text"
                                                value={data.total_price ? formatPrice(data.total_price) : ''}
                                                readOnly
                                                className="bg-muted"
                                            />
                                            <p className="text-sm text-muted-foreground">
                                                Auto-calculated based on seat count.
                                            </p>
                                        </div>

                                        <Button type="submit" disabled={processing} className="w-full">
                                            <Ticket className="mr-2 h-4 w-4" />
                                            {processing ? 'Booking...' : 'Book Now'}
                                        </Button>
                                    </form>
                                </CardContent>
                            </Card>
                        ) : (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Booking Unavailable</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="text-center space-y-4">
                                        <div className="text-red-600">
                                            {(trip.available_seats || 0) === 0 ? (
                                                <p>This trip is sold out.</p>
                                            ) : (
                                                <p>This trip is currently unavailable for booking.</p>
                                            )}
                                        </div>
                                        <Link href={route('user.trips.index')}>
                                            <Button variant="outline" className="w-full">
                                                Browse Other Trips
                                            </Button>
                                        </Link>
                                    </div>
                                </CardContent>
                            </Card>
                        )}

                        <Card>
                            <CardHeader>
                                <CardTitle>Important Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Booking Policy</h4>
                                    <ul className="text-sm text-muted-foreground space-y-1">
                                        <li>• Maximum 10 seats per reservation</li>
                                        <li>• Seats are reserved immediately upon booking</li>
                                        <li>• Confirmation will be sent via email</li>
                                    </ul>
                                </div>
                                
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Cancellation Policy</h4>
                                    <p className="text-sm text-muted-foreground">
                                        You can cancel your reservation up until the departure time. 
                                        Cancelled reservations cannot be restored.
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Boarding Instructions</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Please arrive at the departure location at least 30 minutes 
                                        before your scheduled departure time.
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