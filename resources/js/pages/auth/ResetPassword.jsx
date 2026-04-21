import React, { useState, useEffect } from 'react';
import { useNavigate, useSearchParams, Link } from 'react-router-dom';
import axios from '../../lib/axios';
import AuthLayout from '../../layouts/AuthLayout';
import { FiLoader, FiCheckCircle } from 'react-icons/fi';

export default function ResetPassword() {
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    
    const [email, setEmail] = useState('');
    const [token, setToken] = useState('');
    const [password, setPassword] = useState('');
    const [passwordConfirmation, setPasswordConfirmation] = useState('');
    
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(false);

    useEffect(() => {
        const urlEmail = searchParams.get('email');
        const urlToken = searchParams.get('token');
        
        if (urlEmail) setEmail(urlEmail);
        if (urlToken) setToken(urlToken);
    }, [searchParams]);

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);

        try {
            await axios.get('/sanctum/csrf-cookie');
            const res = await axios.post('/api/auth/reset-password', { 
                email, 
                token, 
                password, 
                password_confirmation: passwordConfirmation 
            });
            
            setSuccess(true);
            setTimeout(() => {
                navigate('/login');
            }, 3000);
            
        } catch (err) {
            console.error('Reset password error:', err);
            const errors = err.response?.data?.errors;
            if (errors) {
                setError(Object.values(errors).flat().join('\n'));
            } else {
                setError(err.response?.data?.message || 'Gagal merubah password. Token tidak valid atau sudah kedaluwarsa.');
            }
        } finally {
            setLoading(false);
        }
    };

    if (success) {
        return (
            <AuthLayout title="Password Diperbarui" subtitle="Berhasil mengganti password akun Anda">
                <div className="text-center">
                    <div className="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                        <FiCheckCircle className="h-6 w-6 text-green-600" />
                    </div>
                    <h3 className="text-lg font-medium text-gray-900 mb-2">Pembaruan Sukses!</h3>
                    <p className="text-sm text-gray-500 mb-6">
                        Password Anda berhasil diperbarui. Silakan masuk kembali menggunakan kredensial baru Anda. Anda akan diarahkan ke halaman login dalam 3 detik...
                    </p>
                    <Link to="/login" className="inline-flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-[#901C31] border border-transparent rounded-lg hover:bg-red-900 focus:outline-none">
                        Kembali ke Login
                    </Link>
                </div>
            </AuthLayout>
        );
    }

    return (
        <AuthLayout 
            title="Reset Password Baru" 
            subtitle="Harap masukkan password baru untuk akun Anda"
        >
            {error && (
                <div className="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-md">
                    <p className="text-sm text-red-700 font-medium whitespace-pre-line">{error}</p>
                </div>
            )}
            
            <form className="space-y-5" onSubmit={handleSubmit}>
                <input type="hidden" name="email" value={email} />
                <input type="hidden" name="token" value={token} />
                
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Email Saat Ini</label>
                    <input
                        type="email"
                        disabled
                        className="appearance-none block w-full px-4 py-3 border border-gray-200 bg-gray-50 rounded-lg shadow-sm text-gray-500 cursor-not-allowed"
                        value={email}
                    />
                </div>

                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input
                        type="password"
                        required
                        minLength={6}
                        className="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#901C31] focus:border-[#901C31] transition-colors"
                        placeholder="Minimal 6 karakter"
                        value={password}
                        onChange={(e) => setPassword(e.target.value)}
                        disabled={loading}
                    />
                </div>
                
                <div>
                    <label className="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <input
                        type="password"
                        required
                        minLength={6}
                        className="appearance-none block w-full px-4 py-3 border border-gray-300 rounded-lg shadow-sm placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#901C31] focus:border-[#901C31] transition-colors"
                        placeholder="Ulangi password baru Anda"
                        value={passwordConfirmation}
                        onChange={(e) => setPasswordConfirmation(e.target.value)}
                        disabled={loading}
                    />
                </div>

                <div className="pt-2">
                    <button
                        type="submit"
                        disabled={loading || !password || !passwordConfirmation || !token || !email}
                        className="w-full flex justify-center py-3 px-4 border border-transparent rounded-lg shadow-md text-sm font-bold text-white bg-[#901C31] hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#901C31] transition-all disabled:opacity-70 disabled:cursor-not-allowed"
                    >
                        {loading ? (
                            <span className="flex items-center">
                                <FiLoader className="animate-spin -ml-1 mr-3 h-5 w-5 text-white" />
                                Memproses...
                            </span>
                        ) : 'Simpan Password Baru'}
                    </button>
                </div>
            </form>
            
            <div className="mt-8 text-center">
                <p className="text-sm text-gray-600">
                    Batal mereset?{' '}
                    <Link to="/login" className="font-medium text-[#901C31] hover:text-red-800">
                        Kembali ke Login
                    </Link>
                </p>
            </div>
        </AuthLayout>
    );
}
