import React, { useState, useEffect, useCallback } from 'react';
import axios from '../../lib/axios';
import { useParams, useNavigate } from 'react-router-dom';
import { 
    FiArrowLeft, FiEdit, FiClock, FiCheckCircle, FiAlertCircle, 
    FiPlus, FiPrinter, FiCalendar, FiTrash2, FiInfo, FiX, FiUserPlus, FiRotateCcw, FiMessageCircle
} from 'react-icons/fi';
import SalesModal from '../../components/sales/SalesModal';
import Toast from '../../components/ui/Toast';
import ConfirmModal from '../../components/ui/ConfirmModal';
import { formatRupiah } from '../../utils/formatters';
import { useToast } from '../../hooks/useToast';

export default function SalesTransactionDetail() {
    const { id } = useParams();
    const navigate = useNavigate();
    const [transaction, setTransaction] = useState(null);
    const [loading, setLoading] = useState(true);
    
    // Options for Modal
    const [kavlings, setKavlings] = useState([]);
    const [buyers, setBuyers] = useState([]);
    const [salesList, setSalesList] = useState([]);

    // UI State
    const [showEditModal, setShowEditModal] = useState(false);
    const [confirmModal, setConfirmModal] = useState({ show: false, title: '', message: '', onConfirm: null });
    const [paymentModal, setPaymentModal] = useState({ show: false, type: '', data: null, date: new Date().toISOString().split('T')[0] });
    const [showCancelModal, setShowCancelModal] = useState(false);
    const [cancelForm, setCancelForm] = useState({
        cancel_type: 'refund',
        refund_amount: 0,
        new_buyer_id: '',
        new_sales_staff_id: ''
    });
    const [flexForm, setFlexForm] = useState({
        amount: '',
        paid_date: new Date().toISOString().split('T')[0],
        notes: ''
    });

    const { toast, showToast } = useToast();

    const fetchOptions = async () => {
        try {
            const [kRes, bRes, sRes] = await Promise.all([
                axios.get('/api/master/kavling'),
                axios.get('/api/master/buyer'),
                axios.get('/api/master/sales')
            ]);
            setKavlings(kRes.data);
            setBuyers(bRes.data);
            setSalesList(sRes.data);
        } catch (err) {
            console.error('Failed to fetch options', err);
        }
    };

    const fetchDetail = useCallback(async () => {
        setLoading(true);
        try {
            const res = await axios.get(`/api/master/sales-transaction/${id}`);
            setTransaction(res.data);
        } catch (err) {
            console.error('Failed to fetch transaction detail', err);
            showToast('Gagal memuat detail transaksi', 'error');
        } finally {
            setLoading(false);
        }
    }, [id]);

    useEffect(() => {
        fetchDetail();
        fetchOptions();
    }, [fetchDetail]);

    // Payment Actions
    const handleActionPayment = async () => {
        const { type, data, date } = paymentModal;
        try {
            let url = '';
            let payload = { date };  // payDp & payOff pakai key 'date'

            if (type === 'dp') {
                url = `/api/master/sales-transaction/${id}/pay-dp`;
            } else if (type === 'off') {
                url = `/api/master/sales-transaction/${id}/pay-off`;
            } else if (type === 'angsuran') {
                url = `/api/master/angsuran/${data.id}/pay`;
                payload = { paid_date: date };  // angsuran pakai key 'paid_date'
            }

            const res = await axios.post(url, payload);
            showToast(res.data.message || 'Pembayaran berhasil diproses');
            setPaymentModal({ show: false, type: '', data: null, date: new Date().toISOString().split('T')[0] });
            fetchDetail();
        } catch (err) {
            showToast(err.response?.data?.message || 'Gagal memproses pembayaran', 'error');
        }
    };

    const handleFlexSubmit = async (e) => {
        e.preventDefault();
        try {
            const res = await axios.post('/api/master/flexible-payment', {
                transaction_id: id,
                ...flexForm
            });
            showToast(res.data.message);
            setFlexForm({
                amount: '',
                paid_date: new Date().toISOString().split('T')[0],
                notes: ''
            });
            fetchDetail();
        } catch (err) {
            showToast(err.response?.data?.message || 'Gagal menyimpan pembayaran', 'error');
        }
    };

    const handleCancelSubmit = async (e) => {
        e.preventDefault();
        try {
            const res = await axios.post(`/api/master/sales-transaction/${id}/cancel`, {
                cancel_type: cancelForm.cancel_type,
                refund_amount: cancelForm.refund_amount,
                new_buyer_id: cancelForm.new_buyer_id,
                new_sales_staff_id: cancelForm.new_sales_staff_id,
            });
            showToast(res.data.message);
            setShowCancelModal(false);
            if (cancelForm.cancel_type === 'delete') {
                navigate('/penjualan');
            } else {
                fetchDetail();
            }
        } catch (err) {
            showToast(err.response?.data?.message || 'Gagal membatalkan transaksi', 'error');
        }
    };

    const handleDeletePayment = async (paymentId) => {
        setConfirmModal({
            show: true,
            title: 'Hapus Pembayaran?',
            message: 'Tindakan ini akan membatalkan pembayaran, menghapus catatan di buku kas, dan mengembalikan sisa piutang. Lanjutkan?',
            onConfirm: async () => {
                try {
                    const res = await axios.delete(`/api/master/payment-history/${paymentId}`);
                    showToast(res.data.message);
                    fetchDetail();
                } catch (err) {
                    showToast(err.response?.data?.message || 'Gagal menghapus pembayaran', 'error');
                }
            }
        });
    };

    const handleWhatsAppBill = async () => {
        try {
            const res = await axios.get(`/api/master/sales-transaction/${id}/whatsapp-bill`);            
            if (res.data.success && res.data.whatsapp_url) {
                window.open(res.data.whatsapp_url, '_blank');
            } else {
                showToast(res.data.message || 'Gagal membuat tautan WhatsApp', 'error');
            }
        } catch (err) {
            showToast(err.response?.data?.message || 'Gagal menghubungi server WhatsApp', 'error');
        }
    };

    if (loading) return (
        <div className="flex items-center justify-center min-h-[60vh]">
            <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-[#901C31]" />
        </div>
    );

    if (!transaction) return (
        <div className="p-8 text-center text-gray-500">Transaksi tidak ditemukan</div>
    );

    return (
        <div className="space-y-6 pb-20 relative font-primary">
            <Toast toast={toast} />

            <ConfirmModal
                show={confirmModal.show}
                title={confirmModal.title}
                message={confirmModal.message}
                onConfirm={confirmModal.onConfirm || (() => {})}
                onClose={() => setConfirmModal({ ...confirmModal, show: false })}
            />

            {/* Payment Date Picker Modal */}
            {paymentModal.show && (
                <div className="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
                    <div className="bg-white rounded-2xl p-8 max-w-sm w-full shadow-2xl animate-in zoom-in-95 duration-200">
                        <div className="flex items-center justify-between mb-6">
                            <h3 className="text-xl font-bold">Konfirmasi Pembayaran</h3>
                            <button onClick={() => setPaymentModal({ ...paymentModal, show: false })} className="text-gray-400 hover:text-gray-600"><FiX /></button>
                        </div>
                        <div className="space-y-4 mb-8">
                            <p className="text-sm text-gray-500">
                                {paymentModal.type === 'dp' ? 'Masukkan tanggal pembayaran Uang Muka (DP).' : 
                                 paymentModal.type === 'off' ? 'Masukkan tanggal pelunasan transaksi.' : 
                                 'Masukkan tanggal pembayaran cicilan.'}
                            </p>
                            <div className="space-y-1.5 font-primary text-gray-800">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Tanggal Bayar</label>
                                <input 
                                    type="date" 
                                    className="w-full border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/10 focus:border-[#901C31]" 
                                    value={paymentModal.date}
                                    onChange={e => setPaymentModal({ ...paymentModal, date: e.target.value })}
                                />
                            </div>
                        </div>
                        <button onClick={handleActionPayment} className="w-full py-4 bg-[#901C31] text-white rounded-xl font-bold text-sm shadow-lg shadow-red-900/20 hover:bg-red-900 transition-all">
                            Konfirmasi Sekarang
                        </button>
                    </div>
                </div>
            )}

            {/* Header / Breadcrumb */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <button onClick={() => navigate('/penjualan')} className="flex items-center text-gray-500 hover:text-[#901C31] transition-colors font-medium text-sm">
                    <FiArrowLeft className="mr-2" /> Kembali ke Daftar Penjualan
                </button>
                <div className="flex flex-wrap items-center justify-end gap-3">
                    <button 
                        onClick={handleWhatsAppBill}
                        className="flex items-center px-4 py-2 border border-green-600 text-green-700 bg-green-50 hover:bg-green-600 hover:text-white rounded-lg font-medium text-sm transition-all shadow-sm"
                    >
                        <FiMessageCircle className="mr-2" /> Tagihan WA
                    </button>
                    <button 
                        onClick={() => setShowCancelModal(true)}
                        className="flex items-center px-4 py-2 border border-red-200 text-red-600 bg-red-50 hover:bg-red-100 rounded-lg font-medium text-sm transition-all shadow-sm"
                    >
                        <FiTrash2 className="mr-2" /> Batalkan
                    </button>
                    <button onClick={() => setShowEditModal(true)} className="flex items-center px-4 py-2 bg-[#901C31] text-white rounded-lg hover:bg-red-900 font-medium text-sm transition-all shadow-sm">
                        <FiEdit className="mr-2" /> Edit
                    </button>
                </div>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 text-gray-800">
                
                {/* Left Column */}
                <div className="lg:col-span-7 space-y-6">
                    
                    {/* Ringkasan Transaksi */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden group">
                        <div className="p-6 border-b border-gray-100 flex items-center justify-between bg-gray-50/30">
                            <div>
                                <h3 className="text-lg font-bold">Ringkasan Transaksi</h3>
                                <p className="text-xs text-gray-500">No: {transaction.transaction_number}</p>
                            </div>
                            <span className={`px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider ${
                                transaction.status === 'paid_off' ? 'bg-green-100 text-green-700' :
                                transaction.status === 'active' ? 'bg-blue-100 text-blue-700' : 'bg-red-100 text-red-700'
                            }`}>
                                {transaction.status}
                            </span>
                        </div>
                        
                        <div className="p-6 grid grid-cols-2 sm:grid-cols-3 gap-6">
                            <div className="space-y-1">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Kavling / Unit</label>
                                <p className="text-sm font-bold">{transaction.plot?.plot_number}</p>
                                <p className="text-[10px] text-gray-500">{transaction.plot?.project?.name}</p>
                            </div>
                            <div className="space-y-1">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Pembeli</label>
                                <p className="text-sm font-bold">{transaction.buyer?.name}</p>
                                <p className="text-[10px] text-gray-500">{transaction.buyer?.phone}</p>
                            </div>
                            <div className="space-y-1">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Tgl Transaksi</label>
                                <p className="text-sm font-bold">{new Date(transaction.booking_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })}</p>
                            </div>
                            <div className="space-y-1">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Metode Bayar</label>
                                <p className="text-sm font-bold capitalize">{transaction.payment_method?.replace(/_/g, ' ')}</p>
                            </div>
                            <div className="space-y-1">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Marketing</label>
                                <p className="text-sm font-bold">{transaction.salesStaff?.name || '-'}</p>
                            </div>
                            <div className="space-y-1">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Tenor</label>
                                <p className="text-sm font-bold">{transaction.tenor_months} Bulan</p>
                            </div>
                        </div>

                        <div className="p-6 border-t border-gray-100 bg-gray-50/50">
                            <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-2">Catatan</label>
                            <p className="text-sm text-gray-600 italic">"{transaction.notes || 'Tidak ada catatan'}"</p>
                        </div>
                    </div>

                    {/* Ikhtisar Keuangan */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <div className="flex items-center justify-between mb-6">
                            <h3 className="text-lg font-bold flex items-center">
                                <span className="w-1 h-5 bg-[#901C31] rounded-full mr-3" />
                                Ikhtisar Keuangan
                            </h3>
                            {transaction.status === 'active' && transaction.payment_method === 'full_cash' && (
                                <button 
                                    onClick={() => setPaymentModal({ show: true, type: 'off', data: null, date: new Date().toISOString().split('T')[0] })}
                                    className="px-3 py-1.5 bg-green-50 text-green-700 border border-green-200 rounded-lg text-xs font-bold hover:bg-green-100 transition-colors flex items-center gap-2"
                                >
                                    <FiCheckCircle /> LUNASI SEKARANG
                                </button>
                            )}
                        </div>
                        
                        <div className="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-8">
                            <div className="bg-gray-50 p-4 rounded-xl border border-gray-100">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block mb-1">Grand Total</label>
                                <p className="text-lg font-bold">{formatRupiah(transaction.grand_total)}</p>
                            </div>
                            <div className="bg-green-50 p-4 rounded-xl border border-green-100">
                                <label className="text-[10px] font-bold text-green-600 uppercase tracking-widest block mb-1">Total Terbayar</label>
                                <p className="text-lg font-bold text-green-700">{formatRupiah(transaction.total_paid)}</p>
                            </div>
                            <div className="bg-red-50 p-4 rounded-xl border border-red-100">
                                <label className="text-[10px] font-bold text-red-600 uppercase tracking-widest block mb-1">Sisa Piutang</label>
                                <p className="text-lg font-bold text-red-700">{formatRupiah(transaction.grand_total - transaction.total_paid)}</p>
                            </div>
                        </div>

                        <div className="flex flex-col sm:flex-row gap-4">
                            {transaction.down_payment_amount > 0 && (
                                <div className="flex-1 p-4 bg-white border border-gray-200 rounded-xl flex items-center justify-between">
                                    <div>
                                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Uang Muka (DP)</label>
                                        <p className="font-bold">{formatRupiah(transaction.down_payment_amount)}</p>
                                    </div>
                                    <div className="flex flex-col items-end gap-1">
                                        <span className={`px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider ${transaction.dp_status === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-50 text-red-600'}`}>
                                            {transaction.dp_status === 'paid' ? 'Lunas' : 'Belum Lunas'}
                                        </span>
                                        {transaction.dp_status !== 'paid' && (
                                            <button 
                                                onClick={() => setPaymentModal({ show: true, type: 'dp', data: null, date: new Date().toISOString().split('T')[0] })}
                                                className="mt-1 px-3 py-1 bg-[#901C31] text-white rounded-lg text-[10px] font-bold hover:bg-red-900 transition-all shadow-sm shadow-red-900/10"
                                            >
                                                Bayar Sekarang
                                            </button>
                                        )}
                                    </div>
                                </div>
                            )}
                            {transaction.booking_fee > 0 && (
                                <div className="flex-1 p-4 bg-white border border-gray-200 rounded-xl flex items-center justify-between">
                                    <div>
                                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block">Booking Fee</label>
                                        <p className="font-bold">{formatRupiah(transaction.booking_fee)}</p>
                                    </div>
                                    <FiCheckCircle className="text-green-500" />
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Riwayat Pembayaran */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 className="text-sm font-bold uppercase tracking-wider mb-6 flex items-center">
                            <FiClock className="mr-2 text-[#901C31]" /> Riwayat Pembayaran
                        </h3>
                        <div className="space-y-4">
                            {transaction.payment_histories?.length > 0 ? (
                                <div className="overflow-hidden border border-gray-100 rounded-xl">
                                    <table className="min-w-full divide-y divide-gray-100">
                                        <thead className="bg-gray-50">
                                            <tr>
                                                <th className="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Tanggal</th>
                                                <th className="px-4 py-3 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Keterangan</th>
                                                <th className="px-4 py-3 text-right text-[10px] font-bold text-gray-400 uppercase tracking-widest">Nominal</th>
                                                <th className="px-4 py-3 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest w-20">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody className="divide-y divide-gray-50">
                                            {transaction.payment_histories.map(history => (
                                                <tr key={history.id} className="text-xs">
                                                    <td className="px-4 py-3 whitespace-nowrap text-gray-500">{new Date(history.date).toLocaleDateString('id-ID')}</td>
                                                    <td className="px-4 py-3 font-medium text-gray-700">{history.notes}</td>
                                                    <td className="px-4 py-3 text-right font-bold text-green-600">{formatRupiah(history.amount)}</td>
                                                    <td className="px-4 py-3 text-center">
                                                        <div className="flex items-center justify-center gap-1">
                                                            <button 
                                                                onClick={() => window.open(`/api/master/payment-history/${history.id}/print-kuitansi`, '_blank')}
                                                                className="p-1.5 bg-gray-50 text-gray-400 hover:text-[#901C31] hover:bg-red-50 rounded-lg transition-all"
                                                                title="Cetak Kuitansi"
                                                            >
                                                                <FiPrinter className="w-4 h-4" />
                                                            </button>
                                                            <button 
                                                                onClick={() => handleDeletePayment(history.id)}
                                                                className="p-1.5 bg-gray-50 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-all"
                                                                title="Batalkan Pembayaran"
                                                            >
                                                                <FiTrash2 className="w-4 h-4" />
                                                            </button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            ))}
                                        </tbody>
                                    </table>
                                </div>
                            ) : (
                                <div className="py-8 text-center bg-gray-50 rounded-xl border border-dashed border-gray-200 text-gray-400 text-sm italic">
                                    Belum ada riwayat pembayaran yang tercatat
                                </div>
                            )}
                        </div>
                    </div>
                </div>

                {/* Right Column */}
                <div className="lg:col-span-5 space-y-6">
                    
                    {/* Jadwal Angsuran */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200 flex flex-col h-[400px]">
                        <div className="p-4 border-b border-gray-100 flex items-center justify-between shrink-0">
                            <h3 className="text-sm font-bold uppercase tracking-wider flex items-center">
                                <FiCalendar className="mr-2 text-[#901C31]" /> Jadwal Angsuran
                            </h3>
                            <span className="text-[10px] font-bold text-gray-400">Tenor: {transaction.tenor_months}x</span>
                        </div>
                        
                        <div className="flex-1 overflow-y-auto p-4 space-y-2 custom-scrollbar">
                            {transaction.installments?.map((item) => (
                                <div key={item.id} className="flex items-center justify-between p-3 bg-gray-50/50 rounded-lg border border-gray-100 group transition-all">
                                    <div className="flex items-center gap-3">
                                        <div className="w-6 h-6 rounded flex items-center justify-center text-[10px] font-bold bg-white border border-gray-200 text-gray-400 group-hover:bg-[#901C31] group-hover:text-white transition-colors">
                                            {item.installment_number}
                                        </div>
                                        <div>
                                            <p className="text-[10px] font-bold text-gray-700">{new Date(item.due_date).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' })}</p>
                                            <p className="text-xs font-bold text-[#901C31]">{formatRupiah(item.remaining_amount)}</p>
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        {item.status === 'paid' ? (
                                            <span className="px-2 py-0.5 bg-green-50 text-green-600 rounded text-[8px] font-extrabold uppercase tracking-widest border border-green-100 flex items-center">
                                                <FiCheckCircle className="mr-1" /> PAID
                                            </span>
                                        ) : item.status === 'partial' ? (
                                            <div className="flex flex-col items-end gap-1">
                                                <span className="px-2 py-0.5 bg-orange-50 text-orange-600 rounded text-[8px] font-extrabold uppercase tracking-widest border border-orange-100">PARTIAL</span>
                                                <button 
                                                    onClick={() => setPaymentModal({ show: true, type: 'angsuran', data: item, date: new Date().toISOString().split('T')[0] })}
                                                    className="px-2 py-1 bg-[#901C31] text-white rounded-[6px] text-[10px] font-bold hover:bg-red-900 transition-all shadow-sm shadow-red-900/10"
                                                >LUNASI</button>
                                            </div>
                                        ) : (
                                            <button 
                                                onClick={() => setPaymentModal({ show: true, type: 'angsuran', data: item, date: new Date().toISOString().split('T')[0] })}
                                                className="px-2 py-1 bg-[#901C31] text-white rounded-[6px] text-[10px] font-bold hover:bg-red-900 transition-all shadow-sm shadow-red-900/10"
                                            >LUNASI</button>
                                        )}
                                    </div>
                                </div>
                            ))}
                            {transaction.installments?.length === 0 && (
                                <div className="h-full flex items-center justify-center text-gray-400 text-xs italic p-4 text-center">
                                    Tidak ada jadwal angsuran untuk metode pembayaran ini
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Bayar Fleksibel */}
                    <div className="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                        <h3 className="text-sm font-bold uppercase tracking-wider mb-4 flex items-center">
                            <FiPlus className="mr-2 text-[#901C31]" /> Bayar Fleksibel
                        </h3>
                        <form onSubmit={handleFlexSubmit} className="space-y-4">
                            <div className="space-y-1">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block pl-1">Nominal (Rp)</label>
                                <input 
                                    type="number" 
                                    className="w-full border-gray-200 rounded-xl py-2.5 px-4 text-sm focus:ring-[#901C31]/10 focus:border-[#901C31] outline-none" 
                                    placeholder="0" 
                                    value={flexForm.amount}
                                    onChange={e => setFlexForm({...flexForm, amount: e.target.value})}
                                    required
                                />
                            </div>
                            <div className="space-y-1">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block pl-1">Tanggal Bayar</label>
                                <input 
                                    type="date" 
                                    className="w-full border-gray-200 rounded-xl py-2.5 px-4 text-sm focus:ring-[#901C31]/10 focus:border-[#901C31] outline-none" 
                                    value={flexForm.paid_date}
                                    onChange={e => setFlexForm({...flexForm, paid_date: e.target.value})}
                                    required
                                />
                            </div>
                            <div className="space-y-1">
                                <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest block pl-1">Catatan (Optional)</label>
                                <input 
                                    type="text" 
                                    className="w-full border-gray-200 rounded-xl py-2.5 px-4 text-sm focus:ring-[#901C31]/10 focus:border-[#901C31] outline-none" 
                                    placeholder="Pembayaran fleksibel..." 
                                    value={flexForm.notes}
                                    onChange={e => setFlexForm({...flexForm, notes: e.target.value})}
                                />
                            </div>
                            <button type="submit" className="w-full py-3 bg-gray-900 text-white rounded-xl font-bold hover:bg-black transition-all shadow-lg shadow-gray-200 mt-2">
                                Simpan Pembayaran
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {/* Edit Modal */}
            <SalesModal 
                isOpen={showEditModal}
                onClose={() => setShowEditModal(false)}
                onSuccess={(msg) => {
                    showToast(msg);
                    fetchDetail();
                }}
                isEdit={true}
                initialData={transaction}
                kavlings={kavlings}
                buyers={buyers}
                salesList={salesList}
                refreshOptions={fetchOptions}
            />

            {/* Modal Pembatalan */}
            {showCancelModal && (
                <div className="fixed inset-0 z-[120] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
                    <div className="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden animate-in fade-in zoom-in-95 duration-200">
                        <div className="p-6 border-b border-gray-100 flex items-center justify-between">
                            <div>
                                <h3 className="text-xl font-bold text-gray-900">Pembatalan Penjualan</h3>
                                <p className="text-xs text-gray-500 mt-1">Pilih metode pembatalan transaksi unit {transaction.plot?.plot_number}</p>
                            </div>
                            <button onClick={() => setShowCancelModal(false)} className="p-2 hover:bg-gray-100 rounded-full transition-colors"><FiX /></button>
                        </div>
                        
                        <form onSubmit={handleCancelSubmit} className="p-6 space-y-6">
                            <div className="grid grid-cols-3 gap-3">
                                {[
                                    { id: 'refund', label: 'Refund', sub: 'Uang Kembali', icon: <FiRotateCcw /> },
                                    { id: 'transfer_credit', label: 'Oper', sub: 'Ganti Pembeli', icon: <FiUserPlus /> },
                                    { id: 'delete', label: 'Hapus', sub: 'Hapus Data', icon: <FiTrash2 /> }
                                ].map(opt => (
                                    <button 
                                        key={opt.id}
                                        type="button"
                                        onClick={() => setCancelForm({ ...cancelForm, cancel_type: opt.id })}
                                        className={`p-4 rounded-xl border-2 transition-all flex flex-col items-center gap-2 ${
                                            cancelForm.cancel_type === opt.id 
                                            ? 'border-[#901C31] bg-red-50 text-[#901C31]' 
                                            : 'border-gray-50 bg-gray-50 text-gray-400 hover:border-gray-200'
                                        }`}
                                    >
                                        <div className="text-xl">{opt.icon}</div>
                                        <div className="text-center">
                                            <p className="text-[10px] font-bold uppercase tracking-wider leading-none">{opt.label}</p>
                                            <p className="text-[8px] mt-1 font-medium opacity-60 leading-none">{opt.sub}</p>
                                        </div>
                                    </button>
                                ))}
                            </div>

                            {cancelForm.cancel_type === 'refund' && (
                                <div className="space-y-4 p-4 bg-gray-50 rounded-xl animate-in fade-in duration-300">
                                    <div className="space-y-1.5 font-primary">
                                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Nominal Refund (Biaya Keluar)</label>
                                        <input 
                                            type="text"
                                            className="w-full border-gray-200 rounded-xl py-3 px-4 text-sm font-bold bg-white focus:ring-[#901C31]/10 focus:border-[#901C31]"
                                            placeholder="Contoh: 5.000.000"
                                            value={new Intl.NumberFormat('id-ID').format(cancelForm.refund_amount)}
                                            onChange={(e) => {
                                                const val = e.target.value.replace(/[^0-9]/g, '');
                                                setCancelForm({ ...cancelForm, refund_amount: val ? parseInt(val) : 0 });
                                            }}
                                        />
                                        <p className="text-[10px] text-gray-400 italic mt-2">* Nominal ini akan dicatat sebagai Pengeluaran Cashback/Refund di laporan keuangan.</p>
                                    </div>
                                </div>
                            )}

                            {cancelForm.cancel_type === 'transfer_credit' && (
                                <div className="space-y-4 p-4 bg-gray-50 rounded-xl animate-in fade-in duration-300">
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Pembeli Baru</label>
                                        <select 
                                            required
                                            className="w-full border-gray-200 rounded-xl py-3 px-4 text-sm bg-white"
                                            value={cancelForm.new_buyer_id}
                                            onChange={e => setCancelForm({ ...cancelForm, new_buyer_id: e.target.value })}
                                        >
                                            <option value="">-- Pilih Pembeli Baru --</option>
                                            {buyers.map(b => (
                                                <option key={b.id} value={b.id}>{b.name}</option>
                                            ))}
                                        </select>
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-[10px] font-bold text-gray-400 uppercase tracking-widest pl-1">Marketing Baru (Opsional)</label>
                                        <select 
                                            className="w-full border-gray-200 rounded-xl py-3 px-4 text-sm bg-white"
                                            value={cancelForm.new_sales_staff_id}
                                            onChange={e => setCancelForm({ ...cancelForm, new_sales_staff_id: e.target.value })}
                                        >
                                            <option value="">-- Tetap / Pilih Baru --</option>
                                            {salesList.map(s => (
                                                <option key={s.id} value={s.id}>{s.name}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>
                            )}

                            {cancelForm.cancel_type === 'delete' && (
                                <div className="p-4 bg-red-50 border border-red-100 rounded-xl animate-in fade-in duration-300">
                                    <div className="flex gap-3">
                                        <FiAlertCircle className="w-5 h-5 text-red-600 shrink-0 mt-0.5" />
                                        <div>
                                            <p className="text-sm font-bold text-red-900">Peringatan Penghapusan</p>
                                            <p className="text-xs text-red-700/80 mt-1 leading-relaxed">
                                                Tindakan ini akan menghapus permanen data penjualan, riwayat pembayaran, dan jadwal angsuran. Unit akan dikembalikan menjadi <strong>Available</strong>.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            )}

                            <div className="flex gap-3 pt-4">
                                <button 
                                    type="button" 
                                    onClick={() => setShowCancelModal(false)}
                                    className="flex-1 py-3.5 text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors"
                                >
                                    Tutup
                                </button>
                                <button 
                                    type="submit"
                                    className="flex-1 py-3.5 bg-[#901C31] text-white rounded-xl font-bold text-sm shadow-lg shadow-red-900/20 hover:bg-red-900 transition-all"
                                >
                                    Proses Pembatalan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </div>
    );
}
