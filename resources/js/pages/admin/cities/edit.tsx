import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/input-error';
import { type City } from '@/types';
import { ArrowLeft, Save, MapPin, CheckCircle, XCircle } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

interface EditCityProps {
    city: City;
}

export default function EditCity({ city }: EditCityProps) {
    const { data, setData, patch, processing, errors } = useForm({
        name: city.name,
        code: city.code,
        latitude: city.latitude?.toString() || '',
        longitude: city.longitude?.toString() || '',
        is_active: city.is_active,
        sort_order: city.sort_order.toString(),
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('admin.cities.update', city.id));
    };

    const breadcrumbs = [
        { title: 'Dashboard', href: route('admin.dashboard') },
        { title: 'Admin Dashboard', href: route('admin.dashboard') },
        { title: 'City Management', href: route('admin.cities.index') },
        { title: 'Edit City', href: route('admin.cities.edit', city.id) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${city.name}`} />
            
            <div className="flex-1 space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <div className="flex items-center space-x-2">
                            <Link href={route('admin.cities.index')}>
                                <Button variant="outline" size="icon">
                                    <ArrowLeft className="h-4 w-4" />
                                </Button>
                            </Link>
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">Edit City</h1>
                                <p className="text-muted-foreground">
                                    Update city information and settings
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    <div className="md:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>City Information</CardTitle>
                                <CardDescription>
                                    Update the city details and location information.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={submit} className="space-y-6">
                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="name">City Name</Label>
                                            <Input
                                                id="name"
                                                type="text"
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                placeholder="Enter city name (e.g., Cairo)"
                                                required
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="code">City Code</Label>
                                            <Input
                                                id="code"
                                                type="text"
                                                value={data.code}
                                                onChange={(e) => setData('code', e.target.value.toUpperCase())}
                                                placeholder="Enter city code (e.g., CAI)"
                                                maxLength={10}
                                                required
                                            />
                                            <InputError message={errors.code} />
                                            <p className="text-sm text-muted-foreground">
                                                Short unique code for the city (3-10 characters).
                                            </p>
                                        </div>
                                    </div>

                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="latitude">Latitude</Label>
                                            <Input
                                                id="latitude"
                                                type="number"
                                                step="any"
                                                min="-90"
                                                max="90"
                                                value={data.latitude}
                                                onChange={(e) => setData('latitude', e.target.value)}
                                                placeholder="Enter latitude (e.g., 30.0444)"
                                            />
                                            <InputError message={errors.latitude} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="longitude">Longitude</Label>
                                            <Input
                                                id="longitude"
                                                type="number"
                                                step="any"
                                                min="-180"
                                                max="180"
                                                value={data.longitude}
                                                onChange={(e) => setData('longitude', e.target.value)}
                                                placeholder="Enter longitude (e.g., 31.2357)"
                                            />
                                            <InputError message={errors.longitude} />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="sort_order">Sort Order</Label>
                                        <Input
                                            id="sort_order"
                                            type="number"
                                            min="0"
                                            value={data.sort_order}
                                            onChange={(e) => setData('sort_order', e.target.value)}
                                            placeholder="Enter sort order (0 for highest priority)"
                                        />
                                        <InputError message={errors.sort_order} />
                                        <p className="text-sm text-muted-foreground">
                                            Lower numbers appear first in lists. Major cities typically use 0-10.
                                        </p>
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
                                        Only active cities are available for trip bookings.
                                    </p>

                                    <div className="flex items-center justify-end space-x-2">
                                        <Link href={route('admin.cities.index')}>
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

                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Current City</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex items-center space-x-3">
                                    <div className="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                                        <MapPin className="h-6 w-6 text-primary" />
                                    </div>
                                    <div>
                                        <p className="font-medium">{city.name}</p>
                                        <p className="text-sm text-muted-foreground">Code: {city.code}</p>
                                    </div>
                                </div>
                                
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Status</span>
                                        {city.is_active ? (
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
                                        <span className="text-sm font-medium">Sort Order</span>
                                        <span className="text-sm text-muted-foreground">{city.sort_order}</span>
                                    </div>
                                    
                                    {city.latitude && city.longitude && (
                                        <div className="flex items-center justify-between">
                                            <span className="text-sm font-medium">Coordinates</span>
                                            <span className="text-sm text-muted-foreground">
                                                {Number(city.latitude).toFixed(4)}, {Number(city.longitude).toFixed(4)}
                                            </span>
                                        </div>
                                    )}
                                    
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Created</span>
                                        <span className="text-sm text-muted-foreground">
                                            {new Date(city.created_at).toLocaleDateString('en-EG')}
                                        </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Location Guidelines</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Coordinates</h4>
                                    <ul className="text-sm text-muted-foreground space-y-1">
                                        <li>• Latitude: -90 to 90 degrees</li>
                                        <li>• Longitude: -180 to 180 degrees</li>
                                        <li>• Use decimal degrees format</li>
                                        <li>• Optional but recommended</li>
                                    </ul>
                                </div>
                                
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Egypt Examples</h4>
                                    <ul className="text-sm text-muted-foreground space-y-1">
                                        <li>• Cairo: 30.0444, 31.2357</li>
                                        <li>• Alexandria: 31.2001, 29.9187</li>
                                        <li>• Luxor: 25.6872, 32.6396</li>
                                        <li>• Aswan: 24.0889, 32.8998</li>
                                    </ul>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Guidelines</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">City Code</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Changing the city code may affect existing trip routes and bookings.
                                    </p>
                                </div>
                                
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Status</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Deactivating a city will hide it from new trip creation but won't affect existing trips.
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
