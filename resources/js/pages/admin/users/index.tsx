import { Head, Link, router } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { useInitials } from '@/hooks/use-initials';
import { type User } from '@/types';
import { Search, MoreHorizontal, UserPlus, Edit, Trash2, Shield, User as UserIcon, CheckCircle, XCircle } from 'lucide-react';
import AppLayout from '@/layouts/app-layout';

interface AdminUsersProps {
    users: {
        data: User[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    filters: {
        search?: string;
    };
}

export default function AdminUsers({ users, filters }: AdminUsersProps) {
    const getInitials = useInitials();
    const [search, setSearch] = useState(filters.search || '');
    const [deleteUser, setDeleteUser] = useState<User | null>(null);
    const [isDeleting, setIsDeleting] = useState(false);

    // Debounced search
    useEffect(() => {
        const timeoutId = setTimeout(() => {
            if (search !== filters.search) {
                router.get(route('admin.users.index'), { search }, {
                    preserveState: true,
                    replace: true,
                });
            }
        }, 300);

        return () => clearTimeout(timeoutId);
    }, [search, filters.search]);

    const handleDeleteUser = async () => {
        if (!deleteUser) return;
        
        setIsDeleting(true);
        router.delete(route('admin.users.destroy', deleteUser.id), {
            onSuccess: () => {
                setDeleteUser(null);
            },
            onFinish: () => {
                setIsDeleting(false);
            },
        });
    };

    const handlePageChange = (page: number) => {
        router.get(route('admin.users.index'), { 
            ...filters, 
            page 
        }, {
            preserveState: true,
        });
    };

    const breadcrumbs = [
        { title: 'Dashboard', href: route('dashboard') },
        { title: 'Admin Dashboard', href: route('admin.dashboard') },
        { title: 'Users', href: route('admin.users.index') },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Users Management" />
            
            <div className="flex-1 space-y-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold tracking-tight">Users Management</h1>
                        <p className="text-muted-foreground">
                            Manage user accounts and permissions
                        </p>
                    </div>
                    <Link href={route('admin.users.create')}>
                        <Button>
                            <UserPlus className="mr-2 h-4 w-4" />
                            Add User
                        </Button>
                    </Link>
                </div>

                <Card className="shadow-none p-0 border-none">
                    <CardHeader className="p-0">
                        <div className="flex items-center justify-between">
                            <div>
                                <CardTitle>All Users</CardTitle>
                                <CardDescription>
                                    {users.total} users total
                                </CardDescription>
                            </div>
                            <div className="relative w-64">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    placeholder="Search users..."
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    className="pl-9"
                                />
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        <div className="space-y-4">
                            {users.data.length === 0 ? (
                                <div className="text-center py-8">
                                    <UserIcon className="mx-auto h-12 w-12 text-muted-foreground" />
                                    <h3 className="mt-2 text-sm font-semibold">No users found</h3>
                                    <p className="mt-1 text-sm text-muted-foreground">
                                        {search ? 'Try adjusting your search terms.' : 'Get started by creating a new user.'}
                                    </p>
                                    {!search && (
                                        <div className="mt-6">
                                            <Link href={route('admin.users.create')}>
                                                <Button>
                                                    <UserPlus className="mr-2 h-4 w-4" />
                                                    Add User
                                                </Button>
                                            </Link>
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <>
                                    {/* Users Table */}
                                    <div className="rounded-md border">
                                        <div className="overflow-x-auto">
                                            <table className="w-full">
                                                <thead className="border-b bg-muted/50">
                                                    <tr>
                                                        <th className="text-left p-4 font-medium">User</th>
                                                        <th className="text-left p-4 font-medium">Role</th>
                                                        <th className="text-left p-4 font-medium">Status</th>
                                                        <th className="text-left p-4 font-medium">Joined</th>
                                                        <th className="text-right p-4 font-medium">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    {users.data.map((user) => (
                                                        <tr key={user.id} className="border-b hover:bg-muted/50">
                                                            <td className="p-4">
                                                                <div className="flex items-center space-x-3">
                                                                    <Avatar className="h-10 w-10">
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
                                                            </td>
                                                            <td className="p-4">
                                                                <Badge variant={user.role === 'admin' ? 'default' : 'secondary'}>
                                                                    {user.role === 'admin' ? (
                                                                        <>
                                                                            <Shield className="mr-1 h-3 w-3" />
                                                                            Admin
                                                                        </>
                                                                    ) : (
                                                                        <>
                                                                            <UserIcon className="mr-1 h-3 w-3" />
                                                                            User
                                                                        </>
                                                                    )}
                                                                </Badge>
                                                            </td>
                                                            <td className="p-4">
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
                                                            </td>
                                                            <td className="p-4 text-sm text-muted-foreground">
                                                                {new Date(user.created_at).toLocaleDateString()}
                                                            </td>
                                                            <td className="p-4 text-right">
                                                                <DropdownMenu>
                                                                    <DropdownMenuTrigger asChild>
                                                                        <Button variant="ghost" size="icon">
                                                                            <MoreHorizontal className="h-4 w-4" />
                                                                        </Button>
                                                                    </DropdownMenuTrigger>
                                                                    <DropdownMenuContent align="end">
                                                                                                                                <DropdownMenuItem asChild>
                                                            <Link href={route('admin.users.edit', user.id)}>
                                                                <Edit className="mr-2 h-4 w-4" />
                                                                Edit
                                                            </Link>
                                                        </DropdownMenuItem>
                                                                        <DropdownMenuSeparator />
                                                                        <DropdownMenuItem 
                                                                            onClick={() => setDeleteUser(user)}
                                                                            className="text-red-600 focus:text-red-600"
                                                                        >
                                                                            <Trash2 className="mr-2 h-4 w-4" />
                                                                            Delete
                                                                        </DropdownMenuItem>
                                                                    </DropdownMenuContent>
                                                                </DropdownMenu>
                                                            </td>
                                                        </tr>
                                                    ))}
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>

                                    {/* Pagination */}
                                    {users.last_page > 1 && (
                                        <div className="flex items-center justify-between">
                                            <div className="text-sm text-muted-foreground">
                                                Showing {users.from} to {users.to} of {users.total} users
                                            </div>
                                            <div className="flex items-center space-x-2">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => handlePageChange(users.current_page - 1)}
                                                    disabled={users.current_page === 1}
                                                >
                                                    Previous
                                                </Button>
                                                
                                                <div className="flex items-center space-x-1">
                                                    {Array.from({ length: Math.min(5, users.last_page) }, (_, i) => {
                                                        const page = users.current_page <= 3 
                                                            ? i + 1 
                                                            : users.current_page + i - 2;
                                                        
                                                        if (page > users.last_page) return null;
                                                        
                                                        return (
                                                            <Button
                                                                key={page}
                                                                variant={page === users.current_page ? "default" : "outline"}
                                                                size="sm"
                                                                onClick={() => handlePageChange(page)}
                                                                className="w-8 h-8 p-0"
                                                            >
                                                                {page}
                                                            </Button>
                                                        );
                                                    })}
                                                </div>
                                                
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() => handlePageChange(users.current_page + 1)}
                                                    disabled={users.current_page === users.last_page}
                                                >
                                                    Next
                                                </Button>
                                            </div>
                                        </div>
                                    )}
                                </>
                            )}
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Delete Confirmation Dialog */}
            <Dialog open={!!deleteUser} onOpenChange={() => setDeleteUser(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Delete User</DialogTitle>
                        <DialogDescription>
                            Are you sure you want to delete <strong>{deleteUser?.name}</strong>? 
                            This action cannot be undone and will permanently remove the user account.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" onClick={() => setDeleteUser(null)}>
                            Cancel
                        </Button>
                        <Button 
                            variant="destructive" 
                            onClick={handleDeleteUser}
                            disabled={isDeleting}
                        >
                            {isDeleting ? 'Deleting...' : 'Delete User'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </AppLayout>
    );
} 