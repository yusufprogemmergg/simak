import React, { useState, useEffect, useRef } from 'react';
import axios from '../../lib/axios';
import { useOutletContext } from 'react-router-dom';
import { FiSave, FiUploadCloud, FiImage, FiTrendingUp, FiSettings, FiCheckCircle,     FiBriefcase,
} from 'react-icons/fi';

const DEFAULT_FORM_VALUES = {
    name: 'PT. Jaya Abadi Property',
    npwp: '01.234.567.8-012.000',
    email: 'hello@company.com',
    telepon: '081234567890',
    alamat: 'Gedung Perkantoran Lt. 3, Jl. Jend. Sudirman No. 123...',
    nama_ttd_admin: 'Ahmad S.',
    catatan_kaki_cetakan: 'Pembayaran dianggap sah bila struk bank tercetak penuh',
    format_faktur: 'INV/{YYYY}/{MM}/{####}',
    format_kuitansi: 'KW/{YYYY}/{MM}/{DD}/{####}',
};

export default function CompanyProfile() {
    const { user } = useOutletContext();
    const [loading, setLoading] = useState(true);
    const [saving, setSaving] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);
    
    // Form state
    const [profileId, setProfileId] = useState(null);
    const [form, setForm] = useState({ ...DEFAULT_FORM_VALUES });
    
    // Separate state for file upload & preview
    const [logoPreview, setLogoPreview] = useState(null);
    const [logoFile, setLogoFile] = useState(null);
    const fileInputRef = useRef(null);

    useEffect(() => {
        const fetchInitialData = async () => {
            try {
                if (user?.role !== 'owner') {
                    setLoading(false);
                    setError("Anda tidak memiliki akses ke halaman ini.");
                    return;
                }

                // Fetch Profile Data
                const profileRes = await axios.get('/api/profile-perusahaan');
                
                // Handle response if wrapped in 'data' field (from provided JSON structure)
                const apiData = profileRes.data.success ? profileRes.data.data : profileRes.data;

                if (apiData && apiData.id) {
                    setProfileId(apiData.id);
                    setForm({
                        name: apiData.name || DEFAULT_FORM_VALUES.name,
                        npwp: apiData.npwp || DEFAULT_FORM_VALUES.npwp,
                        email: apiData.email || DEFAULT_FORM_VALUES.email,
                        telepon: apiData.telepon || DEFAULT_FORM_VALUES.telepon,
                        alamat: apiData.alamat || DEFAULT_FORM_VALUES.alamat,
                        nama_ttd_admin: apiData.nama_ttd_admin || DEFAULT_FORM_VALUES.nama_ttd_admin,
                        catatan_kaki_cetakan: apiData.catatan_kaki_cetakan || DEFAULT_FORM_VALUES.catatan_kaki_cetakan,
                        format_faktur: apiData.format_faktur || DEFAULT_FORM_VALUES.format_faktur,
                        format_kuitansi: apiData.format_kuitansi || DEFAULT_FORM_VALUES.format_kuitansi,
                    });
                    
                    if (apiData.logo) {
                        const logoUrl = apiData.logo.startsWith('http') 
                            ? apiData.logo 
                            : `${import.meta.env.VITE_APP_URL || 'http://localhost:8000'}/storage/${apiData.logo}`;
                        setLogoPreview(logoUrl);
                    }
                }
                setLoading(false);
            } catch (err) {
                console.error(err);
                setLoading(false);
                // Biarkan tetap mount jika 404 (tandanya belum punya profile)
            }
        };
        fetchInitialData();
    }, []);

    const handleChange = (e) => {
        setForm({
            ...form,
            [e.target.name]: e.target.value
        });
    };

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (file) {
            if (file.size > 1024 * 1024) {
                alert('Tolong unggah file dengan ukuran maskimal 1 MB');
                return;
            }
            setLogoFile(file);
            // Create preview URL
            const url = URL.createObjectURL(file);
            setLogoPreview(url);
        }
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setSaving(true);
        setError(null);
        setSuccess(null);

        try {
            const formData = new FormData();
            Object.keys(form).forEach(key => {
                if (form[key]) {
                    formData.append(key, form[key]);
                }
            });

            if (logoFile) {
                formData.append('logo', logoFile);
            }

            let response;
            if (profileId) {
                // Gunakan POST ke URL id untuk mengatasi issue Method Spoofing Multipart/form-data
                response = await axios.post(`/api/profile-perusahaan/${profileId}`, formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
            } else {
                response = await axios.post('/api/profile-perusahaan', formData, {
                    headers: { 'Content-Type': 'multipart/form-data' }
                });
                if (response.data && response.data.data && response.data.data.id) {
                    setProfileId(response.data.data.id);
                }
            }

            setSuccess('Profil Perusahaan berhasil disimpan!');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } catch (err) {
            console.error('Submit error:', err);
            setError(err.response?.data?.message || 'Gagal menyimpan profil perusahaan');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        } finally {
            setSaving(false);
        }
    };

    if (loading) {
        return (
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-8 h-[60vh] flex items-center justify-center">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#901C31]"></div>
            </div>
        );
    }

    if (error && !form.name) {
        return (
            <div className="bg-red-50 p-6 rounded-lg text-red-700 font-medium">
                {error}
            </div>
        );
    }

    return (
        <>
            <div className="mb-6 flex flex-col md:flex-row justify-end items-start md:items-center gap-4">
                <button 
                    onClick={handleSubmit}
                    disabled={saving}
                    className="flex items-center px-6 py-2.5 bg-[#901C31] text-white rounded-lg hover:bg-red-900 font-medium whitespace-nowrap transition-all shadow-md focus:ring-4 focus:ring-red-100 disabled:opacity-70 disabled:cursor-not-allowed"
                >
                    {saving ? (
                        <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></div>
                    ) : (
                        <FiSave className="w-5 h-5 mr-2" />
                    )}
                    Simpan Perubahan
                </button>
            </div>

            {success && (
                <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative mb-6 flex items-center animate-fade-in-down shadow-sm">
                    <FiCheckCircle className="w-5 h-5 mr-3 text-green-500" />
                    <span className="font-medium">{success}</span>
                </div>
            )}
            
            {error && (
                <div className="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl mb-6 shadow-sm">
                    {error}
                </div>
            )}

            <form onSubmit={handleSubmit} className="space-y-6 pb-20">
                {/* 1. INFORMASI DASAR */}
                <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div className="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center">
                        <div className="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center mr-3 text-[#901C31]">
                            <FiBriefcase className="w-4 h-4" />
                        </div>
                        <h2 className="text-lg font-bold text-gray-800">Informasi Dasar</h2>
                    </div>
                    
                    <div className="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label className="block text-sm font-semibold text-gray-700 mb-1">
                                Nama Perusahaan <span className="text-red-500">*</span>
                            </label>
                            <input 
                                type="text"
                                name="name"
                                required
                                value={form.name}
                                onChange={handleChange}
                                className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#901C31]/20 focus:border-[#901C31] focus:bg-white transition-all text-gray-900"
                                placeholder="PT. Jaya Abadi Property"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-semibold text-gray-700 mb-1">
                                NPWP <span className="text-red-500">*</span>
                            </label>
                            <input 
                                type="text"
                                name="npwp"
                                required
                                value={form.npwp}
                                onChange={handleChange}
                                className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#901C31]/20 focus:border-[#901C31] focus:bg-white transition-all text-gray-900"
                                placeholder="01.234.567.8-012.000"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-semibold text-gray-700 mb-1">
                                Email <span className="text-red-500">*</span>
                            </label>
                            <input 
                                type="email"
                                name="email"
                                required
                                value={form.email}
                                onChange={handleChange}
                                className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#901C31]/20 focus:border-[#901C31] focus:bg-white transition-all text-gray-900"
                                placeholder="hello@company.com"
                            />
                        </div>
                        <div>
                            <label className="block text-sm font-semibold text-gray-700 mb-1">
                                Telepon <span className="text-red-500">*</span>
                            </label>
                            <input 
                                type="text"
                                name="telepon"
                                required
                                value={form.telepon}
                                onChange={handleChange}
                                className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#901C31]/20 focus:border-[#901C31] focus:bg-white transition-all text-gray-900"
                                placeholder="081234567890"
                            />
                        </div>
                        <div className="md:col-span-2">
                            <label className="block text-sm font-semibold text-gray-700 mb-1">
                                Alamat Lengkap <span className="text-red-500">*</span>
                            </label>
                            <textarea 
                                name="alamat"
                                required
                                rows="3"
                                value={form.alamat}
                                onChange={handleChange}
                                className="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#901C31]/20 focus:border-[#901C31] focus:bg-white transition-all text-gray-900"
                                placeholder="Gedung Perkantoran Lt. 3, Jl. Jend. Sudirman No. 123..."
                            ></textarea>
                        </div>
                    </div>
                </div>

                {/* 2. PENGATURAN CETAKAN */}
                <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div className="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center">
                        <div className="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center mr-3 text-[#901C31]">
                            <FiImage className="w-4 h-4" />
                        </div>
                        <h2 className="text-lg font-bold text-gray-800">Pengaturan Cetakan</h2>
                    </div>
                    
                    <div className="p-6 space-y-6">
                        {/* Logo Upload Section */}
                        <div className="bg-gray-50 rounded-xl p-5 border border-dashed border-gray-300 flex flex-col md:flex-row items-center gap-6">
                            <div className="flex-shrink-0">
                                {logoPreview ? (
                                    <div className="h-28 w-28 rounded-lg overflow-hidden border border-gray-200 shadow-sm bg-white flex items-center justify-center">
                                        <img src={logoPreview} alt="Logo Preview" className="max-h-full max-w-full object-contain" />
                                    </div>
                                ) : (
                                    <div className="h-28 w-28 rounded-lg bg-gray-100 border border-gray-200 flex flex-col items-center justify-center text-gray-400">
                                        <FiImage className="w-8 h-8 mb-2 opacity-50" />
                                        <span className="text-xs font-medium">No Logo</span>
                                    </div>
                                )}
                            </div>
                            
                            <div className="flex-1 text-center md:text-left">
                                <h3 className="text-sm font-semibold text-gray-800 mb-1">Logo Perusahaan</h3>
                                <p className="text-xs text-gray-500 mb-4">Format: 512x512, PNG/JPG, Maksimal 1 MB.<br/>Direkomendasikan menggunakan background transparan.</p>
                                
                                <input 
                                    type="file" 
                                    accept="image/png, image/jpeg, image/jpg"
                                    className="hidden" 
                                    ref={fileInputRef}
                                    onChange={handleFileChange}
                                />
                                <button 
                                    type="button"
                                    onClick={() => fileInputRef.current?.click()}
                                    className="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 transition-colors focus:ring-2 focus:ring-[#901C31]/20"
                                >
                                    <FiUploadCloud className="mr-2 h-4 w-4 text-gray-500" />
                                    {logoPreview ? 'Ganti Logo' : 'Upload Logo'}
                                </button>
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label className="block text-sm font-semibold text-gray-700 mb-1">
                                    Nama Tanda Tangan Admin <span className="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text"
                                    name="nama_ttd_admin"
                                    required
                                    value={form.nama_ttd_admin}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#901C31]/20 focus:border-[#901C31] focus:bg-white transition-all text-gray-900"
                                    placeholder="Ahmad S."
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-semibold text-gray-700 mb-1">
                                    Catatan Kaki Cetakan <span className="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text"
                                    name="catatan_kaki_cetakan"
                                    required
                                    value={form.catatan_kaki_cetakan}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#901C31]/20 focus:border-[#901C31] focus:bg-white transition-all text-gray-900"
                                    placeholder="Pembayaran dianggap sah bila struk bank tercetak penuh"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                {/* 3. FORMAT PENOMORAN */}
                <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    <div className="bg-gray-50/50 px-6 py-4 border-b border-gray-100 flex items-center">
                        <div className="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center mr-3 text-[#901C31]">
                            <FiSettings className="w-4 h-4" />
                        </div>
                        <h2 className="text-lg font-bold text-gray-800">Format Penomoran</h2>
                    </div>
                    
                    <div className="p-6">
                        <div className="bg-blue-50/50 border border-blue-100 rounded-lg p-4 mb-6">
                            <p className="text-sm text-blue-800 font-medium tracking-wide">
                                Gunakan parameter berikut secara bebas: <br className="md:hidden" />
                                <span className="inline-block bg-white border border-blue-200 px-2 py-0.5 rounded text-blue-800 mx-1 font-mono text-xs mt-2 md:mt-0">&#123;YYYY&#125; = Tahun</span>
                                <span className="inline-block bg-white border border-blue-200 px-2 py-0.5 rounded text-blue-800 mx-1 font-mono text-xs mt-2 md:mt-0">&#123;MM&#125; = Bulan</span>
                                <span className="inline-block bg-white border border-blue-200 px-2 py-0.5 rounded text-blue-800 mx-1 font-mono text-xs mt-2 md:mt-0">&#123;DD&#125; = Hari</span>
                                <span className="inline-block bg-white border border-blue-200 px-2 py-0.5 rounded text-blue-800 mx-1 font-mono text-xs mt-2 md:mt-0">&#123;####&#125; = Nomor Urut (4 digit)</span>
                            </p>
                        </div>
                        
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label className="block text-sm font-semibold text-gray-700 mb-1">
                                    Format Faktur (Invoice) <span className="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text"
                                    name="format_faktur"
                                    required
                                    value={form.format_faktur}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#901C31]/20 focus:border-[#901C31] focus:bg-white transition-all text-gray-900 font-mono"
                                    placeholder="INV/{YYYY}/{MM}/{####}"
                                />
                            </div>
                            <div>
                                <label className="block text-sm font-semibold text-gray-700 mb-1">
                                    Format Kwitansi <span className="text-red-500">*</span>
                                </label>
                                <input 
                                    type="text"
                                    name="format_kuitansi"
                                    required
                                    value={form.format_kuitansi}
                                    onChange={handleChange}
                                    className="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg focus:ring-2 focus:ring-[#901C31]/20 focus:border-[#901C31] focus:bg-white transition-all text-gray-900 font-mono"
                                    placeholder="KW/{YYYY}/{MM}/{DD}/{####}"
                                />
                            </div>
                        </div>
                    </div>
                </div>

            </form>
        </>
    );
}
