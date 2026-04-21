import React, { useState } from 'react';
import { Link } from 'react-router-dom';
import axios from '../../lib/axios';
import AuthLayout from '../../layouts/AuthLayout';
import { FiLoader, FiCheckCircle } from 'react-icons/fi';

export default function ForgotPassword() {
    const [email, setEmail] = useState('');
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        setSuccess(null);

        try {
            await axios.get('/sanctum/csrf-cookie');
            const res = await axios.post('/api/auth/forgot-password', { email });
            setSuccess(res.data.message);
            setEmail('');
        } catch (err) {
            console.error('Forgot password error:', err);
            const errors = err.response?.data?.errors;
            if (errors) {
                setError(Object.values(errors).flat().join('\n'));
            } else {
                setError(err.response?.data?.message || 'Gagal mengirim permintaan reset password.');
            }
        } finally {
            setLoading(false);
        }
    };

    return (
        <AuthLayout 
            title="Lupa Password?" 
            subtitle="Masukkan email Anda, dan kami akan mengirimkan instruksi untuk mengatur ulang password."
        >
            {error && (
                <div className="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-md">
                    <p className="text-sm text-red-700 font-medium whitespace-pre-line">{error}</p>
                </div>
            )}
            
            {success && (
                <div className="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-md flex items-start">
                    <FiCheckCircle className="text-green-500 mt-0.5 mr-3 flex-shrink-0" size={18} />
                    <p className="text-sm text-green-700 font-medium">{success}</p>
                </div>
            )}
            
            <form className="space-y-5" onSubmit={handleSubmit}>
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Alamat Email</label>
                    <input
                        type="email"
                        required
                        className="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#901C31] focus:border-[#901C31] transition-colors"
                        placeholder="contoh: admin@simak.com"
                        value={email}
                        onChange={(e) => setEmail(e.target.value)}
                        disabled={loading}
                    />
                </div>

                <div className="pt-2">
                    <button
                        type="submit"
                        disabled={loading || !email}
                        className="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-[#901C31] hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#901C31] transition-all disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        {loading ? (
                            <span className="flex items-center">
                                <FiLoader className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" />
                                Mengirim...
                            </span>
                        ) : 'Kirim Tautan Reset'}
                    </button>
                </div>
            </form>
            
            <div className="mt-8 text-center">
                <p className="text-sm text-gray-600">
                    Ingat password Anda?{' '}
                    <Link to="/login" className="font-medium text-[#901C31] hover:text-red-800">
                        Kembali ke Login
                    </Link>
                </p>
            </div>
        </AuthLayout>
    );
}
