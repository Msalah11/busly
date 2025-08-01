import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { MapPin, Edit, MoreHorizontal, Plus, Search, Trash2 } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type Trip } from '@/types';

interface TripsIndexProps {
    trips: {
        data: Trip[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    filters: {
        search?: string;
        bus_id?: number;
        active?: boolean;
    };
}

export default function TripsIndex({ trips, filters }: TripsIndexProps) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };
    const [search, setSearch] = useState(filters.search || '');
    const [busId] = useState(filters.bus_id?.toString() || 'all');
    const [active, setActive] = useState(filters.active !== undefined ? (filters.active ? 'active' : 'inactive') : 'all');
    const [deleteTrip, setDeleteTrip] = useState<Trip | null>(null);
    const [isDeleting, setIsDeleting] = useState(false);

    const handleSearch = () => {
        const params: Record<string, string> = {};
        if (search) params.search = search;
        if (busId && busId !== 'all') params.bus_id = busId;
        if (active && active !== 'all') params.active = active === 'active' ? '1' : '0';

        router.get(route('admin.trips.index'), params, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = async () => {
        if (!deleteTrip) return;

        setIsDeleting(true);
        try {
            await router.delete(route('admin.trips.destroy', deleteTrip.id), {
                preserveScroll: true,
                onSuccess: () => {
                    setDeleteTrip(null);
                },
                onFinish: () => {
                    setIsDeleting(false);
                },
            });
        } catch {
            setIsDeleting(false);
        }
    };

    const formatTime = (time: string) => {
        return new Date(time).toLocaleTimeString('en-EG', {
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
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Admin Dashboard', href: route('admin.dashboard') },
        { title: 'Trip Management', href: route('admin.trips.index') },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Trip Management" />

            <div className="flex-1 space-y-6 p-4 md:p-6">
                {flash?.success && (
                    <div className="rounded-md bg-green-50 p-4">
                        <div className="text-sm font-medium text-green-800">
                            {flash.success}
                        </div>
                    </div>
                )}

                {flash?.error && (
                    <div className="rounded-md bg-red-50 p-4">
                        <div className="text-sm font-medium text-red-800">
                            {flash.error}
                        </div>
                    </div>
                )}

                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Trip Management</h1>
                        <p className="text-muted-foreground">
                            Manage your bus trips, routes, and schedules
                        </p>
                    </div>
                    <Link href={route('admin.trips.create')}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Add Trip
                        </Button>
                    </Link>
                </div>

                <Card className='rounded-sm'>
                    <CardHeader>
                        <CardTitle>Filters</CardTitle>
                        <CardDescription>
                            Search and filter trips by various criteria
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-col gap-4 md:flex-row md:items-end">
                            <div className="flex-1">
                                <Input
                                    placeholder="Search by origin or destination..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                />
                            </div>
                            <div className="flex gap-2">
                                <Select value={active} onValueChange={setActive}>
                                    <SelectTrigger className="w-32">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        <SelectItem value="active">Active</SelectItem>
                                        <SelectItem value="inactive">Inactive</SelectItem>
                                    </SelectContent>
                                </Select>
                                <Button onClick={handleSearch}>
                                    <Search className="mr-2 h-4 w-4" />
                                    Search
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card className='p-0 shadow-none border-none'>
                    <CardHeader className='p-0'>
                        <CardTitle>Trips ({trips.total})</CardTitle>
                        <CardDescription>
                            A list of all trips in your system
                        </CardDescription>
                    </CardHeader>
                    <CardContent className='p-0'>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Route</TableHead>
                                        <TableHead>Bus</TableHead>
                                        <TableHead>Schedule</TableHead>
                                        <TableHead>Price</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Created</TableHead>
                                        <TableHead className="w-[70px]">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {trips.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={7} className="h-24 text-center">
                                                <div className="flex flex-col items-center justify-center space-y-2">
                                                    <MapPin className="h-8 w-8 text-muted-foreground" />
                                                    <p className="text-sm text-muted-foreground">No trips found</p>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        trips.data.map((trip) => (
                                            <TableRow key={trip.id}>
                                                <TableCell className="font-medium">
                                                    {trip.origin} → {trip.destination}
                                                </TableCell>
                                                <TableCell>
                                                    <div>
                                                        <div className="font-medium">{trip.bus?.bus_code}</div>
                                                        <div className="text-sm text-muted-foreground">
                                                            {trip.bus?.type} • {trip.bus?.capacity} seats
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>
                                                    <div>
                                                        <div className="font-medium">
                                                            {formatTime(trip.departure_time)}
                                                        </div>
                                                        <div className="text-sm text-muted-foreground">
                                                            Arrives: {formatTime(trip.arrival_time)}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>{formatPrice(trip.price)}</TableCell>
                                                <TableCell>
                                                    <Badge variant={trip.is_active ? 'default' : 'destructive'}>
                                                        {trip.is_active ? 'Active' : 'Inactive'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    {new Date(trip.created_at).toLocaleDateString('en-EG')}
                                                </TableCell>
                                                <TableCell>
                                                    <DropdownMenu>
                                                        <DropdownMenuTrigger asChild>
                                                            <Button variant="ghost" className="h-8 w-8 p-0">
                                                                <span className="sr-only">Open menu</span>
                                                                <MoreHorizontal className="h-4 w-4" />
                                                            </Button>
                                                        </DropdownMenuTrigger>
                                                        <DropdownMenuContent align="end">
                                                            <DropdownMenuItem asChild>
                                                                <Link href={route('admin.trips.edit', trip.id)}>
                                                                    <Edit className="mr-2 h-4 w-4" />
                                                                    Edit
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuSeparator />
                                                            <DropdownMenuItem
                                                                onClick={() => setDeleteTrip(trip)}
                                                                className="text-red-600 focus:text-red-600"
                                                            >
                                                                <Trash2 className="mr-2 h-4 w-4" />
                                                                Delete
                                                            </DropdownMenuItem>
                                                        </DropdownMenuContent>
                                                    </DropdownMenu>
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

            {/* Delete Dialog */}
            <Dialog open={!!deleteTrip} onOpenChange={() => setDeleteTrip(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Trip</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete the trip from{' '}
                            <strong>{deleteTrip?.origin}</strong> to{' '}
                            <strong>{deleteTrip?.destination}</strong>? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDeleteTrip(null)}
                            disabled={isDeleting}
                        >
                            Cancel
                        </Button>
                        <Button 
                            variant="destructive" 
                            onClick={handleDelete}
                            disabled={isDeleting}
                        >
                            {isDeleting ? 'Deleting...' : 'Delete Trip'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}