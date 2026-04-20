import React, { useState, useEffect, useCallback } from 'react';
import axios from '../../lib/axios';
import { useOutletContext, Link } from 'react-router-dom';
import { 
    FiPlus, FiSearch, FiFilter, FiEdit, FiTrash2, 
    FiEye, FiCheckCircle
} from 'react-icons/fi';
import SalesModal from '../../components/sales/SalesModal';
import Toast from '../../components/ui/Toast';
import ConfirmModal from '../../components/ui/ConfirmModal';
import { formatRupiah } from '../../utils/formatters';
import { useToast } from '../../hooks/useToast';

export default function SalesTransactionList() {
    const { user } = useOutletContext();
    const [transactions, setTransactions] = useState([]);
    const [loading, setLoading] = useState(true);
    const [pagination, setPagination] = useState({});
    
    // Options
    const [kavlings, setKavlings] = useState([]);
    const [buyers, setBuyers] = useState([]);
    const [salesList, setSalesList] = useState([]);
    
    // Filters State
    const [showFilters, setShowFilters] = useState(false);
    const [filters, setFilters] = useState({
        search: '',
        payment_method: '',
        dp_status: '',
        status: '',
        sales_staff_id: '',
        buyer_id: '',
        price_min: '',
        price_max: '',
        date_from: '',
        date_to: '',
        page: 1
    });

    // Modal State
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    const [selectedTx, setSelectedTx] = useState(null);
    const [confirmModal, setConfirmModal] = useState({ show: false, id: null });

    const { toast, showToast } = useToast();

    const fetchData = useCallback(async () => {
        setLoading(true);
        try {
            const params = { ...filters };
            Object.keys(params).forEach(key => {
                if (!params[key]) delete params[key];
            });
            const res = await axios.get('/api/master/sales-transaction', { params });
            setTransactions(res.data.data);
            setPagination(res.data.pagination);
        } catch (err) {
            console.error('Failed to fetch transactions', err);
        } finally {
            setLoading(false);
        }
    }, [filters]);

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

    useEffect(() => {
        fetchData();
        fetchOptions();
    }, [fetchData]);

    const handleFilterChange = (e) => {
        setFilters({ ...filters, [e.target.name]: e.target.value, page: 1 });
    };

    const handleResetFilters = () => {
        setFilters({
            search: '',
            payment_method: '',
            dp_status: '',
            status: '',
            sales_staff_id: '',
            buyer_id: '',
            price_min: '',
            price_max: '',
            date_from: '',
            date_to: '',
            page: 1
        });
    };

    const handleOpenModal = (tx = null) => {
        if (tx) {
            setIsEdit(true);
            axios.get(`/api/master/sales-transaction/${tx.id}`).then(res => {
                setSelectedTx(res.data);
                setShowModal(true);
            });
        } else {
            setIsEdit(false);
            setSelectedTx(null);
            setShowModal(true);
        }
    };

    const handleDelete = async () => {
        try {
            await axios.delete(`/api/master/sales-transaction/${confirmModal.id}`);
            showToast('Transaksi berhasil dihapus');
            setConfirmModal({ show: false, id: null });
            fetchData();
        } catch (err) {
            showToast('Gagal menghapus transaksi', 'error');
        }
    };

    return (
        <div className="space-y-6 pb-12 relative">
            <Toast toast={toast} />

            <ConfirmModal
                show={confirmModal.show}
                title="Hapus Transaksi"
                message="Apakah Anda yakin ingin menghapus transaksi ini? Tindakan ini tidak dapat dibatalkan."
                onConfirm={handleDelete}
                onClose={() => setConfirmModal({ show: false, id: null })}
                confirmLabel="Hapus Sekarang"
                confirmClass="bg-red-600 text-white shadow-lg shadow-red-900/20"
                icon={
                    <div className="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-6">
                        <FiTrash2 className="w-6 h-6 text-red-600" />
                    </div>
                }
            />

            {/* Header */}
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800 tracking-tight font-primary">Daftar Penjualan</h1>
                    <p className="text-gray-500 text-sm">Monitoring transaksi dan status pembayaran</p>
                </div>
                <div className="flex items-center gap-3">
                    <button 
                        onClick={() => setShowFilters(!showFilters)}
                        className={`flex items-center justify-center px-4 py-2.5 border rounded-xl font-bold transition-all shadow-sm ${
                            showFilters 
                            ? 'bg-gray-100 text-gray-800 border-gray-200' 
                            : 'bg-white text-gray-600 border-gray-200 hover:bg-gray-50'
                        }`}
                    >
                        <FiFilter className="mr-2" /> Filter
                    </button>
                    <button 
                        onClick={() => handleOpenModal()}
                        className="flex items-center justify-center px-6 py-2.5 bg-[#901C31] text-white rounded-xl hover:bg-red-900 transition-all font-bold shadow-lg shadow-red-900/20"
                    >
                        <FiPlus className="mr-2 w-5 h-5" /> Transaksi Baru
                    </button>
                </div>
            </div>

            {/* Filter Section */}
            {showFilters && (
                <div className="bg-white rounded-[1.5rem] shadow-sm border border-gray-100/80 overflow-hidden animate-in fade-in slide-in-from-top-2 duration-300">
                    <div className="p-6 md:p-8">
                        <div className="flex items-center justify-between mb-6">
                            <div className="flex items-center gap-2">
                                <div className="w-1 h-4 bg-[#901C31] rounded-full" />
                                <h2 className="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em]">Filter Pencarian</h2>
                            </div>
                            <button onClick={handleResetFilters} className="text-[10px] font-bold text-[#901C31] hover:text-red-900 transition-colors uppercase tracking-widest px-3 py-1 bg-red-50 rounded-full">Reset All</button>
                        </div>
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-6">
                            <div className="space-y-2">
                                <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Kata Kunci</label>
                                <div className="relative group">
                                    <FiSearch className="absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-300 group-focus-within:text-[#901C31] transition-colors" />
                                    <input 
                                        type="text" name="search"
                                        className="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-100 rounded-xl text-sm focus:ring-4 focus:ring-[#901C31]/5 focus:border-[#901C31]/20 transition-all placeholder:text-gray-300 outline-none"
                                        placeholder="Unit atau Nama..."
                                        value={filters.search}
                                        onChange={handleFilterChange}
                                    />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Metode Bayar</label>
                                <select name="payment_method" className="w-full bg-white border border-gray-100 rounded-xl text-sm py-2.5 px-4 focus:ring-4 focus:ring-[#901C31]/5 focus:border-[#901C31]/20 outline-none appearance-none cursor-pointer" value={filters.payment_method} onChange={handleFilterChange}>
                                    <option value="">Semua Metode</option>
                                    <option value="full_cash">Cash Keras</option>
                                    <option value="installment">Angsuran In-house</option>
                                    <option value="bank_mortgage">KPR Bank</option>
                                </select>
                            </div>
                            <div className="space-y-2">
                                <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Status Penjualan</label>
                                <select name="status" className="w-full bg-white border border-gray-100 rounded-xl text-sm py-2.5 px-4 focus:ring-4 focus:ring-[#901C31]/5 focus:border-[#901C31]/20 outline-none appearance-none cursor-pointer" value={filters.status} onChange={handleFilterChange}>
                                    <option value="">Semua Status</option>
                                    <option value="active">Active</option>
                                    <option value="paid_off">Paid Off</option>
                                    <option value="cancelled">Batal</option>
                                    <option value="refunded">Refunded</option>
                                </select>
                            </div>
                            <div className="space-y-2">
                                <label className="text-[10px] font-bold text-gray-500 uppercase tracking-widest pl-1">Marketing</label>
                                <select name="sales_staff_id" className="w-full bg-white border border-gray-100 rounded-xl text-sm py-2.5 px-4 focus:ring-4 focus:ring-[#901C31]/5 focus:border-[#901C31]/20 outline-none appearance-none cursor-pointer" value={filters.sales_staff_id} onChange={handleFilterChange}>
                                    <option value="">Semua Marketing</option>
                                    {salesList.map(s => (
                                        <option key={s.id} value={s.id}>{s.name}</option>
                                    ))}
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            )}

            {/* Tabel */}
            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden mt-6">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-100">
                        <thead className="bg-gray-50/50">
                            <tr>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest whitespace-nowrap">No. Transaksi</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Unit</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Pembeli</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Booking</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Metode</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Harga Jual</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Sisa Piutang</th>
                                <th className="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                                <th className="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 bg-white">
                            {loading ? (
                                <tr>
                                    <td colSpan="9" className="px-6 py-12 text-center text-gray-400 animate-pulse font-medium">Memuat data transaksi...</td>
                                </tr>
                            ) : transactions.length === 0 ? (
                                <tr>
                                    <td colSpan="9" className="px-6 py-12 text-center text-gray-400 font-medium">Tidak ada transaksi ditemukan</td>
                                </tr>
                            ) : transactions.map(tx => (
                                <tr key={tx.id} className="hover:bg-gray-50/50 transition-colors group">
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className="text-[10px] font-bold text-[#901C31] uppercase tracking-wider bg-red-50 px-2 py-1 rounded">{tx.transaction_number}</span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className="text-sm font-bold text-gray-800">{tx.plot}</span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium">{tx.buyer}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{tx.booking_date}</td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className="text-[10px] font-bold text-gray-400 uppercase tracking-widest">
                                            {tx.payment_method === 'full_cash' ? 'Cash' : tx.payment_method === 'installment' ? 'Inhouse' : 'KPR'}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{formatRupiah(tx.grand_total)}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-red-600">{formatRupiah(tx.remaining_balance)}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                        <span className={`px-2.5 py-1 rounded-full text-[10px] font-bold uppercase ${
                                            tx.status === 'paid_off' ? 'bg-green-100 text-green-700' :
                                            tx.status === 'active' ? 'bg-blue-100 text-blue-700' :
                                            'bg-red-100 text-red-700'
                                        }`}>
                                            {tx.status}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-center">
                                        <div className="flex items-center justify-center space-x-1">
                                            <Link to={`/penjualan/detail/${tx.id}`} className="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Lihat Detail">
                                                <FiEye className="w-4 h-4" />
                                            </Link>
                                            <button onClick={() => handleOpenModal(tx)} className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit">
                                                <FiEdit className="w-4 h-4" />
                                            </button>
                                            <button onClick={() => setConfirmModal({ show: true, id: tx.id })} className="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus">
                                                <FiTrash2 className="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            <SalesModal 
                isOpen={showModal}
                onClose={() => setShowModal(false)}
                onSuccess={(msg) => {
                    fetchData();
                    showToast(msg);
                }}
                isEdit={isEdit}
                initialData={selectedTx}
                kavlings={kavlings}
                buyers={buyers}
                salesList={salesList}
                refreshOptions={fetchOptions}
            />
        </div>
    );
}
