import React, { useState, useEffect } from 'react';
import axios from '../../lib/axios';
import { useOutletContext } from 'react-router-dom';
import { FiPlus, FiEdit, FiTrash2, FiX, FiSearch, FiUser } from 'react-icons/fi';

export default function BuyerManagement() {
    const { user } = useOutletContext();
    const [buyers, setBuyers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    
    // Modal states
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    
    // Form states
    const [form, setForm] = useState({
        id: '',
        name: '',
        phone: '',
        email: '',
        address: '',
        nik: ''
    });
    
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    useEffect(() => {
        fetchBuyers();
    }, []);

    const fetchBuyers = async () => {
        setLoading(true);
        try {
            const res = await axios.get('/api/master/buyer');
            setBuyers(res.data);
        } catch (err) {
            setError('Gagal memuat data pelanggan');
        } finally {
            setLoading(false);
        }
    };

    const handleOpenModal = (buyer = null) => {
        if (buyer) {
            setIsEdit(true);
            setForm({
                id: buyer.id,
                name: buyer.name,
                phone: buyer.phone || '',
                email: buyer.email || '',
                address: buyer.address || '',
                nik: buyer.nik || ''
            });
        } else {
            setIsEdit(false);
            setForm({ id: '', name: '', phone: '', email: '', address: '', nik: '' });
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
                await axios.put(`/api/master/buyer/${form.id}`, form);
                setSuccess('Berhasil memperbarui data pelanggan');
            } else {
                await axios.post('/api/master/buyer', form);
                setSuccess('Berhasil menambahkan pelanggan baru');
            }
            handleCloseModal();
            fetchBuyers();
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal menyimpan data');
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Hapus pelanggan ini?')) return;
        try {
            await axios.delete(`/api/master/buyer/${id}`);
            setSuccess('Pelanggan berhasil dihapus');
            fetchBuyers();
        } catch (err) {
            alert('Gagal menghapus pelanggan');
        }
    };

    const filteredBuyers = buyers.filter(b => 
        (b.name && b.name.toLowerCase().includes(searchTerm.toLowerCase())) ||
        (b.phone && b.phone.includes(searchTerm))
    );

    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800 tracking-tight">Data Pelanggan</h1>
                    <p className="text-gray-500 text-sm">Kelola informasi pelanggan dan pembeli</p>
                </div>
                <button 
                    onClick={() => handleOpenModal()}
                    className="flex items-center justify-center px-6 py-2.5 bg-[#901C31] text-white rounded-xl hover:bg-red-900 transition-all font-bold shadow-lg shadow-red-900/20"
                >
                    <FiPlus className="mr-2 w-5 h-5" /> Tambah Pelanggan
                </button>
            </div>

            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div className="p-4 border-b border-gray-50">
                    <div className="relative max-w-md">
                        <FiSearch className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" />
                        <input 
                            type="text" 
                            className="w-full pl-12 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#901C31]/20"
                            placeholder="Cari nama atau telepon..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-100">
                        <thead className="bg-gray-50/50">
                            <tr>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pelanggan</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Kontak</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">NIK</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Alamat</th>
                                <th className="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100">
                            {loading ? (
                                <tr><td colSpan="5" className="px-6 py-12 text-center text-gray-400">Memuat...</td></tr>
                            ) : filteredBuyers.length === 0 ? (
                                <tr><td colSpan="5" className="px-6 py-12 text-center text-gray-400">Tidak ada data pelanggan</td></tr>
                            ) : filteredBuyers.map(b => (
                                <tr key={b.id} className="hover:bg-gray-50/50 transition-colors">
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="flex items-center">
                                            <div className="w-8 h-8 rounded-full bg-rose-50 flex items-center justify-center text-[#901C31] mr-3 font-bold">
                                                {b.name?.[0]?.toUpperCase() || '?'}
                                            </div>
                                            <span className="font-bold text-gray-800">{b.name}</span>
                                        </div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="text-sm font-medium text-gray-700">{b.phone || '-'}</div>
                                        <div className="text-xs text-gray-400">{b.email || '-'}</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{b.nik || '-'}</td>
                                    <td className="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">{b.address || '-'}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                        <div className="flex items-center justify-center space-x-2">
                                            <button onClick={() => handleOpenModal(b)} className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors"><FiEdit /></button>
                                            <button onClick={() => handleDelete(b.id)} className="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"><FiTrash2 /></button>
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
                            <h3 className="text-xl font-bold text-gray-900 mb-6">{isEdit ? 'Edit Pelanggan' : 'Pelanggan Baru'}</h3>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Nama Lengkap</label>
                                    <input name="name" required className="w-full border-gray-200 rounded-xl py-3 text-sm" value={form.name} onChange={handleChange} />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">No. Telepon</label>
                                    <input name="phone" required className="w-full border-gray-200 rounded-xl py-3 text-sm" value={form.phone} onChange={handleChange} />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Email</label>
                                    <input name="email" type="email" className="w-full border-gray-200 rounded-xl py-3 text-sm" value={form.email} onChange={handleChange} />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">NIK</label>
                                    <input name="nik" className="w-full border-gray-200 rounded-xl py-3 text-sm" value={form.nik} onChange={handleChange} />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Alamat</label>
                                    <textarea name="address" className="w-full border-gray-200 rounded-xl py-3 text-sm min-h-[100px]" value={form.address} onChange={handleChange}></textarea>
                                </div>
                                <div className="pt-6 flex justify-end gap-3 border-t border-gray-100 mt-6">
                                    <button type="button" onClick={handleCloseModal} className="px-6 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Batal</button>
                                    <button type="submit" className="px-8 py-2.5 bg-[#901C31] text-white rounded-xl font-bold text-sm shadow-lg shadow-red-900/20">
                                        {isEdit ? 'Update Data' : 'Simpan Pelanggan'}
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
