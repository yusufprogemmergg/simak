import React from 'react';
import { Link, useNavigate, useLocation } from 'react-router-dom';
import { FiHome, FiFolder, FiKey, FiBox, FiMenu } from 'react-icons/fi';
import axios from '../lib/axios';

export default function AdminLayout({ children, user }) {
    const navigate = useNavigate();
    const location = useLocation();

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
        { name: 'Dashboard', href: '/dashboard', icon: FiHome },
        { name: 'Project', href: '/projects', icon: FiFolder },
        ...(user?.role === 'super_admin' ? [
            { name: 'Licenses', href: '/admin/licenses', icon: FiKey }
        ] : []),
    ];

    return (
        <div className="min-h-screen bg-gray-100 flex">
            {/* Sidebar */}
            <div className="w-64 bg-white border-r border-gray-200 hidden md:flex flex-col">
                <div className="h-16 flex items-center px-6 border-b border-gray-200">
                    <div className="w-8 h-8 bg-[#901C31] rounded flex items-center justify-center mr-3">
                        <FiBox className="w-5 h-5 text-white" />
                    </div>
                    <span className="font-bold text-gray-800 text-lg">Simak App</span>
                </div>
                
                <nav className="flex-1 px-4 py-6 space-y-1 overflow-y-auto">
                    {navigation.map((item) => {
                        const isActive = location.pathname.startsWith(item.href);
                        return (
                            <Link
                                key={item.name}
                                to={item.href}
                                className={`flex items-center px-3 py-2.5 text-sm font-medium rounded-md transition-colors ${
                                    isActive ? 'bg-rose-50 text-[#901C31]' : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                                }`}
                            >
                                <item.icon className={`mr-3 h-5 w-5 ${isActive ? 'text-[#901C31]' : 'text-gray-400'}`} />
                                {item.name}
                            </Link>
                        );
                    })}
                </nav>

                <div className="p-4 border-t border-gray-200">
                    <div className="flex items-center">
                        <div className="flex-shrink-0">
                            <div className="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center text-[#901C31] font-bold uppercase">
                                {user?.username?.[0] || 'U'}
                            </div>
                        </div>
                        <div className="ml-3">
                            <p className="text-sm font-medium text-gray-700">{user?.username}</p>
                            <p className="text-xs font-medium text-gray-500 capitalize">{user?.role}</p>
                        </div>
                    </div>
                </div>
            </div>

            {/* Main Area */}
            <div className="flex-1 flex flex-col min-w-0">
                {/* Mobile Topbar */}
                <div className="md:hidden flex items-center justify-between bg-white border-b border-gray-200 px-4 py-3">
                    <span className="font-bold text-gray-800">Simak App</span>
                    <button className="text-gray-500 hover:text-gray-700">
                        <FiMenu className="w-6 h-6" />
                    </button>
                </div>

                {/* Header */}
                <header className="bg-white border-b border-gray-200 px-8 py-4 flex items-center justify-between hidden md:flex h-16">
                    <h2 className="text-xl font-semibold text-gray-800 capitalize">
                        {location.pathname.split('/').pop() || 'Dashboard'}
                    </h2>
                    <button
                        onClick={handleLogout}
                        className="text-sm font-medium text-gray-500 hover:text-red-600 transition-colors"
                    >
                        Sign out
                    </button>
                </header>

                {/* Content */}
                <main className="flex-1 p-6 sm:p-8 overflow-y-auto">
                    {children}
                </main>
            </div>
        </div>
    );
}
