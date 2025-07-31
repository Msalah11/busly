import { Head } from '@inertiajs/react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useInitials } from '@/hooks/use-initials';
import { type User } from '@/types';
import { Link } from '@inertiajs/react';
import { Users, UserPlus, Shield, Clock } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

interface AdminDashboardProps {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
    };
}

export default function AdminDashboard({ users }: AdminDashboardProps) {
    const getInitials = useInitials();
    
    const totalUsers = users.total;
    const adminUsers = users.data.filter(user => user.role === 'admin').length;
    const regularUsers = users.data.filter(user => user.role === 'user').length;
    const verifiedUsers = users.data.filter(user => user.email_verified_at !== null).length;

    const breadcrumbs = [
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Admin Dashboard', href: route('admin.dashboard') },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Admin Dashboard" />
            
            <div className="flex-1 space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Admin Dashboard</h1>
                        <p className="text-muted-foreground">
                            Manage users and system settings
                        </p>
                    </div>
                    <Link href={route('admin.users.index')}>
                        <Button>
                            <UserPlus className="mr-2 h-4 w-4" />
                            Manage Users
                        </Button>
                    </Link>
                </div>

                {/* Stats Cards */}
                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Total Users</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{totalUsers}</div>
                            <p className="text-xs text-muted-foreground">
                                All registered users
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Admin Users</CardTitle>
                            <Shield className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{adminUsers}</div>
                            <p className="text-xs text-muted-foreground">
                                Users with admin privileges
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Regular Users</CardTitle>
                            <Users className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{regularUsers}</div>
                            <p className="text-xs text-muted-foreground">
                                Standard user accounts
                            </p>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                            <CardTitle className="text-sm font-medium">Verified Users</CardTitle>
                            <Clock className="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                        <CardContent>
                            <div className="text-2xl font-bold">{verifiedUsers}</div>
                            <p className="text-xs text-muted-foreground">
                                Email verified accounts
                            </p>
                        </CardContent>
                    </Card>
                </div>

                {/* Recent Users */}
                <Card>
                    <CardHeader>
                        <CardTitle>Recent Users</CardTitle>
                        <CardDescription>
                            Latest user registrations
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div className="space-y-4">
                            {users.data.slice(0, 5).map((user) => (
                                <div key={user.id} className="flex items-center space-x-4">
                                    <Avatar className="h-9 w-9">
                                        <AvatarImage src={user.avatar} alt={user.name} />
                                        <AvatarFallback>
                                            {getInitials(user.name)}
                                        </AvatarFallback>
                                    </Avatar>
                                    <div className="flex-1 space-y-1">
                                        <p className="text-sm font-medium leading-none">
                                            {user.name}
                                        </p>
                                        <p className="text-sm text-muted-foreground">
                                            {user.email}
                                        </p>
                                    </div>
                                    <div className="flex items-center space-x-2">
                                        <Badge variant={user.role === 'admin' ? 'default' : 'secondary'}>
                                            {user.role}
                                        </Badge>
                                        {user.email_verified_at && (
                                            <Badge variant="outline" className="text-green-600 border-green-600">
                                                Verified
                                            </Badge>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                        {users.data.length > 5 && (
                            <div className="mt-4 pt-4 border-t">
                                <Link href={route('admin.users.index')}>
                                    <Button variant="outline" className="w-full">
                                        View All Users
                                    </Button>
                                </Link>
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </AppLayout>
    );
} 