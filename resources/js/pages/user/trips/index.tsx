import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Calendar, Clock, MapPin, Search, Users, Route as RouteIcon } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type Trip } from '@/types';

interface City {
    id: number;
    name: string;
    code: string;
}

interface TripsIndexProps {
    trips: {
        data: Trip[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from?: number;
        to?: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    cities: City[];
    filters: {
        origin_city_id?: number;
        destination_city_id?: number;
        departure_date?: string;
        min_seats?: number;
        max_price?: number;
    };
}

export default function TripsIndex({ trips, cities, filters }: TripsIndexProps) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };
    const [originCity, setOriginCity] = useState(filters.origin_city_id?.toString() || 'all');
    const [destinationCity, setDestinationCity] = useState(filters.destination_city_id?.toString() || 'all');
    const [departureDate, setDepartureDate] = useState(filters.departure_date || '');
    const [minSeats, setMinSeats] = useState(filters.min_seats?.toString() || '');
    const [maxPrice, setMaxPrice] = useState(filters.max_price?.toString() || '');

    const handleSearch = () => {
        const params: Record<string, string> = {};
        if (originCity && originCity !== 'all') params.origin_city_id = originCity;
        if (destinationCity && destinationCity !== 'all') params.destination_city_id = destinationCity;
        if (departureDate) params.departure_date = departureDate;
        if (minSeats) params.min_seats = minSeats;
        if (maxPrice) params.max_price = maxPrice;

        router.get(route('user.trips.index'), params, {
            preserveState: true,
            replace: true,
        });
    };

    const formatTime = (time: string) => {
        if (!time || !time.includes(':')) return time;
        
        // Create a date object with today's date and the provided time
        const [hours, minutes] = time.split(':');
        const date = new Date();
        date.setHours(parseInt(hours, 10), parseInt(minutes, 10), 0, 0);
        
        return date.toLocaleTimeString('en-US', {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        });
    };

    const formatPrice = (price: string | number) => {
        return new Intl.NumberFormat('en-EG', {
            style: 'currency',
            currency: 'EGP',
        }).format(typeof price === 'string' ? parseFloat(price) : price);
    };

    const getAvailabilityBadge = (availableSeats: number | undefined) => {
        const seats = availableSeats || 0;
        if (seats === 0) {
            return <Badge variant="destructive">Sold Out</Badge>;
        } else if (seats <= 5) {
            return <Badge variant="secondary" className="text-orange-600 border-orange-600">Few Seats Left</Badge>;
        } else {
            return <Badge variant="outline" className="text-green-600 border-green-600">Available</Badge>;
        }
    };

    const breadcrumbs = [
        { title: 'Dashboard', href: route('user.dashboard') },
        { title: 'Browse Trips', href: route('user.trips.index') },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Browse Trips" />

            <div className="flex-1 space-y-6 p-4 md:p-6">
                {flash?.success && (
                    <div className="rounded-md bg-green-50 p-4">
                        <div className="text-sm font-medium text-green-800">{flash.success}</div>
                    </div>
                )}

                {flash?.error && (
                    <div className="rounded-md bg-red-50 p-4">
                        <div className="text-sm font-medium text-red-800">{flash.error}</div>
                    </div>
                )}

                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Browse Trips</h1>
                        <p className="text-muted-foreground">
                            Find and book your next journey
                        </p>
                    </div>
                    <Link href={route('user.reservations.index')}>
                        <Button variant="outline">
                            <Calendar className="mr-2 h-4 w-4" />
                            My Reservations
                        </Button>
                    </Link>
                </div>

                <Card className='rounded-sm'>
                    <CardHeader>
                        <CardTitle>Search Trips</CardTitle>
                        <CardDescription>
                            Find trips by destination, date, and other preferences
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                            <div className="space-y-2">
                                <label className="text-sm font-medium">From</label>
                                <Select value={originCity} onValueChange={setOriginCity}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Origin city" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Cities</SelectItem>
                                        {cities.map((city) => (
                                            <SelectItem key={city.id} value={city.id.toString()}>
                                                {city.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <label className="text-sm font-medium">To</label>
                                <Select value={destinationCity} onValueChange={setDestinationCity}>
                                    <SelectTrigger>
                                        <SelectValue placeholder="Destination city" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Cities</SelectItem>
                                        {cities.map((city) => (
                                            <SelectItem key={city.id} value={city.id.toString()}>
                                                {city.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Departure Date</label>
                                <Input
                                    type="date"
                                    value={departureDate}
                                    onChange={(e) => setDepartureDate(e.target.value)}
                                />
                            </div>
                            <div className="space-y-2">
                                <label className="text-sm font-medium">Min Seats</label>
                                <Input
                                    type="number"
                                    min="1"
                                    max="10"
                                    placeholder="1"
                                    value={minSeats}
                                    onChange={(e) => setMinSeats(e.target.value)}
                                />
                            </div>
                            <div className="flex items-end">
                                <Button onClick={handleSearch} className="w-full">
                                    <Search className="mr-2 h-4 w-4" />
                                    Search
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card className='p-0 shadow-none border-none'>
                    <CardHeader className='p-0'>
                        <CardTitle>Available Trips ({trips.total})</CardTitle>
                        <CardDescription>
                            Choose from available trips matching your criteria
                        </CardDescription>
                    </CardHeader>
                    <CardContent className='p-0'>

                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Route</TableHead>
                                        <TableHead>Departure</TableHead>
                                        <TableHead>Bus</TableHead>
                                        <TableHead>Available Seats</TableHead>
                                        <TableHead>Price</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead className="w-[100px]">Action</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {trips.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={7} className="h-24 text-center">
                                                <div className="flex flex-col items-center justify-center space-y-2">
                                                    <RouteIcon className="h-8 w-8 text-muted-foreground" />
                                                    <p className="text-sm text-muted-foreground">No trips found</p>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        trips.data.map((trip) => (
                                            <TableRow key={trip.id}>
                                                <TableCell className="font-medium">
                                                    <div>
                                                        <div className="flex items-center space-x-1">
                                                            <MapPin className="h-3 w-3 text-muted-foreground" />
                                                            <span className="font-medium">
                                                                {trip.origin_city?.name || 'Unknown'} → {trip.destination_city?.name || 'Unknown'}
                                                            </span>
                                                        </div>
                                                        <div className="text-sm text-muted-foreground">
                                                            {trip.origin_city?.code} → {trip.destination_city?.code}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>
                                                        <div className="font-medium">
                                                        {formatTime(trip.departure_time)}
                                                        </div>
                                                        <div className="text-sm text-muted-foreground flex items-center space-x-1">
                                                            <Clock className="h-3 w-3" />
                                                            <span>Arrives: {formatTime(trip.arrival_time)}</span>
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>
                                                        <div className="font-medium">{trip.bus?.bus_code}</div>
                                                        <div className="flex items-center space-x-2">
                                                            {trip.bus && (
                                                                <Badge 
                                                                    variant={trip.bus.type === 'VIP' ? 'default' : 'secondary'}
                                                                    className={trip.bus.type === 'VIP' ? 'bg-purple-600' : ''}
                                                                >
                                                                    {trip.bus.type}
                                                                </Badge>
                                                            )}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div className="flex items-center space-x-2">
                                                        <Users className="h-4 w-4 text-muted-foreground" />
                                                        <span className={`font-medium ${trip.available_seats === 0 ? 'text-red-500' : (trip.available_seats || 0) <= 5 ? 'text-orange-500' : 'text-green-600'}`}>
                                                            {trip.available_seats || 0}
                                                        </span>
                                                        <span className="text-sm text-muted-foreground">
                                                            / {trip.bus?.capacity}
                                                        </span>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="font-medium">
                                                    {formatPrice(trip.price)}
                                                </TableCell>
                                                <TableCell>
                                                    {getAvailabilityBadge(trip.available_seats)}
                                                </TableCell>
                                                <TableCell>
                                                    {(trip.available_seats || 0) > 0 ? (
                                                        <Link href={route('user.trips.show', trip.id)}>
                                                            <Button size="sm">
                                                                Book Now
                                                            </Button>
                                                        </Link>
                                                    ) : (
                                                        <Button size="sm" disabled>
                                                            Sold Out
                                                        </Button>
                                                    )}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </div>

                        {/* Pagination */}
                        {trips.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((trips.current_page - 1) * trips.per_page) + 1} to{' '}
                                    {Math.min(trips.current_page * trips.per_page, trips.total)} of{' '}
                                    {trips.total} results
                                </div>
                                <div className="flex space-x-2">
                                    {trips.links.map((link, index) => (
                                        link.url ? (
                                            <Button
                                                key={index}
                                                variant={link.active ? 'default' : 'outline'}
                                                size="sm"
                                                onClick={() => router.get(link.url!)}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        ) : (
                                            <Button
                                                key={index}
                                                variant="outline"
                                                size="sm"
                                                disabled
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                            />
                                        )
                                    ))}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
}