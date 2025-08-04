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
import { Calendar, Edit, MoreHorizontal, Plus, Search, Trash2 } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type Reservation } from '@/types';

interface ReservationsIndexProps {
    reservations: {
        data: Reservation[];
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
    filters: {
        search?: string;
        status?: string;
        user_id?: number;
        trip_id?: number;
        start_date?: string;
        end_date?: string;
        upcoming_only?: boolean;
    };
    statusOptions: Record<string, string>;
}

export default function ReservationsIndex({ reservations, filters, statusOptions }: ReservationsIndexProps) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.status || 'all');

    const [deleteReservation, setDeleteReservation] = useState<Reservation | null>(null);
    const [isDeleting, setIsDeleting] = useState(false);

    const handleSearch = () => {
        const params: Record<string, string> = {};
        if (search) params.search = search;
        if (status && status !== 'all') params.status = status;

        router.get(route('admin.reservations.index'), params, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDelete = async () => {
        if (!deleteReservation) return;

        setIsDeleting(true);
        try {
            await router.delete(route('admin.reservations.destroy', deleteReservation.id), {
                preserveScroll: true,
                onSuccess: () => {
                    setDeleteReservation(null);
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
                return <Badge variant="default">Confirmed</Badge>;
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
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Reservation Management" />

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
                        <h1 className="text-2xl font-bold tracking-tight">Reservation Management</h1>
                        <p className="text-muted-foreground">
                            Manage customer reservations and bookings
                        </p>
                    </div>
                    <Link href={route('admin.reservations.create')}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Create Reservation
                        </Button>
                    </Link>
                </div>

                <Card className='rounded-sm'>
                    <CardHeader>
                        <CardTitle>Filters</CardTitle>
                        <CardDescription>
                            Search and filter reservations by various criteria
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-col gap-4 md:flex-row md:items-end">
                            <div className="flex-1">
                                <Input
                                    placeholder="Search by reservation code..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                />
                            </div>
                            <div className="flex gap-2">
                                <Select value={status} onValueChange={setStatus}>
                                    <SelectTrigger className="w-32">
                                        <SelectValue placeholder="Status" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="all">All Status</SelectItem>
                                        {Object.entries(statusOptions).map(([value, label]) => (
                                            <SelectItem key={value} value={value}>
                                                {label}
                                            </SelectItem>
                                        ))}
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
                        <CardTitle>Reservations ({reservations.total})</CardTitle>
                        <CardDescription>
                            A list of all reservations in your system
                        </CardDescription>
                    </CardHeader>
                    <CardContent className='p-0'>

                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Code</TableHead>
                                        <TableHead>Customer</TableHead>
                                        <TableHead>Trip</TableHead>
                                        <TableHead>Seats</TableHead>
                                        <TableHead>Price</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Reserved</TableHead>
                                        <TableHead className="w-[70px]">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {reservations.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={8} className="h-24 text-center">
                                                <div className="flex flex-col items-center justify-center space-y-2">
                                                    <Calendar className="h-8 w-8 text-muted-foreground" />
                                                    <p className="text-sm text-muted-foreground">No reservations found</p>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        reservations.data.map((reservation) => (
                                            <TableRow key={reservation.id}>
                                                <TableCell className="font-medium">
                                                    {reservation.reservation_code}
                                                </TableCell>
                                                <TableCell>
                                                    <div>
                                                        <div className="font-medium">{reservation.user?.name}</div>
                                                        <div className="text-sm text-muted-foreground">{reservation.user?.email}</div>
                                                    </div>
                                                </TableCell>
                                                <TableCell className="font-medium">
                                                    <div>
                                                        <div className="font-medium">
                                                            {reservation.trip?.origin} → {reservation.trip?.destination}
                                                        </div>
                                                        <div className="text-sm text-muted-foreground">
                                                            {reservation.trip?.departure_time && formatTime(reservation.trip.departure_time)} • {reservation.trip?.bus?.bus_code}
                                                        </div>
                                                    </div>
                                                </TableCell>
                                                <TableCell>{reservation.seats_count}</TableCell>
                                                <TableCell className="font-medium">
                                                    {formatPrice(reservation.total_price)}
                                                </TableCell>
                                                <TableCell>
                                                    {getStatusBadge(reservation.status)}
                                                </TableCell>
                                                <TableCell>
                                                    {new Date(reservation.reserved_at).toLocaleDateString('en-EG')}
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
                                                                <Link href={route('admin.reservations.edit', reservation.id)}>
                                                                    <Edit className="mr-2 h-4 w-4" />
                                                                    Edit
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuSeparator />
                                                            <DropdownMenuItem
                                                                onClick={() => setDeleteReservation(reservation)}
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
                        {reservations.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((reservations.current_page - 1) * reservations.per_page) + 1} to{' '}
                                    {Math.min(reservations.current_page * reservations.per_page, reservations.total)} of{' '}
                                    {reservations.total} results
                                </div>
                                <div className="flex space-x-2">
                                    {reservations.links.map((link, index) => (
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
            <Dialog open={!!deleteReservation} onOpenChange={() => setDeleteReservation(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete Reservation</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete reservation{' '}
                            <strong>{deleteReservation?.reservation_code}</strong>? This action cannot be undone.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setDeleteReservation(null)}
                            disabled={isDeleting}
                        >
                            Cancel
                        </Button>
                        <Button 
                            variant="destructive" 
                            onClick={handleDelete}
                            disabled={isDeleting}
                        >
                            {isDeleting ? 'Deleting...' : 'Delete Reservation'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}