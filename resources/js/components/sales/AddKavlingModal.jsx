import React, { useState } from 'react';
import axios from '../../lib/axios';
import AddProjectModal from './AddProjectModal';

/**
 * Sub-modal untuk menambah Kavling Baru dari dalam SalesModal
 * @param {{
 *   projectOptions: Array,
 *   onClose: function,
 *   onSuccess: function(plotId: number): void,
 *   onProjectAdded: function(): void,
 * }} props
 */
export default function AddKavlingModal({ projectOptions, onClose, onSuccess, onProjectAdded }) {
    const [showProjectModal, setShowProjectModal] = useState(false);

    const handleSubmit = async (e) => {
        e.preventDefault();
        const kData = {
            project_id: e.target.project_id.value,
            plot_number: e.target.plot_number.value,
            base_price: e.target.base_price.value.replace(/[^0-9]/g, ''),
            area: e.target.area.value,
            status: 'available',
        };
        try {
            const res = await axios.post('/api/master/kavling', kData);
            onSuccess(res.data.data.id);
        } catch (err) {
            alert(err.response?.data?.message || 'Gagal menambah kavling');
        }
    };

    const handleProjectAdded = async () => {
        setShowProjectModal(false);
        await onProjectAdded();
    };

    return (
        <>
            <div className="fixed inset-0 z-[60] overflow-y-auto">
                <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                    <div
                        className="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm"
                        onClick={onClose}
                    />
                    <div className="relative inline-block w-full max-w-md p-6 sm:p-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-[1rem] border border-gray-100">
                        <h3 className="text-xl font-bold text-gray-900 mb-6 tracking-tight">Kavling Baru</h3>
                        <form onSubmit={handleSubmit}>
                            <div className="space-y-4">
                                <div className="space-y-1.5">
                                    <div className="flex justify-between items-end">
                                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                            Pilih Proyek
                                        </label>
                                        <button
                                            type="button"
                                            onClick={() => setShowProjectModal(true)}
                                            className="px-2 py-0.5 bg-red-50 text-[#901C31] border border-red-100 rounded-[6px] text-[8px] font-extrabold uppercase tracking-widest hover:bg-red-100 transition-all"
                                        >
                                            + Tambah Proyek
                                        </button>
                                    </div>
                                    <select
                                        name="project_id"
                                        required
                                        className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm outline-none"
                                    >
                                        <option value="">-- Pilih Proyek --</option>
                                        {projectOptions.map((p) => (
                                            <option key={p.id} value={p.id}>
                                                {p.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                        Blok &amp; Nomor
                                    </label>
                                    <input
                                        name="plot_number"
                                        required
                                        className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm outline-none"
                                        placeholder="Contoh: A-01"
                                    />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                        Harga Dasar
                                    </label>
                                    <input
                                        name="base_price"
                                        required
                                        className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm outline-none"
                                        placeholder="Contoh: 500.000.000"
                                        onChange={(e) => {
                                            let val = e.target.value.replace(/[^0-9]/g, '');
                                            e.target.value = new Intl.NumberFormat('id-ID').format(val);
                                        }}
                                    />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">
                                        Luas Tanah (m2)
                                    </label>
                                    <input
                                        name="area"
                                        type="number"
                                        required
                                        className="w-full border-2 border-gray-50 rounded-xl py-3 px-4 text-sm outline-none"
                                        placeholder="Contoh: 72"
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
                                    Simpan Kavling
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {showProjectModal && (
                <AddProjectModal
                    onClose={() => setShowProjectModal(false)}
                    onSuccess={handleProjectAdded}
                />
            )}
        </>
    );
}
