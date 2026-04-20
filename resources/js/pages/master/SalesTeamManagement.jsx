import React, { useState, useEffect } from 'react';
import axios from '../../lib/axios';
import { useOutletContext } from 'react-router-dom';
import { FiPlus, FiEdit, FiTrash2, FiX, FiSearch, FiBriefcase, FiMail, FiPhone, FiLock } from 'react-icons/fi';

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
    
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    useEffect(() => {
        fetchSales();
    }, []);

    const fetchSales = async () => {
        setLoading(true);
        try {
            const res = await axios.get('/api/master/sales');
            setSalesList(res.data);
        } catch (err) {
            setError('Gagal memuat data marketing');
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
        setError(null);
    };

    const handleChange = (e) => {
        setForm({ ...form, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (isEdit) {
                await axios.put(`/api/master/sales/${form.id}`, form);
                setSuccess('Berhasil memperbarui data marketing');
            } else {
                await axios.post('/api/master/sales', form);
                setSuccess('Berhasil menambahkan marketing baru');
            }
            handleCloseModal();
            fetchSales();
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal menyimpan data');
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Hapus team marketing ini? Akun login mereka juga akan dihapus.')) return;
        try {
            await axios.delete(`/api/master/sales/${id}`);
            setSuccess('Marketing berhasil dihapus');
            fetchSales();
        } catch (err) {
            alert('Gagal menghapus marketing');
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

            {/* Modal */}
            {showModal && (
                <div className="fixed inset-0 z-50 overflow-y-auto">
                    <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div className="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" onClick={handleCloseModal}></div>
                        <div className="relative inline-block w-full max-w-md p-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-gray-100">
                            <h3 className="text-xl font-bold text-gray-900 mb-6">{isEdit ? 'Edit Marketing' : 'Marketing Baru'}</h3>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Nama Marketing</label>
                                    <input name="name" required className="w-full border-gray-200 rounded-xl py-3 text-sm" value={form.name} onChange={handleChange} />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Email (Login)</label>
                                    <input name="email" type="email" required className="w-full border-gray-200 rounded-xl py-3 text-sm" value={form.email} onChange={handleChange} />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">{isEdit ? 'Password Baru (Kosongkan jika tidak ganti)' : 'Password Login'}</label>
                                    <div className="relative">
                                        <FiLock className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" />
                                        <input name="password" type="password" required={!isEdit} className="w-full pl-12 pr-4 border-gray-200 rounded-xl py-3 text-sm" value={form.password} onChange={handleChange} />
                                    </div>
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">No. HP / WhatsApp</label>
                                    <input name="phone" required className="w-full border-gray-200 rounded-xl py-3 text-sm" value={form.phone} onChange={handleChange} />
                                </div>
                                <div className="pt-6 flex justify-end gap-3 border-t border-gray-100 mt-6">
                                    <button type="button" onClick={handleCloseModal} className="px-6 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Batal</button>
                                    <button type="submit" className="px-8 py-2.5 bg-[#901C31] text-white rounded-xl font-bold text-sm shadow-lg shadow-red-900/20">
                                        {isEdit ? 'Update Data' : 'Simpan Marketing'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
}
