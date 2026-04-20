import React from 'react';
import axios from '../../lib/axios';

/**
 * Sub-modal untuk menambah Marketing/Sales Baru dari dalam SalesModal
 * @param {{
 *   onClose: function,
 *   onSuccess: function(salesStaffId: number): void,
 * }} props
 */
export default function AddSalesModal({ onClose, onSuccess }) {
    const handleSubmit = async (e) => {
        e.preventDefault();
        const sData = {
            name: e.target.name.value,
            email: e.target.email.value,
            password: e.target.password.value,
            phone: e.target.phone.value,
        };
        try {
            const res = await axios.post('/api/master/sales', sData);
            // SalesController@store returns { message, data: salesStaff } where salesStaff.id is the SalesStaff ID
            onSuccess(res.data.data.id);
        } catch (err) {
            alert(err.response?.data?.message || 'Gagal menambah marketing');
        }
    };

    return (
        <div className="fixed inset-0 z-[60] overflow-y-auto">
            <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div
                    className="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm"
                    onClick={onClose}
                />
                <div className="relative inline-block w-full max-w-md p-6 sm:p-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-[1rem] border border-gray-100">
                    <h3 className="text-xl font-bold text-gray-900 mb-6 tracking-tight">Marketing Baru</h3>
                    <form onSubmit={handleSubmit}>
                        <div className="space-y-4">
                            <div className="space-y-1.5">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                    Nama Marketing
                                </label>
                                <input
                                    name="name"
                                    required
                                    className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#901C31]/10 outline-none"
                                    placeholder="Nama lengkap staf"
                                />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                    Email Login
                                </label>
                                <input
                                    name="email"
                                    type="email"
                                    required
                                    className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#901C31]/10 outline-none"
                                    placeholder="email@marketing.com"
                                />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                    Password
                                </label>
                                <input
                                    name="password"
                                    type="password"
                                    required
                                    className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#901C31]/10 outline-none"
                                    placeholder="Minimal 8 karakter"
                                />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                    Telepon
                                </label>
                                <input
                                    name="phone"
                                    required
                                    className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#901C31]/10 outline-none"
                                    placeholder="08xxxxxxxx"
                                />
                            </div>
                        </div>
                        <div className="mt-8 flex justify-end gap-3 pt-6 border-t border-gray-100">
                            <button
                                type="button"
                                onClick={onClose}
                                className="px-6 py-2 text-sm font-bold text-gray-500"
                            >
                                Batal
                            </button>
                            <button
                                type="submit"
                                className="px-8 py-2.5 bg-[#901C31] text-white rounded-xl font-bold text-sm shadow-lg shadow-red-900/20"
                            >
                                Simpan Marketing
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
