import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/input-error';
import { ArrowLeft, MapPin } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

export default function CreateCity() {
    const { data, setData, post, processing, errors, reset } = useForm({
        name: '',
        code: '',
        latitude: '',
        longitude: '',
        is_active: true,
        sort_order: '0',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('admin.cities.store'), {
            onSuccess: () => reset(),
        });
    };

    const breadcrumbs = [
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Admin Dashboard', href: route('admin.dashboard') },
        { title: 'City Management', href: route('admin.cities.index') },
        { title: 'Create City', href: route('admin.cities.create') },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create City" />
            
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
                                <h1 className="text-2xl font-bold tracking-tight">Create City</h1>
                                <p className="text-muted-foreground">
                                    Add a new city to available travel destinations
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
                                    Enter the city details and location information.
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
                                            <MapPin className="mr-2 h-4 w-4" />
                                            {processing ? 'Creating...' : 'Create City'}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
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
                                <CardTitle>City Code Guidelines</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Format</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Use 3-letter airport codes when available (CAI, ALX, LXR) or create meaningful abbreviations.
                                    </p>
                                </div>
                                
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Requirements</h4>
                                    <ul className="text-sm text-muted-foreground space-y-1">
                                        <li>• Must be unique</li>
                                        <li>• 3-10 characters</li>
                                        <li>• Uppercase letters only</li>
                                        <li>• No spaces or symbols</li>
                                    </ul>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Sort Order</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Priority Levels</h4>
                                    <ul className="text-sm text-muted-foreground space-y-1">
                                        <li>• 0-10: Major cities</li>
                                        <li>• 11-30: Regional capitals</li>
                                        <li>• 31-50: Tourist destinations</li>
                                        <li>• 51+: Other cities</li>
                                    </ul>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
