import React, { useState, useEffect } from 'react';
import axios from '../../lib/axios';
import AdminLayout from '../../layouts/AdminLayout';
import { FiKey, FiTrash2, FiX } from 'react-icons/fi';
export default function LicenseManagement() {
    const [user, setUser] = useState(null);
    const [licenses, setLicenses] = useState([]);
    const [loading, setLoading] = useState(true);
    
    // Modal states
    const [showGenerateModal, setShowGenerateModal] = useState(false);
    const [quantity, setQuantity] = useState(1);
    const [note, setNote] = useState('');
    
    const [showEditModal, setShowEditModal] = useState(false);
    const [editingLicense, setEditingLicense] = useState(null);
    
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    useEffect(() => {
        const fetchInitialData = async () => {
            try {
                const userRes = await axios.get('/api/me');
                setUser(userRes.data);
                if (userRes.data.role === 'super_admin') {
                    fetchLicenses();
                } else {
                    setLoading(false);
                    setError("Anda tidak memiliki akses ke halaman ini.");
                }
            } catch (err) {
                console.error(err);
            }
        };
        fetchInitialData();
    }, []);

    const fetchLicenses = async () => {
        setLoading(true);
        try {
            const res = await axios.get('/api/admin/license');
            setLicenses(res.data.data);
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal memuat licenses');
        } finally {
            setLoading(false);
        }
    };

    const handleGenerate = async (e) => {
        e.preventDefault();
        setError(null);
        try {
            await axios.post('/api/admin/license', { quantity, note });
            setSuccess(`Berhasil men-generate ${quantity} license key`);
            setShowGenerateModal(false);
            setQuantity(1);
            setNote('');
            fetchLicenses();
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal membuat license');
        }
    };

    const handleUpdateStatus = async (id, newStatus) => {
        try {
            await axios.put(`/api/admin/license/${id}`, { status: newStatus });
            fetchLicenses();
        } catch (err) {
            alert(err.response?.data?.message || 'Gagal update status');
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Hapus permanen license key ini?')) return;
        try {
            await axios.delete(`/api/admin/license/${id}`);
            fetchLicenses();
        } catch (err) {
            alert(err.response?.data?.message || 'Gagal menghapus');
        }
    };

    if (loading && !licenses.length) return (
        <AdminLayout user={user}>
            <div className="flex justify-center p-12">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#901C31]"></div>
            </div>
        </AdminLayout>
    );

    if (error && !licenses.length) return (
        <AdminLayout user={user}>
            <div className="bg-red-50 p-6 rounded-lg text-red-700">{error}</div>
        </AdminLayout>
    );

    return (
        <AdminLayout user={user}>
            <div className="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h3 className="text-2xl font-bold text-gray-800">License Management</h3>
                    <p className="text-gray-600 text-sm">Kelola akses Owner aplikasi secara terpusat.</p>
                </div>
                <button 
                    onClick={() => setShowGenerateModal(true)}
                    className="flex items-center px-4 py-2 bg-[#901C31] text-white rounded-lg hover:bg-red-900 font-medium whitespace-nowrap transition-colors"
                >
                    <FiKey className="w-5 h-5 mr-2" />
                    Generate Key
                </button>
            </div>

            {success && (
                <div className="bg-green-50 border-l-4 border-green-500 p-4 mb-6 relative">
                    <p className="text-sm text-green-700 font-medium">{success}</p>
                    <button onClick={() => setSuccess(null)} className="absolute top-4 right-4 text-green-600 hover:text-green-800">&times;</button>
                </div>
            )}

            {/* Table */}
            <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-200">
                        <thead className="bg-gray-50">
                            <tr>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">License Key</th>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Note</th>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Used By</th>
                                <th scope="col" className="px-6 py-4 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-200">
                            {licenses.map(lic => (
                                <tr key={lic.id} className="hover:bg-gray-50">
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <div className="font-mono text-sm text-gray-900 bg-gray-100 px-2 py-1 rounded inline-block">{lic.license_key}</div>
                                    </td>
                                    <td className="px-6 py-4">
                                        <div className="text-sm text-gray-600 truncate max-w-xs cursor-help" title={lic.note}>{lic.note || '-'}</div>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            ${lic.status === 'active' ? 'bg-rose-100 text-[#901C31]' 
                                            : lic.status === 'available' ? 'bg-green-100 text-green-800' 
                                            : 'bg-red-100 text-red-800'}`}>
                                            {lic.status}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        {lic.used_by ? (
                                            <div className="flex flex-col">
                                                <span className="text-sm font-medium text-gray-900">{lic.used_by.username}</span>
                                                <span className="text-xs text-gray-500">{lic.used_by.email}</span>
                                            </div>
                                        ) : (
                                            <span className="text-sm text-gray-400 italic">Not Assigned</span>
                                        )}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div className="flex justify-end space-x-2">
                                            {lic.status === 'active' && (
                                                <button onClick={() => handleUpdateStatus(lic.id, 'revoked')} className="text-red-600 hover:text-red-900 bg-red-50 px-2 py-1 rounded">Revoke</button>
                                            )}
                                            {lic.status === 'revoked' && (
                                                <button onClick={() => handleUpdateStatus(lic.id, 'active')} className="text-green-600 hover:text-green-900 bg-green-50 px-2 py-1 rounded">Restore</button>
                                            )}
                                            {lic.status === 'available' && !lic.is_used && (
                                                <button onClick={() => handleDelete(lic.id)} className="text-red-600 hover:text-red-900 bg-gray-100 px-2 py-1 rounded">
                                                    <FiTrash2 className="w-4 h-4" />
                                                </button>
                                            )}
                                        </div>
                                    </td>
                                </tr>
                            ))}
                            {licenses.length === 0 && (
                                <tr>
                                    <td colSpan="5" className="px-6 py-8 text-center text-gray-500">Belum ada license key yang digenerate.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Modal Generate */}
            {showGenerateModal && (
                <div className="fixed inset-0 z-50 overflow-y-auto">
                    <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div className="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75" onClick={() => setShowGenerateModal(false)}></div>
                        
                        <div className="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-2xl">
                            <h3 className="text-lg font-bold leading-6 text-gray-900 mb-4">Generate License Key</h3>
                            <form onSubmit={handleGenerate}>
                                <div className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Quantity (Max 50)</label>
                                        <input 
                                            type="number" min="1" max="50" required 
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-[#901C31] focus:border-[#901C31]" 
                                            value={quantity} onChange={e => setQuantity(e.target.value)} 
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700">Informasi / Catatan (opsional)</label>
                                        <input 
                                            type="text" 
                                            className="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-[#901C31] focus:border-[#901C31]" 
                                            value={note} onChange={e => setNote(e.target.value)} 
                                            placeholder="Grup Properti A"
                                        />
                                    </div>
                                </div>
                                <div className="mt-6 flex justify-end space-x-3">
                                    <button type="button" onClick={() => setShowGenerateModal(false)} className="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">Batal</button>
                                    <button type="submit" className="px-4 py-2 text-sm font-medium text-white bg-[#901C31] rounded-md hover:bg-red-900 transition-colors">Generate</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
