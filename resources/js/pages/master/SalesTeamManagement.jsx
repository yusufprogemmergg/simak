import React, { useState, useEffect } from 'react';
import axios from '../../lib/axios';
import { useOutletContext } from 'react-router-dom';
import { FiPlus, FiEdit, FiTrash2, FiX, FiSearch, FiBriefcase, FiMail, FiPhone, FiLock } from 'react-icons/fi';
import Toast from '../../components/ui/Toast';
import { useToast } from '../../hooks/useToast';

export default function SalesTeamManagement() {
    const { user } = useOutletContext();
    const [salesList, setSalesList] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    
    // Modal states
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    
    // Form states
    const [form, setForm] = useState({
        id: '',
        name: '',
        email: '',
        password: '',
        phone: ''
    });
    
    const { toast, showToast } = useToast();

    useEffect(() => {
        fetchSales();
    }, []);

    const fetchSales = async () => {
        setLoading(true);
        try {
            const res = await axios.get('/api/master/sales');
            setSalesList(res.data);
        } catch (err) {
            showToast('Gagal memuat data marketing', 'error');
        } finally {
            setLoading(false);
        }
    };

    const handleOpenModal = (sales = null) => {
        if (sales) {
            setIsEdit(true);
            setForm({
                id: sales.id,
                name: sales.name || '',
                email: sales.user?.email || '',
                password: '', // Don't show password
                phone: sales.phone || ''
            });
        } else {
            setIsEdit(false);
            setForm({ id: '', name: '', email: '', password: '', phone: '' });
        }
        setShowModal(true);
    };

    const handleCloseModal = () => {
        setShowModal(false);
    };

    const handleChange = (e) => {
        setForm({ ...form, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (isEdit) {
                await axios.put(`/api/master/sales/${form.id}`, form);
                showToast('Berhasil memperbarui data marketing');
            } else {
                await axios.post('/api/master/sales', form);
                showToast('Berhasil menambahkan marketing baru');
            }
            handleCloseModal();
            fetchSales();
        } catch (err) {
            const errors = err.response?.data?.errors;
            if (errors) {
                showToast(Object.values(errors).flat().join('\n'), 'error');
            } else {
                showToast(err.response?.data?.message || 'Gagal menyimpan data marketing', 'error');
            }
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Hapus team marketing ini? Akun login mereka juga akan dihapus.')) return;
        try {
            await axios.delete(`/api/master/sales/${id}`);
            showToast('Marketing berhasil dihapus');
            fetchSales();
        } catch (err) {
            showToast(err.response?.data?.message || 'Gagal menghapus marketing', 'error');
        }
    };

    const filteredSales = salesList.filter(s => 
        (s.name || '').toLowerCase().includes(searchTerm.toLowerCase()) ||
        (s.phone || '').includes(searchTerm)
    );

    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800 tracking-tight">Tim Marketing</h1>
                    <p className="text-gray-500 text-sm">Kelola akun dan performa tim marketing</p>
                </div>
                <button 
                    onClick={() => handleOpenModal()}
                    className="flex items-center justify-center px-6 py-2.5 bg-[#901C31] text-white rounded-xl hover:bg-red-900 transition-all font-bold shadow-lg shadow-red-900/20"
                >
                    <FiPlus className="mr-2 w-5 h-5" /> Tambah Marketing
                </button>
            </div>

            <Toast toast={toast} />

            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div className="p-4 border-b border-gray-50">
                    <div className="relative max-w-md">
                        <FiSearch className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" />
                        <input 
                            type="text" 
                            className="w-full pl-12 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#901C31]/20"
                            placeholder="Cari nama marketing..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-100">
                        <thead className="bg-gray-50/50">
                            <tr>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Marketing</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kontak</th>
                                <th className="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Unit Terjual</th>
                                <th className="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Revenue</th>
                                <th className="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {loading ? (
                                <tr><td colSpan="5" className="px-6 py-12 text-center text-gray-400">Memuat...</td></tr>
                            ) : filteredSales.length === 0 ? (
                                <tr><td colSpan="5" className="px-6 py-12 text-center text-gray-400">Tidak ada data marketing</td></tr>
                            ) : filteredSales.map(s => (
                                <tr key={s.id} className="hover:bg-gray-50/50 transition-colors">
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="flex items-center">
                                            <div className="w-8 h-8 rounded-full bg-rose-50 flex items-center justify-center text-[#901C31] mr-3 font-bold">
                                                {s.name?.[0]?.toUpperCase() || 'S'}
                                            </div>
                                            <span className="font-bold text-gray-800">{s.name}</span>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="flex items-center text-sm text-gray-600 mb-1"><FiPhone className="mr-2 text-gray-400 w-3 h-3" /> {s.phone || '-'}</div>
                                        <div className="flex items-center text-xs text-gray-400"><FiMail className="mr-2 text-gray-400 w-3 h-3" /> {s.user?.email}</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                        <span className="px-3 py-1 bg-gray-100 text-gray-700 rounded-lg text-xs font-bold">{s.total_units_sold || 0} Unit</span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-center text-sm font-bold text-green-600">
                                        Rp {new Intl.NumberFormat('id-ID').format(s.total_revenue || 0)}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                        <div className="flex items-center justify-center space-x-2">
                                            <button onClick={() => handleOpenModal(s)} className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"><FiEdit /></button>
                                            <button onClick={() => handleDelete(s.id)} className="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"><FiTrash2 /></button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {showModal && (
                <div className="fixed inset-0 z-50 overflow-y-auto">
                    <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div className="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" onClick={handleCloseModal} />
                        <div className="relative inline-block w-full max-w-lg p-0 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-[1rem] border border-gray-100">
                            {/* Header */}
                            <div className="px-8 py-6 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-30">
                                <div>
                                    <h3 className="text-xl font-bold text-gray-900">{isEdit ? 'Edit Marketing' : 'Marketing Baru'}</h3>
                                    <p className="text-gray-500 text-xs mt-1">Data akun login akan dibuat otomatis</p>
                                </div>
                                <button onClick={handleCloseModal} className="p-2 bg-gray-50 text-gray-400 hover:text-gray-900 rounded-2xl transition-all">
                                    <FiX className="w-5 h-5" />
                                </button>
                            </div>
                            {/* Body */}
                            <form onSubmit={handleSubmit} className="px-8 py-6 space-y-5 max-h-[65vh] overflow-y-auto bg-gray-50/20">
                                <div className="space-y-1.5">
                                    <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Nama Marketing</label>
                                    <input name="name" required className="w-full bg-white border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/10 focus:border-[#901C31]" value={form.name} onChange={handleChange} placeholder="Contoh: Andi Wijaya" />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Email (Login)</label>
                                    <input name="email" type="email" required className="w-full bg-white border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/10 focus:border-[#901C31]" value={form.email} onChange={handleChange} placeholder="andi@perusahaan.com" />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">{isEdit ? 'Password Baru (Kosongkan jika tidak ganti)' : 'Password Login'}</label>
                                    <div className="relative">
                                        <FiLock className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" />
                                        <input name="password" type="password" required={!isEdit} className="w-full pl-12 pr-4 bg-white border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/10 focus:border-[#901C31]" value={form.password} onChange={handleChange} placeholder="Min. 6 karakter" />
                                    </div>
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">No. HP / WhatsApp</label>
                                    <input name="phone" required className="w-full bg-white border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/10 focus:border-[#901C31]" value={form.phone} onChange={handleChange} placeholder="Contoh: 08123456789" />
                                </div>
                            </form>
                            {/* Footer */}
                            <div className="px-8 py-5 border-t border-gray-100 flex justify-end gap-3 sticky bottom-0 bg-white z-30">
                                <button type="button" onClick={handleCloseModal} className="px-6 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">Batal</button>
                                <button onClick={handleSubmit} className="px-10 py-2.5 bg-[#901C31] text-white rounded-xl font-bold text-sm shadow-lg shadow-red-900/20 hover:bg-red-900 transition-all">
                                    {isEdit ? 'Update Data' : 'Simpan Marketing'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
