import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Checkbox } from '@/components/ui/checkbox';
import InputError from '@/components/input-error';
import { ArrowLeft, Bus } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

export default function CreateBus() {
    const { data, setData, post, processing, errors, reset } = useForm({
        bus_code: '',
        capacity: '',
        type: 'Standard',
        is_active: true,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('admin.buses.store'), {
            onSuccess: () => reset(),
        });
    };

    const breadcrumbs = [
        { title: 'Dashboard', href: route('admin.dashboard') },
        { title: 'Admin Dashboard', href: route('admin.dashboard') },
        { title: 'Bus Management', href: route('admin.buses.index') },
        { title: 'Create Bus', href: route('admin.buses.create') },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Create Bus" />
            
            <div className="flex-1 space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <div className="flex items-center space-x-2">
                            <Link href={route('admin.buses.index')}>
                                <Button variant="outline" size="icon">
                                    <ArrowLeft className="h-4 w-4" />
                                </Button>
                            </Link>
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">Create Bus</h1>
                                <p className="text-muted-foreground">
                                    Add a new bus to your fleet
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    <div className="md:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Bus Information</CardTitle>
                                <CardDescription>
                                    Enter the bus details and configuration.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={submit} className="space-y-6">
                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="bus_code">Bus Code</Label>
                                            <Input
                                                id="bus_code"
                                                type="text"
                                                value={data.bus_code}
                                                onChange={(e) => setData('bus_code', e.target.value)}
                                                placeholder="Enter bus code (e.g., BUS001)"
                                                required
                                            />
                                            <InputError message={errors.bus_code} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="capacity">Capacity</Label>
                                            <Input
                                                id="capacity"
                                                type="number"
                                                min="1"
                                                max="100"
                                                value={data.capacity}
                                                onChange={(e) => setData('capacity', e.target.value)}
                                                placeholder="Enter seat capacity"
                                                required
                                            />
                                            <InputError message={errors.capacity} />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="type">Bus Type</Label>
                                        <Select
                                            value={data.type}
                                            onValueChange={(value: 'Standard' | 'VIP') => setData('type', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select bus type" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="Standard">Standard</SelectItem>
                                                <SelectItem value="VIP">VIP</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.type} />
                                        <p className="text-sm text-muted-foreground">
                                            Standard buses are regular seating, VIP buses have premium amenities.
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
                                        Only active buses can be assigned to trips.
                                    </p>

                                    <div className="flex items-center justify-end space-x-2">
                                        <Link href={route('admin.buses.index')}>
                                            <Button variant="outline" type="button">
                                                Cancel
                                            </Button>
                                        </Link>
                                        <Button type="submit" disabled={processing}>
                                            <Bus className="mr-2 h-4 w-4" />
                                            {processing ? 'Creating...' : 'Create Bus'}
                                        </Button>
                                    </div>
                                </form>
                            </CardContent>
                        </Card>
                    </div>

                    <div className="space-y-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Bus Types</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Standard</h4>
                                    <ul className="text-sm text-muted-foreground space-y-1">
                                        <li>• Regular seating</li>
                                        <li>• Basic amenities</li>
                                        <li>• Standard pricing</li>
                                        <li>• Most common type</li>
                                    </ul>
                                </div>
                                
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">VIP</h4>
                                    <ul className="text-sm text-muted-foreground space-y-1">
                                        <li>• Premium seating</li>
                                        <li>• Enhanced comfort</li>
                                        <li>• Additional amenities</li>
                                        <li>• Higher pricing tier</li>
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
                                    <h4 className="text-sm font-medium">Bus Code</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Use a unique identifier for each bus. Common formats include BUS001, B-001, or company-specific codes.
                                    </p>
                                </div>
                                
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Capacity</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Enter the total number of passenger seats. This will be used for reservation management.
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