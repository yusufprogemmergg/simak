import React from 'react';
import axios from '../../lib/axios';

/**
 * Sub-modal untuk menambah Pembeli Baru dari dalam SalesModal
 * @param {{
 *   onClose: function,
 *   onSuccess: function(buyerId: number): void,
 * }} props
 */
export default function AddBuyerModal({ onClose, onSuccess }) {
    const handleSubmit = async (e) => {
        e.preventDefault();
        const bData = {
            name: e.target.name.value,
            phone: e.target.phone.value,
            email: e.target.email.value,
            address: e.target.address.value,
        };
        try {
            const res = await axios.post('/api/master/buyer', bData);
            // BuyerController@store returns { message, data } where data = buyer object
            onSuccess(res.data.data.id);
        } catch (err) {
            alert('Gagal menambah pelanggan');
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
                    <h3 className="text-xl font-bold text-gray-900 mb-6">Pembeli Baru</h3>
                    <form onSubmit={handleSubmit}>
                        <div className="space-y-4">
                            <div className="space-y-1.5">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                    Nama Lengkap
                                </label>
                                <input
                                    name="name"
                                    required
                                    className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#901C31]/10 outline-none"
                                    placeholder="Contoh: John Doe"
                                />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                    No. Telepon
                                </label>
                                <input
                                    name="phone"
                                    required
                                    className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#901C31]/10 outline-none"
                                    placeholder="08xxxxxxxx"
                                />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                    Email
                                </label>
                                <input
                                    name="email"
                                    type="email"
                                    className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#901C31]/10 outline-none"
                                    placeholder="john@example.com"
                                />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                    Alamat
                                </label>
                                <textarea
                                    name="address"
                                    className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm focus:ring-2 focus:ring-[#901C31]/10 outline-none min-h-[80px]"
                                    placeholder="Jl. Raya No. 123..."
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
                                Simpan Pembeli
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
