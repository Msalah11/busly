import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/input-error';
import { ArrowLeft, MapPin } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type Bus as BusType } from '@/types';

interface CreateTripProps {
    buses: BusType[];
}

export default function CreateTrip({ buses }: CreateTripProps) {
    const { data, setData, post, processing, errors, reset } = useForm({
        origin: '',
        destination: '',
        departure_time: '',
        arrival_time: '',
        price: '',
        bus_id: '',
        is_active: true,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('admin.trips.store'), {
            onSuccess: () => reset(),
        });
    };

    const breadcrumbs = [
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Admin Dashboard', href: route('admin.dashboard') },
        { title: 'Trip Management', href: route('admin.trips.index') },
        { title: 'Create Trip', href: route('admin.trips.create') },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Trip" />
            
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
                                <h1 className="text-2xl font-bold tracking-tight">Create Trip</h1>
                                <p className="text-muted-foreground">
                                    Add a new trip to the system
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
                                    Enter the trip details and configuration.
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
                                            <MapPin className="mr-2 h-4 w-4" />
                                            {processing ? 'Creating...' : 'Create Trip'}
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
                                <CardTitle>Guidelines</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Route Information</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Enter clear city names for origin and destination. Use standard city names that passengers will recognize.
                                    </p>
                                </div>
                                
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Schedule</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Ensure arrival time is after departure time. Consider travel duration and traffic conditions.
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Pricing</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Set competitive prices based on distance, duration, and bus type. VIP buses typically have higher prices.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>

                        {buses.length > 0 && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Available Buses</CardTitle>
                                </CardHeader>
                                <CardContent>
                                    <div className="space-y-2 text-sm">
                                        {buses.slice(0, 5).map((bus) => (
                                            <div key={bus.id} className="flex items-center justify-between p-2 rounded border">
                                                <div>
                                                    <div className="font-medium">{bus.bus_code}</div>
                                                    <div className="text-muted-foreground">{bus.type}</div>
                                                </div>
                                                <div className="text-right">
                                                    <div className="font-medium">{bus.capacity} seats</div>
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