import React, { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import axios from '../lib/axios';
import AdminLayout from '../layouts/AdminLayout';
import { FiTrendingUp } from 'react-icons/fi';

export default function Dashboard() {
    const [user, setUser] = useState(null);
    const [loading, setLoading] = useState(true);
    const navigate = useNavigate();

    useEffect(() => {
        const fetchUser = async () => {
            try {
                const response = await axios.get('/api/me');
                const userData = response.data;
                setUser(userData);
                
                // Jika super admin akses dashboard, lempar ke license
                if (userData.role === 'super_admin') {
                    navigate('/admin/licenses');
                }
            } catch (error) {
                console.error('Error fetching user:', error);
                if (error.response && (error.response.status === 401 || error.response.status === 403)) {
                    navigate('/login');
                }
            } finally {
                setLoading(false);
            }
        };

        fetchUser();
    }, [navigate]);

    if (loading || !user) {
        return (
            <AdminLayout user={user}>
                <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-8 h-[60vh] flex items-center justify-center">
                    <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#901C31]"></div>
                </div>
            </AdminLayout>
        );
    }

    return (
        <AdminLayout user={user}>
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-8">
                <h3 className="text-2xl font-bold text-gray-800 mb-2">Selamat Datang, {user.username}!</h3>
                <p className="text-gray-600 mb-6">Anda sedang login sebagai <span className="font-semibold uppercase text-[#901C31]">{user.role}</span>.</p>
                
                <div className="border-4 border-dashed border-gray-100 rounded-lg h-64 flex flex-col items-center justify-center text-center p-6">
                    <div className="w-16 h-16 bg-rose-50 text-[#901C31] rounded-full flex items-center justify-center mb-4">
                        <FiTrendingUp className="w-8 h-8" />
                    </div>
                    <h4 className="text-lg font-medium text-gray-800">Area Dashboard Utama</h4>
                    <p className="text-gray-500 text-sm max-w-sm mt-1">
                        Sistem sedang memuat metrik penjualan Anda. Silakan navigasi melalui menu di sebelah kiri untuk mengelola properti.
                    </p>
                </div>
            </div>
        </AdminLayout>
    );
}
