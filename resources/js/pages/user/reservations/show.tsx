import { Head, Link, router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Alert, AlertDescription } from '@/components/ui/alert';
import { ArrowLeft, Bus, Calendar, Clock, CreditCard, MapPin, Users, X, AlertCircle } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';
import { type Reservation } from '@/types';

interface ReservationShowProps {
    reservation: Reservation;
}

export default function ReservationShow({ reservation }: ReservationShowProps) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };
    const [showCancelDialog, setShowCancelDialog] = useState(false);
    const [isCancelling, setIsCancelling] = useState(false);

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

    const formatPrice = (price: string | number) => {
        return new Intl.NumberFormat('en-EG', {
            style: 'currency',
            currency: 'EGP',
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

    const canCancelReservation = (): boolean => {
        if (reservation.status === 'cancelled') return false;
        
        // Check if departure is in the future (allow cancellation up to departure time)
        const departureTime = new Date(reservation.trip?.departure_time || '');
        const now = new Date();
        
        return departureTime > now;
    };

    const handleCancel = async () => {
        setIsCancelling(true);
        try {
            await router.delete(route('user.reservations.destroy', reservation.id), {
                onSuccess: () => {
                    setShowCancelDialog(false);
                },
                onFinish: () => {
                    setIsCancelling(false);
                },
            });
        } catch {
            setIsCancelling(false);
        }
    };

    const breadcrumbs = [
        { title: 'Dashboard', href: route('user.dashboard') },
        { title: 'My Reservations', href: route('user.reservations.index') },
        { title: 'Reservation Details', href: route('user.reservations.show', reservation.id) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Reservation - ${reservation.reservation_code}`} />
            
            <div className="flex-1 space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <div className="flex items-center space-x-2">
                            <Link href={route('user.reservations.index')}>
                                <Button variant="outline" size="icon">
                                    <ArrowLeft className="h-4 w-4" />
                                </Button>
                            </Link>
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">Reservation Details</h1>
                                <p className="text-muted-foreground">
                                    Booking confirmation for {reservation.reservation_code}
                                </p>
                            </div>
                        </div>
                    </div>
                    <div className="flex items-center space-x-2">
                        {getStatusBadge(reservation.status)}
                        {canCancelReservation() && (
                            <Button 
                                variant="destructive" 
                                onClick={() => setShowCancelDialog(true)}
                            >
                                <X className="mr-2 h-4 w-4" />
                                Cancel Reservation
                            </Button>
                        )}
                    </div>
                </div>

                {flash?.success && (
                    <Alert className="border-green-200 bg-green-50">
                        <AlertCircle className="h-4 w-4 text-green-600" />
                        <AlertDescription className="text-green-800">{flash.success}</AlertDescription>
                    </Alert>
                )}

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
                                <CardTitle className="flex items-center justify-between">
                                    <span>Trip Information</span>
                                    <div className="flex items-center space-x-2">
                                        {reservation.trip?.bus && (
                                            <Badge 
                                                variant={reservation.trip.bus.type === 'VIP' ? 'default' : 'secondary'}
                                                className={reservation.trip.bus.type === 'VIP' ? 'bg-purple-600' : ''}
                                            >
                                                {reservation.trip.bus.type}
                                            </Badge>
                                        )}
                                    </div>
                                </CardTitle>
                                <CardDescription>
                                    Details about your scheduled trip
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
                                                {reservation.trip?.origin_city?.name || 'Unknown'} → {reservation.trip?.destination_city?.name || 'Unknown'}
                                            </p>
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center space-x-2">
                                                <Calendar className="h-4 w-4 text-muted-foreground" />
                                                <span className="text-sm font-medium">Departure Date</span>
                                            </div>
                                            <p className="text-lg">
                                                {reservation.trip?.departure_time && new Date(reservation.trip.departure_time).toLocaleDateString('en-EG', {
                                                    weekday: 'long',
                                                    year: 'numeric',
                                                    month: 'long',
                                                    day: 'numeric'
                                                })}
                                            </p>
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center space-x-2">
                                                <Clock className="h-4 w-4 text-muted-foreground" />
                                                <span className="text-sm font-medium">Departure Time</span>
                                            </div>
                                            <p className="text-lg">
                                                {reservation.trip?.departure_time && formatTime(reservation.trip.departure_time)}
                                            </p>
                                        </div>
                                    </div>

                                    <div className="space-y-4">
                                        {reservation.trip?.bus && (
                                            <div className="space-y-2">
                                                <div className="flex items-center space-x-2">
                                                    <Bus className="h-4 w-4 text-muted-foreground" />
                                                    <span className="text-sm font-medium">Bus Information</span>
                                                </div>
                                                <div className="space-y-1">
                                                    <p className="text-lg font-semibold">{reservation.trip.bus.bus_code}</p>
                                                    <p className="text-sm text-muted-foreground">
                                                        {reservation.trip.bus.type} • {reservation.trip.bus.capacity} seats
                                                    </p>
                                                </div>
                                            </div>
                                        )}

                                        <div className="space-y-2">
                                            <div className="flex items-center space-x-2">
                                                <Users className="h-4 w-4 text-muted-foreground" />
                                                <span className="text-sm font-medium">Seats Reserved</span>
                                            </div>
                                            <p className="text-lg font-semibold">{reservation.seats_count} seat{reservation.seats_count > 1 ? 's' : ''}</p>
                                        </div>

                                        <div className="space-y-2">
                                            <div className="flex items-center space-x-2">
                                                <CreditCard className="h-4 w-4 text-muted-foreground" />
                                                <span className="text-sm font-medium">Total Price</span>
                                            </div>
                                            <p className="text-2xl font-bold text-primary">
                                                {formatPrice(reservation.total_price)}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Booking Summary</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Reservation Code:</span>
                                        <span className="text-sm font-medium">{reservation.reservation_code}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Status:</span>
                                        <div>{getStatusBadge(reservation.status)}</div>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Seats:</span>
                                        <span className="text-sm font-medium">{reservation.seats_count}</span>
                                    </div>
                                    <div className="flex justify-between">
                                        <span className="text-sm text-muted-foreground">Price per seat:</span>
                                        <span className="text-sm font-medium">
                                            {reservation.trip?.price && formatPrice(reservation.trip.price)}
                                        </span>
                                    </div>
                                    <div className="flex justify-between border-t pt-2">
                                        <span className="text-sm font-medium">Total:</span>
                                        <span className="text-sm font-bold">{formatPrice(reservation.total_price)}</span>
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

                        <Card>
                            <CardHeader>
                                <CardTitle>Important Information</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Cancellation Policy</h4>
                                    <p className="text-sm text-muted-foreground">
                                        You can cancel your reservation up until the departure time. 
                                        Cancelled reservations cannot be restored.
                                    </p>
                                </div>
                                
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Boarding Information</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Please arrive at the departure location at least 30 minutes before 
                                        your scheduled departure time.
                                    </p>
                                </div>

                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Contact Support</h4>
                                    <p className="text-sm text-muted-foreground">
                                        If you have any questions about your reservation, please contact 
                                        our customer support team.
                                    </p>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>

            {/* Cancel Dialog */}
            <Dialog open={showCancelDialog} onOpenChange={setShowCancelDialog}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Cancel Reservation</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to cancel reservation{' '}
                            <strong>{reservation.reservation_code}</strong>? This action cannot be undone and 
                            your seats will be released for other passengers.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => setShowCancelDialog(false)}
                            disabled={isCancelling}
                        >
                            Keep Reservation
                        </Button>
                        <Button 
                            variant="destructive" 
                            onClick={handleCancel}
                            disabled={isCancelling}
                        >
                            {isCancelling ? 'Cancelling...' : 'Cancel Reservation'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}