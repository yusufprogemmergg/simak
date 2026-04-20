import React, { useState, useEffect } from 'react';
import { FiX } from 'react-icons/fi';
import axios from '../../lib/axios';
import { formatRupiah, formatNumberInput } from '../../utils/formatters';
import AddBuyerModal from './AddBuyerModal';
import AddSalesModal from './AddSalesModal';
import AddKavlingModal from './AddKavlingModal';

export default function SalesModal({ 
    isOpen, 
    onClose, 
    onSuccess, 
    isEdit = false, 
    initialData = null,
    kavlings = [],
    buyers = [],
    salesList = [],
    refreshOptions = () => {}
}) {
    const [formData, setFormData] = useState({
        id: '',
        plot_id: '',
        buyer_id: '',
        sales_staff_id: '',
        payment_method: 'full_cash',
        booking_date: new Date().toISOString().split('T')[0],
        discount_type: 'nominal',
        discount_amount: 0,
        ppjb_fee: 0,
        shm_fee: 0,
        other_fees: 0,
        booking_fee: 0,
        is_unit_included: false,
        tenor_months: 1,
        due_day: 1,
        down_payment_percent: 0,
        down_payment_amount: 0,
        notes: '',
        status: 'active'
    });

    const [previews, setPreviews] = useState({
        hargaDasar: 0,
        diskonNominal: 0,
        hargaNetto: 0,
        grandTotal: 0,
        estimasiAngsuran: 0,
        dpNominal: 0
    });

    const [showBuyerModal, setShowBuyerModal] = useState(false);
    const [showSalesModal, setShowSalesModal] = useState(false);
    const [showKavlingModal, setShowKavlingModal] = useState(false);
    const [projectOptions, setProjectOptions] = useState([]);

    const fetchProjects = async () => {
        try {
            const res = await axios.get('/api/master/project');
            setProjectOptions(res.data || []);
        } catch (err) { 
            console.error("Failed to fetch projects", err); 
        }
    };

    useEffect(() => {
        fetchProjects();
    }, []);

    useEffect(() => {
        if (initialData) {
            // booking_date bisa berupa ISO string ('2024-01-15') atau Date object dari BE cast
            const bookingDate = initialData.booking_date
                ? (typeof initialData.booking_date === 'string'
                    ? initialData.booking_date.split('T')[0]
                    : new Date(initialData.booking_date).toISOString().split('T')[0])
                : new Date().toISOString().split('T')[0];

            setFormData({
                ...initialData,
                booking_date: bookingDate,
                // Pastikan field numerik tidak null
                discount_amount: initialData.discount_amount ?? 0,
                ppjb_fee: initialData.ppjb_fee ?? 0,
                shm_fee: initialData.shm_fee ?? 0,
                other_fees: initialData.other_fees ?? 0,
                booking_fee: initialData.booking_fee ?? 0,
                down_payment_amount: initialData.down_payment_amount ?? 0,
                tenor_months: initialData.tenor_months ?? 1,
                due_day: initialData.due_day ?? 1,
                // discount_type default 'nominal' jika tidak ada
                discount_type: initialData.discount_type ?? 'nominal',
            });
        } else {
            setFormData({
                id: '',
                plot_id: '',
                buyer_id: '',
                sales_staff_id: '',
                payment_method: 'full_cash',
                booking_date: new Date().toISOString().split('T')[0],
                discount_type: 'nominal',
                discount_amount: 0,
                ppjb_fee: 0,
                shm_fee: 0,
                other_fees: 0,
                booking_fee: 0,
                is_unit_included: false,
                tenor_months: 1,
                due_day: 1,
                down_payment_percent: 0,
                down_payment_amount: 0,
                notes: '',
                status: 'active'
            });
        }
    }, [initialData, isOpen]);

    useEffect(() => {
        const selectedPlot = kavlings.find(k => k.id == formData.plot_id);
        const hargaDasar = selectedPlot ? parseFloat(selectedPlot.base_price) : 0;
        
        let diskonNominal = parseFloat(formData.discount_amount) || 0;
        // BE validation pakai 'percent', FE juga harus konsisten
        if (formData.discount_type === 'percent') {
            diskonNominal = hargaDasar * (diskonNominal / 100);
        }
        
        const hargaNetto = hargaDasar - diskonNominal;
        const ppjb = parseFloat(formData.ppjb_fee) || 0;
        const shm = parseFloat(formData.shm_fee) || 0;
        const lain = parseFloat(formData.other_fees) || 0;
        const bookingFee = parseFloat(formData.booking_fee) || 0;
        
        const grandTotal = hargaNetto + ppjb + shm + lain + (formData.is_unit_included ? 0 : bookingFee);
        
        const dpNominal = parseFloat(formData.down_payment_amount) || 0;
        const sisaPokok = grandTotal - dpNominal;
        const angsuran = formData.tenor_months > 0 ? sisaPokok / formData.tenor_months : 0;
        
        setPreviews({
            hargaDasar,
            diskonNominal,
            hargaNetto,
            grandTotal,
            estimasiAngsuran: angsuran,
            dpNominal
        });
    }, [formData, kavlings]);

    const handleFormChange = (e) => {
        const { name, value, type, checked } = e.target;
        setFormData({
            ...formData,
            [name]: type === 'checkbox' ? checked : value
        });
    };

    const handleNumberInputChange = (e, fieldName) => {
        let rawValue = e.target.value;
        let cleanValue = rawValue.replace(/[^0-9]/g, '');
        setFormData({
            ...formData,
            [fieldName]: cleanValue === '' ? '' : parseInt(cleanValue)
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        try {
            if (isEdit) {
                await axios.post(`/api/master/sales-transaction/${formData.id}`, formData);
            } else {
                await axios.post('/api/master/sales-transaction', formData);
            }
            onSuccess(isEdit ? 'Berhasil memperbarui transaksi' : 'Berhasil menambahkan transaksi');
            onClose();
        } catch (err) {
            alert(err.response?.data?.message || 'Gagal menyimpan transaksi');
        }
    };

    // Callback setelah pembeli baru ditambah
    const handleBuyerAdded = async (buyerId) => {
        await refreshOptions();
        setFormData(prev => ({ ...prev, buyer_id: buyerId }));
        setShowBuyerModal(false);
    };

    // Callback setelah marketing baru ditambah
    const handleSalesAdded = async (staffId) => {
        await refreshOptions();
        setFormData(prev => ({ ...prev, sales_staff_id: staffId }));
        setShowSalesModal(false);
    };

    // Callback setelah kavling baru ditambah
    const handleKavlingAdded = async (plotId) => {
        await refreshOptions();
        setFormData(prev => ({ ...prev, plot_id: plotId }));
        setShowKavlingModal(false);
    };

    // Callback setelah proyek baru ditambah (dari dalam AddKavlingModal)
    const handleProjectAdded = async () => {
        await fetchProjects();
    };

    if (!isOpen) return null;

    return (
        <div className="fixed inset-0 z-50 overflow-y-auto">
            <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                <div className="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" onClick={onClose} />
                
                <div className="relative inline-block w-full max-w-4xl p-0 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-[1rem] border border-gray-100">
                    {/* Header */}
                    <div className="px-4 sm:px-8 py-4 sm:py-6 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white z-30">
                        <div>
                            <h3 className="text-xl sm:text-2xl font-bold text-gray-900 leading-tight">
                                {isEdit ? 'Update Transaksi' : 'Transaksi Baru'}
                            </h3>
                            <p className="text-gray-500 text-xs sm:text-sm mt-1">Konfigurasi skema pembayaran dan data unit</p>
                        </div>
                        <button onClick={onClose} className="p-2 bg-gray-50 text-gray-400 hover:text-gray-900 rounded-2xl transition-all">
                            <FiX className="w-5 h-5 sm:w-6 sm:h-6" />
                        </button>
                    </div>
                    
                    {/* Form Body */}
                    <form onSubmit={handleSubmit} className="px-4 sm:px-8 py-4 sm:py-8 h-[75vh] overflow-y-auto bg-gray-50/20 scrollbar-thin">
                        <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8">
                            {/* Left — Form Fields */}
                            <div className="lg:col-span-8 space-y-6 sm:space-y-8">
                                {/* Seksi Data Utama */}
                                <div className="space-y-4">
                                    <div className="flex items-center gap-3 mb-2">
                                        <div className="w-1.5 h-6 bg-[#901C31] rounded-full" />
                                        <h4 className="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Data Utama Transaksi</h4>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {/* Pilih Kavling */}
                                        <div className="space-y-1.5">
                                            <div className="flex justify-between items-end">
                                                <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Pilih Unit Kavling</label>
                                                {!isEdit && (
                                                    <button type="button" onClick={() => setShowKavlingModal(true)} className="px-2 py-0.5 bg-red-50 text-[#901C31] border border-red-100 rounded-[6px] text-[8px] font-extrabold uppercase tracking-widest hover:bg-red-100 transition-all">
                                                        + Tambah Kavling
                                                    </button>
                                                )}
                                            </div>
                                            <select name="plot_id" required className="w-full bg-white border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/10 focus:border-[#901C31]" value={formData.plot_id} onChange={handleFormChange}>
                                                <option value="">-- Pilih --</option>
                                                {kavlings.filter(k => k.status === 'available' || k.id == formData.plot_id).map(k => (
                                                    <option key={k.id} value={k.id}>{k.plot_number} - {k.project?.name}</option>
                                                ))}
                                            </select>
                                        </div>

                                        {/* Pilih Pembeli */}
                                        <div className="space-y-1.5">
                                            <div className="flex justify-between items-end">
                                                <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Data Pelanggan</label>
                                                <button type="button" onClick={() => setShowBuyerModal(true)} className="px-2 py-0.5 bg-red-50 text-[#901C31] border border-red-100 rounded-[6px] text-[8px] font-extrabold uppercase tracking-widest hover:bg-red-100 transition-all">
                                                    + Tambah Pembeli
                                                </button>
                                            </div>
                                            <select name="buyer_id" required className="w-full bg-white border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/10 focus:border-[#901C31]" value={formData.buyer_id} onChange={handleFormChange}>
                                                <option value="">-- Pilih --</option>
                                                {buyers.map(b => (
                                                    <option key={b.id} value={b.id}>{b.name}</option>
                                                ))}
                                            </select>
                                        </div>

                                        {/* Pilih Marketing */}
                                        <div className="space-y-1.5">
                                            <div className="flex justify-between items-end">
                                                <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Staf Marketing</label>
                                                <button type="button" onClick={() => setShowSalesModal(true)} className="px-2 py-0.5 bg-red-50 text-[#901C31] border border-red-100 rounded-[6px] text-[8px] font-extrabold uppercase tracking-widest hover:bg-red-100 transition-all">
                                                    + Tambah Marketing
                                                </button>
                                            </div>
                                            <select name="sales_staff_id" required className="w-full bg-white border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/10 focus:border-[#901C31]" value={formData.sales_staff_id} onChange={handleFormChange}>
                                                <option value="">-- Pilih --</option>
                                                {salesList.map(s => (
                                                    <option key={s.id} value={s.id}>{s.name}</option>
                                                ))}
                                            </select>
                                        </div>

                                        {/* Tanggal Booking */}
                                        <div className="space-y-1.5">
                                            <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Tanggal Booking</label>
                                            <input type="date" name="booking_date" required className="w-full bg-white border-gray-200 rounded-xl py-3 text-sm" value={formData.booking_date} onChange={handleFormChange} />
                                        </div>
                                    </div>
                                </div>

                                {/* Seksi Skema & Biaya */}
                                <div className="space-y-4 pt-4 border-t border-gray-100">
                                    <div className="flex items-center gap-3 mb-2">
                                        <div className="w-1.5 h-6 bg-[#901C31] rounded-full" />
                                        <h4 className="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Skema &amp; Detail Biaya</h4>
                                    </div>
                                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        {/* Metode Bayar */}
                                        <div className="space-y-1.5">
                                            <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Metode Bayar</label>
                                            <select name="payment_method" required className="w-full bg-white border-gray-200 rounded-xl py-3 text-sm" value={formData.payment_method} onChange={handleFormChange}>
                                                <option value="full_cash">Cash Keras</option>
                                                <option value="installment">Angsuran In-house</option>
                                                <option value="bank_mortgage">KPR Bank</option>
                                            </select>
                                        </div>

                                        {/* Diskon */}
                                        <div className="space-y-1.5">
                                            <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Diskon / Promo</label>
                                            <div className="flex gap-2">
                                                <select name="discount_type" className="bg-gray-50 border-none rounded-xl text-xs px-3 w-20" value={formData.discount_type} onChange={handleFormChange}>
                                                    <option value="nominal">Rp</option>
                                                    <option value="percent">%</option>
                                                </select>
                                                <input 
                                                    type="text" 
                                                    name="discount_amount" 
                                                    className="flex-1 bg-white border-gray-200 rounded-xl py-3 text-sm" 
                                                    placeholder="Contoh: 5.000.000" 
                                                    value={formatNumberInput(formData.discount_amount)} 
                                                    onChange={(e) => handleNumberInputChange(e, 'discount_amount')} 
                                                />
                                            </div>
                                        </div>

                                        {/* Uang Muka */}
                                        <div className="space-y-1.5">
                                            <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Uang Muka (DP)</label>
                                            <input 
                                                type="text" 
                                                name="down_payment_amount" 
                                                className="w-full bg-white border-gray-200 rounded-xl py-3 text-sm" 
                                                placeholder="Contoh: 50.000.000" 
                                                value={formatNumberInput(formData.down_payment_amount)} 
                                                onChange={(e) => handleNumberInputChange(e, 'down_payment_amount')} 
                                            />
                                        </div>

                                        {/* Tenor & Due Day */}
                                        <div className="grid grid-cols-2 gap-3">
                                            <div className="space-y-1.5">
                                                <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Tenor (Bln)</label>
                                                <input type="number" name="tenor_months" required className="w-full bg-white border-gray-200 rounded-xl py-3 text-sm" value={formData.tenor_months} onChange={handleFormChange} />
                                            </div>
                                            <div className="space-y-1.5">
                                                <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Tgl Tempo</label>
                                                <input type="number" name="due_day" min="1" max="31" required className="w-full bg-white border-gray-200 rounded-xl py-3 text-sm" value={formData.due_day} onChange={handleFormChange} />
                                            </div>
                                        </div>

                                        {/* Administrasi */}
                                        <div className="space-y-1.5">
                                            <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Administrasi (SHM/PPJB)</label>
                                            <div className="flex flex-col sm:flex-row gap-2">
                                                <input 
                                                    type="text" name="ppjb_fee" 
                                                    className="flex-1 bg-white border-gray-200 rounded-xl py-3 text-sm" 
                                                    placeholder="PPJB" 
                                                    value={formatNumberInput(formData.ppjb_fee)} 
                                                    onChange={(e) => handleNumberInputChange(e, 'ppjb_fee')} 
                                                />
                                                <input 
                                                    type="text" name="shm_fee" 
                                                    className="flex-1 bg-white border-gray-200 rounded-xl py-3 text-sm" 
                                                    placeholder="SHM" 
                                                    value={formatNumberInput(formData.shm_fee)} 
                                                    onChange={(e) => handleNumberInputChange(e, 'shm_fee')} 
                                                />
                                            </div>
                                        </div>

                                        {/* Booking Fee */}
                                        <div className="space-y-1.5">
                                            <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Booking Fee (BF)</label>
                                            <input 
                                                type="text" name="booking_fee" 
                                                className="w-full bg-white border-gray-200 rounded-xl py-3 text-sm" 
                                                value={formatNumberInput(formData.booking_fee)} 
                                                onChange={(e) => handleNumberInputChange(e, 'booking_fee')} 
                                            />
                                            <div className="flex items-center pl-1 mt-1">
                                                <input type="checkbox" id="is_unit_included" name="is_unit_included" className="rounded text-[#901C31] focus:ring-[#901C31]" checked={formData.is_unit_included} onChange={handleFormChange} />
                                                <label htmlFor="is_unit_included" className="ml-2 text-[10px] text-gray-400 font-bold uppercase tracking-wider cursor-pointer">Potong harga unit</label>
                                            </div>
                                        </div>
                                    </div>

                                    {/* Catatan */}
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Catatan Transaksi</label>
                                        <textarea name="notes" className="w-full bg-white border-gray-200 rounded-xl py-3 text-sm min-h-[80px]" value={formData.notes} onChange={handleFormChange} placeholder="Berikan catatan tambahan jika diperlukan..." />
                                    </div>
                                </div>
                            </div>

                            {/* Right — Ringkasan Harga */}
                            <div className="lg:col-span-4">
                                <div className="bg-[#901C31] rounded-[1rem] p-4 sm:p-6 text-white shadow-xl shadow-red-900/20 space-y-6 sticky top-0">
                                    <h5 className="text-xs font-bold uppercase tracking-[0.2em] opacity-60">Ringkasan Harga</h5>
                                    
                                    <div className="space-y-4">
                                        <div className="flex justify-between items-center">
                                            <span className="text-sm opacity-70">Harga Dasar</span>
                                            <span className="font-bold text-base break-all text-right">{formatRupiah(previews.hargaDasar)}</span>
                                        </div>
                                        <div className="flex justify-between items-center text-red-100">
                                            <span className="text-sm opacity-70">Potongan Harga</span>
                                            <span className="font-bold text-base break-all text-right">- {formatRupiah(previews.diskonNominal)}</span>
                                        </div>
                                        <div className="pt-4 border-t border-white/10 flex justify-between items-end">
                                            <span className="text-sm opacity-70">Grand Total</span>
                                            <span className="font-bold text-xl sm:text-2xl break-all text-right">{formatRupiah(previews.grandTotal)}</span>
                                        </div>
                                    </div>

                                    <div className="bg-white/10 rounded-2xl p-4 space-y-3">
                                        <div className="flex justify-between items-center">
                                            <span className="text-xs opacity-60 uppercase tracking-widest font-bold">Uang Muka</span>
                                            <span className="font-bold break-all text-right">{formatRupiah(previews.dpNominal)}</span>
                                        </div>
                                        <div className="flex justify-between items-center border-t border-white/5 pt-2">
                                            <span className="text-xs opacity-60 uppercase tracking-widest font-bold">Cicilan ({formData.tenor_months}x)</span>
                                            <span className="font-bold text-base break-all text-right">{formatRupiah(previews.estimasiAngsuran)}</span>
                                        </div>
                                    </div>

                                    <p className="text-[10px] text-center opacity-40 leading-relaxed font-medium">
                                        Estimasi cicilan bersifat sementara dan dapat berubah sesuai dengan persetujuan manajemen atau bank.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </form>
                    
                    {/* Footer */}
                    <div className="px-4 sm:px-8 py-4 sm:py-6 border-t border-gray-100 flex justify-end gap-3 sticky bottom-0 bg-white z-30">
                        <button type="button" onClick={onClose} className="px-4 sm:px-6 py-2.5 text-sm font-bold text-gray-500 hover:text-gray-700 transition-colors">Batal</button>
                        <button onClick={handleSubmit} className="px-6 sm:px-10 py-2.5 bg-[#901C31] text-white rounded-xl font-bold text-sm shadow-lg shadow-red-900/20 hover:bg-red-900 transition-all">
                            Simpan Transaksi
                        </button>
                    </div>
                </div>
            </div>

            {/* Sub-modals */}
            {showBuyerModal && (
                <AddBuyerModal
                    onClose={() => setShowBuyerModal(false)}
                    onSuccess={handleBuyerAdded}
                />
            )}
            {showSalesModal && (
                <AddSalesModal
                    onClose={() => setShowSalesModal(false)}
                    onSuccess={handleSalesAdded}
                />
            )}
            {showKavlingModal && (
                <AddKavlingModal
                    projectOptions={projectOptions}
                    onClose={() => setShowKavlingModal(false)}
                    onSuccess={handleKavlingAdded}
                    onProjectAdded={handleProjectAdded}
                />
            )}
        </div>
    );
}
