import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/input-error';
import { type Trip, type Bus as BusType } from '@/types';
import { ArrowLeft, Save, MapPin, CheckCircle, XCircle } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

interface EditTripProps {
    trip: Trip;
    buses: BusType[];
}

export default function EditTrip({ trip, buses }: EditTripProps) {
    // Helper function to extract time from datetime string
    const extractTime = (datetime: string) => {
        return new Date(datetime).toTimeString().slice(0, 5); // Gets HH:MM format
    };

    // Helper function to format time for display
    const formatTime = (time: string) => {
        return new Date(time).toLocaleTimeString('en-EG', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        });
    };

    const { data, setData, patch, processing, errors } = useForm({
        origin: trip.origin,
        destination: trip.destination,
        departure_time: extractTime(trip.departure_time),
        arrival_time: extractTime(trip.arrival_time),
        price: trip.price,
        bus_id: trip.bus_id.toString(),
        is_active: trip.is_active,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('admin.trips.update', trip.id));
    };



    const formatPrice = (price: string) => {
        return new Intl.NumberFormat('en-EG', {
            style: 'currency',
            currency: 'EGP',
        }).format(parseFloat(price));
    };

    const breadcrumbs = [
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Admin Dashboard', href: route('admin.dashboard') },
        { title: 'Trip Management', href: route('admin.trips.index') },
        { title: 'Edit Trip', href: route('admin.trips.edit', trip.id) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit Trip: ${trip.origin} → ${trip.destination}`} />
            
            <div className="flex-1 space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <div className="flex items-center space-x-2">
                            <Link href={route('admin.trips.index')}>
                                <Button variant="outline" size="icon">
                                    <ArrowLeft className="h-4 w-4" />
                                </Button>
                            </Link>
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">
                                    Edit Trip: {trip.origin} → {trip.destination}
                                </h1>
                                <p className="text-muted-foreground">
                                    Update trip information and settings
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    <div className="md:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Trip Information</CardTitle>
                                <CardDescription>
                                    Update the trip details and configuration.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={submit} className="space-y-6">
                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="origin">Origin</Label>
                                            <Input
                                                id="origin"
                                                type="text"
                                                value={data.origin}
                                                onChange={(e) => setData('origin', e.target.value)}
                                                placeholder="Enter departure city"
                                                required
                                            />
                                            <InputError message={errors.origin} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="destination">Destination</Label>
                                            <Input
                                                id="destination"
                                                type="text"
                                                value={data.destination}
                                                onChange={(e) => setData('destination', e.target.value)}
                                                placeholder="Enter arrival city"
                                                required
                                            />
                                            <InputError message={errors.destination} />
                                        </div>
                                    </div>

                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="departure_time">Departure Time</Label>
                                            <Input
                                                id="departure_time"
                                                type="time"
                                                value={data.departure_time}
                                                onChange={(e) => setData('departure_time', e.target.value)}
                                                required
                                            />
                                            <InputError message={errors.departure_time} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="arrival_time">Arrival Time</Label>
                                            <Input
                                                id="arrival_time"
                                                type="time"
                                                value={data.arrival_time}
                                                onChange={(e) => setData('arrival_time', e.target.value)}
                                                required
                                            />
                                            <InputError message={errors.arrival_time} />
                                        </div>
                                    </div>

                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="bus_id">Bus</Label>
                                            <Select value={data.bus_id} onValueChange={(value) => setData('bus_id', value)}>
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select a bus" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {buses.map((bus) => (
                                                        <SelectItem key={bus.id} value={bus.id.toString()}>
                                                            {bus.bus_code} - {bus.type} ({bus.capacity} seats)
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError message={errors.bus_id} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="price">Price ($)</Label>
                                            <Input
                                                id="price"
                                                type="number"
                                                step="0.01"
                                                min="0"
                                                value={data.price}
                                                onChange={(e) => setData('price', e.target.value)}
                                                placeholder="Enter ticket price"
                                                required
                                            />
                                            <InputError message={errors.price} />
                                        </div>
                                    </div>

                                    <div className="flex items-center space-x-2">
                                        <Checkbox
                                            id="is_active"
                                            checked={data.is_active}
                                            onCheckedChange={(checked) => setData('is_active', Boolean(checked))}
                                        />
                                        <Label htmlFor="is_active">Active</Label>
                                        <InputError message={errors.is_active} />
                                    </div>
                                    <p className="text-sm text-muted-foreground">
                                        Only active trips are available for booking.
                                    </p>

                                    <div className="flex items-center justify-end space-x-2">
                                        <Link href={route('admin.trips.index')}>
                                            <Button variant="outline" type="button">
                                                Cancel
                                            </Button>
                                        </Link>
                                        <Button type="submit" disabled={processing}>
                                            <Save className="mr-2 h-4 w-4" />
                                            {processing ? 'Saving...' : 'Save Changes'}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    {/* Sidebar */}
                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Current Trip</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex items-center space-x-3">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                                        <MapPin className="h-6 w-6 text-primary" />
                                    </div>
                                    <div>
                                        <p className="font-medium">{trip.origin} → {trip.destination}</p>
                                        <p className="text-sm text-muted-foreground">{formatPrice(trip.price)}</p>
                                    </div>
                                </div>
                                
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Status</span>
                                        {trip.is_active ? (
                                            <Badge variant="outline" className="text-green-600 border-green-600">
                                                <CheckCircle className="mr-1 h-3 w-3" />
                                                Active
                                            </Badge>
                                        ) : (
                                            <Badge variant="outline" className="text-red-600 border-red-600">
                                                <XCircle className="mr-1 h-3 w-3" />
                                                Inactive
                                            </Badge>
                                        )}
                                    </div>
                                    
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Bus</span>
                                        <span className="text-sm text-muted-foreground">
                                            {trip.bus?.bus_code}
                                        </span>
                                    </div>
                                    
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Departure</span>
                                        <span className="text-sm text-muted-foreground">
                                            {formatTime(trip.departure_time)}
                                        </span>
                                    </div>
                                    
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Arrival</span>
                                        <span className="text-sm text-muted-foreground">
                                            {formatTime(trip.arrival_time)}
                                        </span>
                                    </div>
                                    
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Created</span>
                                        <span className="text-sm text-muted-foreground">
                                            {new Date(trip.created_at).toLocaleDateString('en-EG')}
                                        </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Guidelines</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Route Changes</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Changing the route may affect existing reservations. Consider notifying passengers if significant changes are made.
                                    </p>
                                </div>
                                
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Schedule Updates</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Time changes should be communicated to passengers with existing bookings well in advance.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        {/* Available Buses */}
                        {buses.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Available Buses</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2 text-sm">
                                        {buses.slice(0, 5).map((bus) => (
                                            <div 
                                                key={bus.id} 
                                                className={`flex items-center justify-between p-2 rounded border ${
                                                    bus.id === trip.bus_id ? 'bg-primary/10 border-primary' : ''
                                                }`}
                                            >
                                                <div>
                                                    <div className="font-medium">{bus.bus_code}</div>
                                                    <div className="text-muted-foreground">{bus.type}</div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="font-medium">{bus.capacity} seats</div>
                                                    {bus.id === trip.bus_id && (
                                                        <div className="text-xs text-primary">Current</div>
                                                    )}
                                                </div>
                                            </div>
                                        ))}
                                        {buses.length > 5 && (
                                            <p className="text-muted-foreground text-center">
                                                +{buses.length - 5} more buses available
                                            </p>
                                        )}
                                    </div>
                                </CardContent>
                            </Card>
                        )}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}