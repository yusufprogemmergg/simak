import React from 'react';
import axios from '../../lib/axios';

/**
 * Sub-modal untuk menambah Proyek Baru dari dalam AddKavlingModal
 * @param {{
 *   onClose: function,
 *   onSuccess: function(): void,
 * }} props
 */
export default function AddProjectModal({ onClose, onSuccess }) {
    const handleSubmit = async (e) => {
        e.preventDefault();
        const pData = {
            name: e.target.name.value,
            location: e.target.location.value,
            total_units: e.target.total_units.value,
            notes: e.target.notes.value,
        };
        try {
            await axios.post('/api/master/project', pData);
            onSuccess();
        } catch (err) {
            alert(err.response?.data?.message || 'Gagal menambah proyek');
        }
    };

    return (
        <div className="fixed inset-0 z-[70] overflow-y-auto">
            <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div
                    className="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm"
                    onClick={onClose}
                />
                <div className="relative inline-block w-full max-w-md p-6 sm:p-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-[1rem] border border-gray-100">
                    <h3 className="text-xl font-bold text-gray-900 mb-6 tracking-tight">Proyek Baru</h3>
                    <form onSubmit={handleSubmit}>
                        <div className="space-y-4">
                            <div className="space-y-1.5">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                    Nama Proyek
                                </label>
                                <input
                                    name="name"
                                    required
                                    className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm outline-none"
                                    placeholder="Contoh: Permata Residence"
                                />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                    Lokasi
                                </label>
                                <input
                                    name="location"
                                    required
                                    className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm outline-none"
                                    placeholder="Contoh: Jakarta Selatan"
                                />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                    Total Unit
                                </label>
                                <input
                                    name="total_units"
                                    type="number"
                                    required
                                    className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm outline-none"
                                    placeholder="Contoh: 100"
                                />
                            </div>
                            <div className="space-y-1.5">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                    Catatan
                                </label>
                                <textarea
                                    name="notes"
                                    className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm outline-none min-h-[60px]"
                                    placeholder="Tambahkan catatan jika ada..."
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
                                Simpan Proyek
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
