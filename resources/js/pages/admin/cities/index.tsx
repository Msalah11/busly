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
import { type City } from '@/types';

interface CitiesIndexProps {
    cities: {
        data: City[];
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
        is_active?: boolean;
    };
}

export default function CitiesIndex({ cities, filters }: CitiesIndexProps) {
    const { flash } = usePage().props as { flash?: { success?: string; error?: string } };
    const [search, setSearch] = useState(filters.search || '');
    const [status, setStatus] = useState(filters.is_active === undefined ? 'all' : filters.is_active ? 'active' : 'inactive');
    const [deleteCity, setDeleteCity] = useState<City | null>(null);
    const [isDeleting, setIsDeleting] = useState(false);

    const handleSearch = () => {
        const params: Record<string, string> = {};
        if (search) params.search = search;
        if (status === 'active') params.is_active = '1';
        if (status === 'inactive') params.is_active = '0';

        router.get(route('admin.cities.index'), params, {
            preserveState: true,
            replace: true,
        });
    };

    const handleDeleteCity = async () => {
        if (!deleteCity) return;
        
        setIsDeleting(true);
        router.delete(route('admin.cities.destroy', deleteCity.id), {
            onSuccess: () => {
                setDeleteCity(null);
            },
            onFinish: () => {
                setIsDeleting(false);
            },
        });
    };

    const breadcrumbs = [
        { title: 'Dashboard', href: route('admin.dashboard') },
        { title: 'Admin Dashboard', href: route('admin.dashboard') },
        { title: 'City Management', href: route('admin.cities.index') },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="City Management" />
            
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
                        <h1 className="text-2xl font-bold tracking-tight">City Management</h1>
                        <p className="text-muted-foreground">
                            Manage cities available for bus travel routes
                        </p>
                    </div>
                    <Link href={route('admin.cities.create')}>
                        <Button>
                            <Plus className="mr-2 h-4 w-4" />
                            Add City
                        </Button>
                    </Link>
                </div>

                <Card className='rounded-sm'>
                    <CardHeader>
                        <CardTitle>Filters</CardTitle>
                        <CardDescription>
                            Search and filter cities by various criteria
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="flex flex-col gap-4 md:flex-row md:items-end">
                            <div className="flex-1">
                                <Input
                                    placeholder="Search by city name or code..."
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
                        <CardTitle>Cities ({cities.total})</CardTitle>
                        <CardDescription>
                            A list of all cities available for bus routes
                        </CardDescription>
                    </CardHeader>
                    <CardContent className='p-0'>
                        <div className="rounded-md border">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>City Name</TableHead>
                                        <TableHead>Code</TableHead>
                                        <TableHead>Coordinates</TableHead>
                                        <TableHead>Status</TableHead>
                                        <TableHead>Sort Order</TableHead>
                                        <TableHead>Created</TableHead>
                                        <TableHead className="w-[70px]">Actions</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {cities.data.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={7} className="h-24 text-center">
                                                <div className="flex flex-col items-center justify-center space-y-2">
                                                    <MapPin className="h-8 w-8 text-muted-foreground" />
                                                    <p className="text-sm text-muted-foreground">No cities found</p>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        cities.data.map((city) => (
                                            <TableRow key={city.id}>
                                                <TableCell className="font-medium">{city.name}</TableCell>
                                                <TableCell>
                                                    <Badge variant="outline">
                                                        {city.code}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    {city.latitude && city.longitude ? (
                                                        <span className="text-sm text-muted-foreground">
                                                            {Number(city.latitude).toFixed(4)}, {Number(city.longitude).toFixed(4)}
                                                        </span>
                                                    ) : (
                                                        <span className="text-sm text-muted-foreground">—</span>
                                                    )}
                                                </TableCell>
                                                <TableCell>
                                                    <Badge variant={city.is_active ? 'default' : 'destructive'}>
                                                        {city.is_active ? 'Active' : 'Inactive'}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell>
                                                    <span className="text-sm text-muted-foreground">{city.sort_order}</span>
                                                </TableCell>
                                                <TableCell>
                                                    {new Date(city.created_at).toLocaleDateString('en-EG')}
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
                                                                <Link href={route('admin.cities.edit', city.id)}>
                                                                    <Edit className="mr-2 h-4 w-4" />
                                                                    Edit
                                                                </Link>
                                                            </DropdownMenuItem>
                                                            <DropdownMenuSeparator />
                                                            <DropdownMenuItem
                                                                onClick={() => setDeleteCity(city)}
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

                        {cities.last_page > 1 && (
                            <div className="flex items-center justify-between space-x-2 py-4">
                                <div className="text-sm text-muted-foreground">
                                    Showing {((cities.current_page - 1) * cities.per_page) + 1} to{' '}
                                    {Math.min(cities.current_page * cities.per_page, cities.total)} of{' '}
                                    {cities.total} results
                                </div>
                                <div className="flex space-x-2">
                                    {cities.links.map((link, index) => (
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

            {/* Delete Confirmation Dialog */}
            <Dialog open={!!deleteCity} onOpenChange={() => setDeleteCity(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete City</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete city <strong>{deleteCity?.name}</strong>? 
                            This action cannot be undone and will permanently remove the city from available routes.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteCity(null)}>
                            Cancel
                        </Button>
                        <Button 
                            variant="destructive" 
                            onClick={handleDeleteCity}
                            disabled={isDeleting}
                        >
                            {isDeleting ? 'Deleting...' : 'Delete City'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
}
