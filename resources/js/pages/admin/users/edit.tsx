import { Head, Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import InputError from '@/components/input-error';
import { useInitials } from '@/hooks/use-initials';
import { type User } from '@/types';
import { ArrowLeft, Save, CheckCircle, XCircle } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

interface EditUserProps {
    user: User;
}

export default function EditUser({ user }: EditUserProps) {
    const getInitials = useInitials();
    const { data, setData, patch, processing, errors } = useForm({
        name: user.name,
        email: user.email,
        role: user.role,
        password: '',
        password_confirmation: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        
        // Remove empty password fields
        const { password, password_confirmation, ...submitData } = data;
        if (password) {
            Object.assign(submitData, { password, password_confirmation });
        }
        
        patch(route('admin.users.update', user.id));
    };

    const breadcrumbs = [
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Admin Dashboard', href: route('admin.dashboard') },
        { title: 'Users', href: route('admin.users.index') },
        { title: 'Edit User', href: route('admin.users.edit', user.id) },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`Edit ${user.name}`} />
            
            <div className="flex-1 space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <div className="flex items-center space-x-2">
                            <Link href={route('admin.users.index')}>
                                <Button variant="outline" size="icon">
                                    <ArrowLeft className="h-4 w-4" />
                                </Button>
                            </Link>
                            <div>
                                <h1 className="text-2xl font-bold tracking-tight">Edit User</h1>
                                <p className="text-muted-foreground">
                                    Update user information and settings
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div className="grid gap-6 md:grid-cols-3">
                    <div className="md:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>User Information</CardTitle>
                                <CardDescription>
                                    Update the user's basic information and account settings.
                                </CardDescription>
                            </CardHeader>
                            <CardContent>
                                <form onSubmit={submit} className="space-y-6">
                                    <div className="grid gap-6 md:grid-cols-2">
                                        <div className="space-y-2">
                                            <Label htmlFor="name">Full Name</Label>
                                            <Input
                                                id="name"
                                                type="text"
                                                value={data.name}
                                                onChange={(e) => setData('name', e.target.value)}
                                                placeholder="Enter full name"
                                                required
                                            />
                                            <InputError message={errors.name} />
                                        </div>

                                        <div className="space-y-2">
                                            <Label htmlFor="email">Email Address</Label>
                                            <Input
                                                id="email"
                                                type="email"
                                                value={data.email}
                                                onChange={(e) => setData('email', e.target.value)}
                                                placeholder="Enter email address"
                                                required
                                            />
                                            <InputError message={errors.email} />
                                        </div>
                                    </div>

                                    <div className="space-y-2">
                                        <Label htmlFor="role">Role</Label>
                                        <Select
                                            value={data.role}
                                            onValueChange={(value: 'admin' | 'user') => setData('role', value)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder="Select a role" />
                                            </SelectTrigger>
                                            <SelectContent>
                                                <SelectItem value="user">User</SelectItem>
                                                <SelectItem value="admin">Admin</SelectItem>
                                            </SelectContent>
                                        </Select>
                                        <InputError message={errors.role} />
                                        <p className="text-sm text-muted-foreground">
                                            Admin users have full access to the system, while regular users have limited access.
                                        </p>
                                    </div>

                                    <div className="space-y-4">
                                        <div className="border-t pt-6">
                                            <h3 className="text-lg font-medium mb-4">Change Password</h3>
                                            <p className="text-sm text-muted-foreground mb-4">
                                                Leave password fields empty to keep the current password.
                                            </p>
                                            
                                            <div className="grid gap-6 md:grid-cols-2">
                                                <div className="space-y-2">
                                                    <Label htmlFor="password">New Password</Label>
                                                    <Input
                                                        id="password"
                                                        type="password"
                                                        value={data.password}
                                                        onChange={(e) => setData('password', e.target.value)}
                                                        placeholder="Enter new password"
                                                    />
                                                    <InputError message={errors.password} />
                                                </div>

                                                <div className="space-y-2">
                                                    <Label htmlFor="password_confirmation">Confirm New Password</Label>
                                                    <Input
                                                        id="password_confirmation"
                                                        type="password"
                                                        value={data.password_confirmation}
                                                        onChange={(e) => setData('password_confirmation', e.target.value)}
                                                        placeholder="Confirm new password"
                                                    />
                                                    <InputError message={errors.password_confirmation} />
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div className="flex items-center justify-end space-x-2">
                                        <Link href={route('admin.users.index')}>
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
                                <CardTitle>Current User</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="flex items-center space-x-3">
                                    <Avatar className="h-12 w-12">
                                        <AvatarImage src={user.avatar} alt={user.name} />
                                        <AvatarFallback>
                                            {getInitials(user.name)}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div>
                                        <p className="font-medium">{user.name}</p>
                                        <p className="text-sm text-muted-foreground">{user.email}</p>
                                    </div>
                                </div>
                                
                                <div className="space-y-2">
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Role</span>
                                        <Badge variant={user.role === 'admin' ? 'default' : 'secondary'}>
                                            {user.role}
                                        </Badge>
                                    </div>
                                    
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Status</span>
                                        {user.email_verified_at ? (
                                            <Badge variant="outline" className="text-green-600 border-green-600">
                                                <CheckCircle className="mr-1 h-3 w-3" />
                                                Verified
                                            </Badge>
                                        ) : (
                                            <Badge variant="outline" className="text-orange-600 border-orange-600">
                                                <XCircle className="mr-1 h-3 w-3" />
                                                Unverified
                                            </Badge>
                                        )}
                                    </div>
                                    
                                    <div className="flex items-center justify-between">
                                        <span className="text-sm font-medium">Joined</span>
                                        <span className="text-sm text-muted-foreground">
                                            {new Date(user.created_at).toLocaleDateString()}
                                        </span>
                                    </div>
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Account Actions</CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-4">
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Email Verification</h4>
                                    <p className="text-sm text-muted-foreground">
                                        {user.email_verified_at 
                                            ? 'This user has verified their email address.'
                                            : 'This user has not verified their email address yet.'
                                        }
                                    </p>
                                </div>
                                
                                <div className="space-y-2">
                                    <h4 className="text-sm font-medium">Password Reset</h4>
                                    <p className="text-sm text-muted-foreground">
                                        Use the form to update the user's password or leave empty to keep current password.
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