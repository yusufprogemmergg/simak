import React, { useState, useEffect } from 'react';
import { Link, useNavigate, useLocation, Outlet } from 'react-router-dom';
import { FiHome, FiFolder, FiKey, FiBox, FiMenu, FiBriefcase, FiX, FiShoppingCart, FiUsers, FiUser } from 'react-icons/fi';
import axios from '../lib/axios';

export default function AdminLayout() {
    const navigate = useNavigate();
    const location = useLocation();
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const [isSidebarOpen, setIsSidebarOpen] = useState(false);

    useEffect(() => {
        const fetchUser = async () => {
            try {
                const res = await axios.get('/api/me');
                setUser(res.data);
            } catch (err) {
                console.error('Failed to fetch user in global layout:', err);
            } finally {
                setLoading(false);
            }
        };
        
        if (localStorage.getItem('isLoggedIn') === 'true') {
            fetchUser();
        } else {
            setLoading(false);
        }
    }, []);

    const handleLogout = async () => {
        try {
            await axios.post('/api/auth/logout');
            localStorage.removeItem('isLoggedIn');
            navigate('/login');
        } catch (error) {
            console.error('Logout error:', error);
        }
    };

    const navigation = [
        { name: 'Dashboard', href: '/dashboard', icon: FiHome, roles: ['owner', 'super_admin', 'sales'] },
        { name: 'Penjualan', href: '/penjualan', icon: FiShoppingCart, roles: ['owner', 'sales'] },
        { name: 'Project', href: '/projects', icon: FiFolder, roles: ['owner'] },
        { name: 'Kavling', href: '/kavling', icon: FiBox, roles: ['owner'] },
        { name: 'Buyer', href: '/master/buyer', icon: FiUser, roles: ['owner'] },
        { name: 'Tim Marketing', href: '/master/sales', icon: FiUsers, roles: ['owner'] },
        { name: 'Profil Perusahaan', href: '/profile-perusahaan', icon: FiBriefcase, roles: ['owner'] },
        { name: 'Licenses', href: '/admin/licenses', icon: FiKey, roles: ['super_admin'] }
    ];

    const filteredNavigation = navigation.filter(item => {
        if (!user) return false;
        const userRole = user.role || 'owner';
        return item.roles.includes(userRole);
    });

    const SidebarContent = () => (
        <div className="flex flex-col h-full bg-white">
            <div className="h-16 flex items-center justify-between px-6 border-b border-gray-100">
                <div className="flex items-center">
                    <div className="w-8 h-8 bg-[#901C31] rounded flex items-center justify-center mr-3">
                        <FiBox className="w-5 h-5 text-white" />
                    </div>
                    <span className="font-bold text-gray-800 text-lg">Simak App</span>
                </div>
                <button 
                    onClick={() => setIsSidebarOpen(false)}
                    className="md:hidden text-gray-500 hover:text-gray-700"
                >
                    <FiX className="w-6 h-6" />
                </button>
            </div>
            
            <nav className="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                {!user ? (
                    <div className="animate-pulse space-y-3">
                        <div className="h-10 bg-gray-100 rounded-md w-full"></div>
                        <div className="h-10 bg-gray-100 rounded-md w-full"></div>
                        <div className="h-10 bg-gray-100 rounded-md w-full"></div>
                    </div>
                ) : (
                    filteredNavigation.map((item) => {
                        const isActive = location.pathname.startsWith(item.href);
                        return (
                            <Link
                                key={item.name}
                                to={item.href}
                                onClick={() => setIsSidebarOpen(false)}
                                className={`flex items-center px-3 py-2.5 text-sm font-semibold rounded-lg transition-all ${
                                    isActive ? 'bg-rose-50 text-[#901C31]' : 'text-gray-600 hover:bg-gray-50'
                                }`}
                            >
                                <item.icon className={`mr-3 h-5 w-5 ${isActive ? 'text-[#901C31]' : 'text-gray-400'}`} />
                                {item.name}
                            </Link>
                        );
                    })
                )}
            </nav>

            <div className="p-4 border-t border-gray-100">
                <div className="flex items-center">
                    <div className="w-8 h-8 rounded-full bg-rose-50 flex items-center justify-center text-[#901C31] font-bold uppercase text-xs">
                        {user?.username?.[0] || 'U'}
                    </div>
                    <div className="ml-3 overflow-hidden">
                        <p className="text-sm font-bold text-gray-700 truncate">{user?.username || 'User'}</p>
                        <p className="text-[10px] font-bold text-gray-400 uppercase tracking-tight">{user?.role}</p>
                    </div>
                </div>
            </div>
        </div>
    );

    return (
        <div className="h-screen bg-gray-50 flex overflow-hidden">
            {/* Desktop Sidebar */}
            <div className="w-64 border-r border-gray-200 hidden md:flex flex-col">
                <SidebarContent />
            </div>

            {/* Mobile Sidebar (Drawer) */}
            <div className={`fixed inset-0 z-50 md:hidden ${isSidebarOpen ? 'visible' : 'invisible'}`}>
                {/* Backdrop */}
                <div 
                    className={`absolute inset-0 bg-black/50 transition-opacity duration-300 ${isSidebarOpen ? 'opacity-100' : 'opacity-0'}`}
                    onClick={() => setIsSidebarOpen(false)}
                />
                {/* Sidebar */}
                <div className={`absolute top-0 bottom-0 left-0 w-72 transition-transform duration-300 transform ${isSidebarOpen ? 'translate-x-0' : '-translate-x-full'}`}>
                    <SidebarContent />
                </div>
            </div>

            {/* Main Area */}
            <div className="flex-1 flex flex-col min-w-0 overflow-hidden">
                {/* Topbar (Mobile) */}
                <div className="md:hidden flex items-center justify-between bg-white border-b border-gray-200 px-4 h-16 shrink-0">
                    <button 
                        onClick={() => setIsSidebarOpen(true)}
                        className="p-2 text-gray-500 hover:text-[#901C31] transition-colors"
                    >
                        <FiMenu className="w-6 h-6" />
                    </button>
                    <span className="font-bold text-gray-800">Simak App</span>
                    <div className="w-10" /> {/* Spacer */}
                </div>

                {/* Header (Desktop) */}
                <header className="bg-white border-b border-gray-200 px-8 h-16 items-center justify-between hidden md:flex shrink-0">
                    <h2 className="text-xl font-bold text-gray-800 capitalize">
                        {location.pathname.split('/').pop() || 'Dashboard'}
                    </h2>
                    <div className="flex items-center gap-4">
                        <button
                            onClick={handleLogout}
                            className="text-xs font-bold text-gray-400 hover:text-red-600 uppercase tracking-widest transition-colors"
                        >
                            Sign out
                        </button>
                    </div>
                </header>

                {/* Content */}
                <main className="flex-1 p-4 sm:p-8 overflow-y-auto">
                    {loading ? (
                        <div className="h-[60vh] flex items-center justify-center">
                            <div className="animate-spin rounded-full h-10 w-10 border-b-2 border-[#901C31]"></div>
                        </div>
                    ) : (
                        <Outlet context={{ user }} />
                    )}
                </main>
            </div>
        </div>
    );
}
