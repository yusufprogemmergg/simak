import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from '../lib/axios';
import AuthLayout from '../layouts/AuthLayout';
import { FiLoader } from 'react-icons/fi';

export default function Login() {
    const [email, setEmail] = useState('');
    const [password, setPassword] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const navigate = useNavigate();

    const handleLogin = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);

        try {
            await axios.get('/sanctum/csrf-cookie');
            const res = await axios.post('/api/auth/login', { email, password });
            
            // Set login flag for quick client routing decisions
            localStorage.setItem('isLoggedIn', 'true');

            // Redirect based on role
            if (res.data?.data?.user?.role === 'super_admin') {
                navigate('/admin/licenses');
            } else {
                navigate('/dashboard');
            }
        } catch (err) {
            console.error('Login error:', err);
            setError(err.response?.data?.message || 'Kredensial tidak valid atau lisensi kadaluarsa.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <AuthLayout 
            title="Sign in to your account" 
            subtitle="Masuk untuk memonitor penjualan properti Anda"
        >
            {error && (
                <div className="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-md">
                    <p className="text-sm text-red-700 font-medium">{error}</p>
                </div>
            )}
            
            <form className="space-y-5" onSubmit={handleLogin}>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                    <input
                        type="email"
                        required
                        className="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#901C31] focus:border-[#901C31] transition-colors"
                        placeholder="admin@simak.com"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                    />
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        type="password"
                        required
                        className="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#901C31] focus:border-[#901C31] transition-colors"
                        placeholder="••••••••"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                    />
                </div>

                <div className="flex items-center justify-between mt-2">
                    <div className="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" className="h-4 w-4 text-[#901C31] focus:ring-[#901C31] border-gray-300 rounded" />
                        <label htmlFor="remember-me" className="ml-2 block text-sm text-gray-900">
                            Remember me
                        </label>
                    </div>
                    <div className="text-sm">
                        <a href="#" className="font-medium text-[#901C31] hover:text-red-800">
                            Lupa password?
                        </a>
                    </div>
                </div>

                <div className="pt-2">
                    <button
                        type="submit"
                        disabled={loading}
                        className="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-[#901C31] hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#901C31] transition-all disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        {loading ? (
                            <span className="flex items-center">
                                <FiLoader className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" />
                                Signing in...
                            </span>
                        ) : 'Sign in'}
                    </button>
                </div>
            </form>
            
            <div className="mt-8 text-center">
                <p className="text-sm text-gray-600">
                    Owner baru?{' '}
                    <Link to="/register" className="font-medium text-[#901C31] hover:text-red-800">
                        Registrasi menggunakan License Key
                    </Link>
                </p>
            </div>
        </AuthLayout>
    );
}
