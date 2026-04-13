import React, { useState } from 'react';
import { useNavigate, Link } from 'react-router-dom';
import axios from '../lib/axios';
import AuthLayout from '../layouts/AuthLayout';

export default function Register() {
    const [formData, setFormData] = useState({
        username: '',
        email: '',
        password: '',
        license_key: ''
    });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(false);
    const navigate = useNavigate();

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleRegister = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);

        try {
            await axios.get('/sanctum/csrf-cookie');
            await axios.post('/api/auth/register', formData);
            localStorage.setItem('isLoggedIn', 'true');
            setSuccess(true);
            setTimeout(() => navigate('/dashboard'), 2000);
        } catch (err) {
            console.error('Register error:', err);
            setError(err.response?.data?.message || 'Gagal meregistrasi akun. Cek kembali data Anda atau hubungi Super Admin.');
        } finally {
            setLoading(false);
        }
    };

    return (
        <AuthLayout 
            title="Daftar Akun Owner" 
            subtitle="Masukkan detail Anda dan License Key yang diberikan oleh sistem"
        >
            {error && (
                <div className="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-md">
                    <p className="text-sm text-red-700 font-medium">{error}</p>
                </div>
            )}
            
            {success && (
                <div className="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-md">
                    <p className="text-sm text-green-700 font-medium">Registrasi sukses! Mengalihkan ke dashboard...</p>
                </div>
            )}

            <form className="space-y-4" onSubmit={handleRegister}>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Username</label>
                    <input
                        type="text"
                        name="username"
                        required
                        className="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#901C31] focus:border-[#901C31] transition-colors"
                        placeholder="AcmeCorp"
                        value={formData.username}
                        onChange={handleChange}
                    />
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Email address</label>
                    <input
                        type="email"
                        name="email"
                        required
                        className="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#901C31] focus:border-[#901C31] transition-colors"
                        placeholder="owner@acme.com"
                        value={formData.email}
                        onChange={handleChange}
                    />
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input
                        type="password"
                        name="password"
                        required
                        minLength={6}
                        className="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#901C31] focus:border-[#901C31] transition-colors"
                        placeholder="Minimal 6 karakter"
                        value={formData.password}
                        onChange={handleChange}
                    />
                </div>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">License Key Aktif</label>
                    <input
                        type="text"
                        name="license_key"
                        required
                        className="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#901C31] focus:border-[#901C31] transition-colors bg-rose-50 font-mono tracking-wider"
                        placeholder="XXXX-XXXX-XXXX"
                        value={formData.license_key}
                        onChange={handleChange}
                    />
                </div>

                <div className="pt-4">
                    <button
                        type="submit"
                        disabled={loading || success}
                        className="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-[#901C31] hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#901C31] transition-all disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        {loading ? 'Processing...' : 'Register as Owner'}
                    </button>
                </div>
            </form>
            
            <div className="mt-8 text-center">
                <p className="text-sm text-gray-600">
                    Sudah punya akun?{' '}
                    <Link to="/login" className="font-medium text-[#901C31] hover:text-red-800">
                        Login sekarang
                    </Link>
                </p>
            </div>
        </AuthLayout>
    );
}
