import { useCallback, useEffect, useState } from 'react';
import {
    FiArrowDownCircle,
    FiArrowUpCircle,
    FiCalendar,
    FiMap,
    FiRefreshCw,
    FiShoppingBag,
    FiTrendingUp,
    FiXCircle
} from 'react-icons/fi';
import { useNavigate, useOutletContext } from 'react-router-dom';
import {
    Bar,
    BarChart,
    CartesianGrid,
    Cell,
    Pie,
    PieChart,
    ResponsiveContainer,
    Tooltip,
    XAxis, YAxis
} from 'recharts';
import axios from '../lib/axios';

// ─── Helpers ──────────────────────────────────────
const IDRFull = (val) =>
    new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(parseFloat(val) || 0);

const PERIOD_MAP = {
    'Minggu Ini': 'minggu_ini',
    'Bulan Ini': 'bulan_ini',
    'Tahun Ini': 'tahun_ini',
    'Tahun Lalu': 'tahun_lalu',
    'Semua': 'semua',
};

const COLORS_PIE = ['#1e40af', '#901C31', '#059669', '#d97706', '#4f46e5', '#db2777'];
const COLORS_BARS = { nilai: '#901C31', unit: '#1e40af' };

// ─── Sub-komponen KPI Card ─────────────────────────
function KpiCard({ title, icon, children, accent = 'blue' }) {
    const iconColors = {
        green: 'text-emerald-600 bg-emerald-50',
        red: 'text-rose-600 bg-rose-50',
        blue: 'text-blue-600 bg-blue-50',
        maroon: 'text-[#901C31] bg-red-50',
        amber: 'text-amber-600 bg-amber-50',
    };
    return (
        <div className="bg-white rounded-xl border border-gray-100 p-5 flex flex-col gap-4 shadow-sm hover:shadow-md transition-all duration-200">
            <div className="flex justify-between items-center">
                <span className="text-xs font-bold text-gray-400 uppercase tracking-widest">{title}</span>
                <span className={`p-2 rounded-lg ${iconColors[accent] || iconColors.blue}`}>{icon}</span>
            </div>
            <div className="space-y-1">
                {children}
            </div>
        </div>
    );
}

// ─── Custom Tooltip ────────────────────────────────
const ChartTooltip = ({ active, payload, label }) => {
    if (!active || !payload?.length) return null;
    return (
        <div className="bg-white border border-gray-100 shadow-xl rounded-lg p-3 text-sm">
            <p className="font-bold text-gray-800 mb-2 border-b border-gray-50 pb-1">{label}</p>
            {payload.map((p, i) => (
                <div key={i} className="flex items-center gap-2 mb-1 last:mb-0">
                    <div className="w-2 h-2 rounded-full" style={{ backgroundColor: p.color }} />
                    <span className="text-gray-500">{p.name}:</span>
                    <span className="font-bold text-gray-900">
                        {typeof p.value === 'number' && p.value > 1000 ? IDRFull(p.value) : p.value}
                    </span>
                </div>
            ))}
        </div>
    );
};

// ─── Toggle Nilai / Unit ───────────────────────────
function ChartToggle({ value, onChange }) {
    return (
        <div className="flex bg-gray-50 rounded-lg p-1 border border-gray-100">
            {['Nilai', 'Unit'].map(m => (
                <button
                    key={m}
                    onClick={() => onChange(m)}
                    className={`px-3 py-1 text-xs font-semibold rounded-md transition-all ${value === m ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-400 hover:text-gray-600'}`}
                >
                    {m}
                </button>
            ))}
        </div>
    );
}

export default function Dashboard() {
    const { user } = useOutletContext();
    const navigate = useNavigate();

    // State
    const [loading, setLoading] = useState(true);
    const [data, setData] = useState(null);
    const [error, setError] = useState(null);
    const [period, setPeriod] = useState('Bulan Ini');
    const [startDate, setStartDate] = useState('');
    const [endDate, setEndDate] = useState('');
    const [kasMode, setKasMode] = useState('Nilai');
    const [jualMode, setJualMode] = useState('Nilai');
    const [compare, setCompare] = useState(false);

    // Fetch
    const fetchData = useCallback(async (overridePeriod) => {
        setLoading(true);
        setError(null);
        const p = overridePeriod ?? period;
        const filterKey = PERIOD_MAP[p] ?? 'bulan_ini';
        try {
            const params = new URLSearchParams({ filter: filterKey });
            if (compare) params.set('bandingkan', 'true');
            if (filterKey === 'semua' || (startDate && endDate)) {
                if (startDate) params.set('start_date', startDate);
                if (endDate) params.set('end_date', endDate);
                if (startDate && endDate) params.set('filter', 'custom');
            }
            const res = await axios.get(`/api/dashboard?${params}`);
            const body = res.data?.data ?? res.data;
            setData(body);
        } catch (e) {
            console.error(e);
            setError('Gagal memuat data dashboard.');
        } finally {
            setLoading(false);
        }
    }, [period, startDate, endDate, compare]);

    useEffect(() => {
        if (user?.role === 'super_admin') { navigate('/admin/licenses'); return; }
        if (user) fetchData();
    }, [user]);

    if (!user) return null;

    // Shortcuts
    const S = data?.summary ?? {};
    const T = data?.tren_penjualan ?? {};
    const M = data?.performa_tim_marketing ?? [];
    const PP = data?.persediaan_per_proyek ?? [];

    const penerimaan = S.penerimaan_periode_ini ?? {};
    const dp = S.total_dp_diterima ?? {};
    const penjualan = S.total_penjualan ?? {};
    const batal = S.total_penjualan_batal ?? {};
    const piutang = S.total_piutang ?? {};
    const persediaan = S.nilai_persediaan ?? {};

    const chartJualData = (T.label ?? []).map((label, idx) => ({
        label,
        nilai: T.nilai?.[idx] ?? 0,
        unit: T.unit?.[idx] ?? 0
    }));

    return (
        <div className="max-w-7xl mx-auto space-y-6 sm:space-y-8 pb-12">

            {/* Header / Filters (Responsive) */}
            <div className="flex flex-col gap-4">
                <div className="flex flex-col md:flex-row gap-3 items-start md:items-center justify-between">
                    <div className="flex flex-wrap items-center gap-2 bg-white rounded-xl shadow-sm border border-gray-100 p-1 w-full md:w-auto">
                        {Object.keys(PERIOD_MAP).map(label => (
                            <button
                                key={label}
                                onClick={() => { setPeriod(label); fetchData(label); }}
                                className={`flex-1 md:flex-none px-3 sm:px-4 py-1.5 text-xs sm:text-sm font-semibold rounded-lg transition-all ${period === label ? 'bg-gray-900 text-white shadow-md' : 'text-gray-500 hover:text-gray-800'}`}
                            >{label}</button>
                        ))}
                    </div>

                    <div className="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 w-full md:w-auto">
                        <div className="flex items-center gap-2 bg-white rounded-xl shadow-sm border border-gray-100 p-1 flex-1">
                            <input type="date" className="text-xs sm:text-sm font-semibold text-gray-700 bg-transparent border-none focus:ring-0 p-1.5 flex-1 min-w-[120px]"
                                value={startDate} onChange={e => setStartDate(e.target.value)} />
                            <span className="text-gray-300 font-bold px-1 hidden sm:inline">•</span>
                            <input type="date" className="text-xs sm:text-sm font-semibold text-gray-700 bg-transparent border-none focus:ring-0 p-1.5 flex-1 min-w-[120px]"
                                value={endDate} onChange={e => setEndDate(e.target.value)} />
                        </div>
                        
                        <div className="flex items-center gap-2">
                             <label className="flex flex-1 items-center justify-center gap-2 px-3 py-2 bg-white rounded-xl border border-gray-100 shadow-sm cursor-pointer hover:bg-gray-50 transition-colors group">
                                <input type="checkbox" checked={compare} onChange={(e) => setCompare(e.target.checked)}
                                    className="w-4 h-4 text-gray-900 border-gray-300 rounded focus:ring-gray-900" />
                                <span className="text-sm font-semibold text-gray-500 group-hover:text-gray-900">Bandingkan</span>
                            </label>

                            <button onClick={() => fetchData()} className="bg-[#901C31] text-white p-2.5 rounded-xl shadow-lg hover:bg-black transition-all active:scale-95 group shrink-0">
                                <FiRefreshCw className={`w-5 h-5 ${loading ? 'animate-spin' : 'group-hover:rotate-180 transition-transform duration-500'}`} />
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {loading ? (
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                    {[...Array(4)].map((_, i) => (
                        <div key={i} className="bg-white rounded-xl border border-gray-100 p-6 h-36 animate-pulse">
                            <div className="h-4 bg-gray-100 rounded w-1/2 mb-4" />
                            <div className="h-8 bg-gray-100 rounded w-3/4" />
                        </div>
                    ))}
                </div>
            ) : error ? (
                <div className="bg-rose-50 text-rose-700 border border-rose-200 rounded-xl p-6 font-semibold flex items-center gap-3 text-sm sm:text-base">
                    <FiXCircle className="w-5 h-5" /> {error}
                </div>
            ) : (
                <>
                    {/* Row 1: KPI Cards */}
                    <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
                        <KpiCard title="Revenue" icon={<FiArrowUpCircle className="w-4 h-4 sm:w-5 sm:h-5" />} accent="blue">
                            <p className="text-2xl sm:text-3xl font-bold text-gray-900">{IDRFull(penerimaan.value).replace('Rp\u00A0', '')}</p>
                            <div className="flex justify-between items-center pt-2">
                                <span className="text-[10px] sm:text-xs font-semibold text-gray-400 uppercase tracking-tighter">{penerimaan.total_transaksi ?? 0} Transaksi</span>
                                {penerimaan.comparison && (
                                    <span className={`text-[10px] sm:text-xs font-bold ${penerimaan.comparison.percentage >= 0 ? 'text-emerald-600' : 'text-rose-600'}`}>
                                        {penerimaan.comparison.percentage >= 0 ? '+' : ''}{penerimaan.comparison.percentage}%
                                    </span>
                                )}
                            </div>
                        </KpiCard>

                        <KpiCard title="DP Received" icon={<FiArrowDownCircle className="w-4 h-4 sm:w-5 sm:h-5" />} accent="green">
                            <p className="text-2xl sm:text-3xl font-bold text-gray-900">{IDRFull(dp.value).replace('Rp\u00A0', '')}</p>
                            <div className="flex justify-between items-center pt-2">
                                <span className="text-[10px] sm:text-xs font-semibold text-gray-400 uppercase tracking-tighter">{dp.total_penjualan ?? 0} Unit</span>
                                {dp.comparison && (
                                    <span className={`text-[10px] sm:text-xs font-bold ${dp.comparison.percentage >= 0 ? 'text-emerald-600' : 'text-rose-600'}`}>
                                        {dp.comparison.percentage >= 0 ? '+' : ''}{dp.comparison.percentage}%
                                    </span>
                                )}
                            </div>
                        </KpiCard>

                        <KpiCard title="Active Sales" icon={<FiTrendingUp className="w-4 h-4 sm:w-5 sm:h-5" />} accent="maroon">
                            <p className="text-2xl sm:text-3xl font-bold text-gray-900">{penjualan.unit ?? 0} <span className="text-sm font-semibold text-gray-400">Unit</span></p>
                            <p className="text-[10px] sm:text-xs font-semibold text-gray-400 uppercase pt-2">Valuation: {IDRFull(penjualan.nilai)}</p>
                        </KpiCard>

                        <KpiCard title="Cancelled" icon={<FiXCircle className="w-4 h-4 sm:w-5 sm:h-5" />} accent="red">
                            <p className="text-2xl sm:text-3xl font-bold text-gray-900">{batal.unit ?? 0} <span className="text-sm font-semibold text-gray-400">Unit</span></p>
                            <p className="text-[10px] sm:text-xs font-semibold text-rose-400 uppercase pt-2">Loss: {IDRFull(batal.nilai)}</p>
                        </KpiCard>
                    </div>

                    {/* Row 2: Asset Management */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
                        <div className="bg-white rounded-xl border border-gray-100 p-6 sm:p-8 shadow-sm flex flex-col sm:flex-row justify-between relative overflow-hidden">
                            <div className="flex-1">
                                <div className="flex items-center gap-3 mb-6">
                                    <div className="p-2 bg-amber-50 text-amber-600 rounded-lg"><FiShoppingBag className="w-5 h-5" /></div>
                                    <h3 className="text-xs sm:text-sm font-bold text-gray-400 uppercase tracking-widest">Global Receivables</h3>
                                </div>
                                <p className="text-3xl sm:text-4xl font-bold text-gray-900 leading-none">{IDRFull(piutang.value).replace('Rp\u00A0', '')}</p>
                                <p className="text-xs sm:text-sm font-semibold text-gray-400 mt-3">{piutang.total_penjualan_aktif ?? 0} Piutang Aktif</p>
                            </div>
                            <div className="flex flex-row sm:flex-col justify-start sm:justify-end gap-2 mt-6 sm:mt-0">
                                <div className="flex items-center gap-2 px-2 sm:px-3 py-1.5 bg-emerald-50 text-emerald-700 rounded-lg border border-emerald-100">
                                    <span className="w-2 h-2 rounded-full bg-emerald-500" />
                                    <span className="text-[10px] font-bold uppercase whitespace-nowrap">Healthy: 100%</span>
                                </div>
                                <div className="flex items-center gap-2 px-2 sm:px-3 py-1.5 bg-amber-50 text-amber-700 rounded-lg border border-amber-100">
                                    <span className="w-2 h-2 rounded-full bg-amber-500" />
                                    <span className="text-[10px] font-bold uppercase whitespace-nowrap">Attention: 0%</span>
                                </div>
                            </div>
                        </div>

                        <div className="bg-white rounded-xl border border-gray-100 p-6 sm:p-8 shadow-sm flex flex-col sm:flex-row gap-8 relative items-center sm:items-start text-center sm:text-left">
                            <div className="flex-1">
                                <div className="flex items-center justify-center sm:justify-start gap-3 mb-6">
                                    <div className="p-2 bg-blue-50 text-blue-600 rounded-lg"><FiMap className="w-5 h-5" /></div>
                                    <h3 className="text-xs sm:text-sm font-bold text-gray-400 uppercase tracking-widest">Inventory Value</h3>
                                </div>
                                <p className="text-3xl sm:text-4xl font-bold text-gray-900 leading-none">{IDRFull(persediaan.value).replace('Rp\u00A0', '')}</p>
                                <p className="text-xs sm:text-sm font-semibold text-gray-400 mt-3">{persediaan.total_unit ?? 0} Unit Tersedia</p>
                            </div>
                            <div className="w-28 h-28 sm:w-32 sm:h-32">
                                <ResponsiveContainer width="100%" height="100%">
                                    <PieChart>
                                        <Pie data={PP.filter(i => i.unit > 0)} innerRadius={35} outerRadius={45} stroke="none" dataKey="unit">
                                            {PP.map((_, i) => <Cell key={i} fill={COLORS_PIE[i % COLORS_PIE.length]} />)}
                                        </Pie>
                                    </PieChart>
                                </ResponsiveContainer>
                            </div>
                        </div>
                    </div>

                    {/* Row 3: Main Trend Charts (Scrollable on Mobile) */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-8">
                        {/* Revenue Performance Chart */}
                        <div className="bg-white rounded-xl border border-gray-100 p-6 sm:p-8 shadow-sm overflow-hidden">
                            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                                <div>
                                    <h3 className="text-base sm:text-lg font-bold text-gray-900">Revenue Performance</h3>
                                    <p className="text-xs sm:text-sm font-medium text-gray-400 mt-1">Tren penjualan periode ini</p>
                                </div>
                                <ChartToggle value={jualMode} onChange={setJualMode} />
                            </div>
                            <div className="overflow-x-auto pb-4">
                                <div className="h-64 min-w-[500px]">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <BarChart data={chartJualData} margin={{ top: 0, right: 0, left: -20, bottom: 0 }}>
                                            <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f5f9" />
                                            <XAxis dataKey="label" axisLine={false} tickLine={false} tick={{ fontSize: 11, fontWeight: 600, fill: '#94a3b8' }} />
                                            <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 11, fontWeight: 600, fill: '#94a3b8' }}
                                                tickFormatter={v => jualMode === 'Nilai' ? (v >= 1_000_000 ? `${(v/1_000_000).toFixed(0)}Jt` : v) : v} />
                                            <Tooltip cursor={{fill: '#f8fafc'}} content={<ChartTooltip />} />
                                            <Bar dataKey={jualMode === 'Nilai' ? 'nilai' : 'unit'} fill={COLORS_BARS.nilai} radius={[4, 4, 0, 0]} maxBarSize={30} />
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            </div>
                        </div>

                        {/* Marketing Index Chart */}
                        <div className="bg-white rounded-xl border border-gray-100 p-6 sm:p-8 shadow-sm overflow-hidden">
                            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                                <div>
                                    <h3 className="text-base sm:text-lg font-bold text-gray-900">Marketing Index</h3>
                                    <p className="text-xs sm:text-sm font-medium text-gray-400 mt-1">Performa tim penjualan</p>
                                </div>
                                <ChartToggle value={kasMode} onChange={setKasMode} />
                            </div>
                            <div className="overflow-x-auto pb-4">
                                <div className="h-64 min-w-[500px]">
                                    <ResponsiveContainer width="100%" height="100%">
                                        <BarChart data={M} margin={{ top: 0, right: 0, left: -20, bottom: 0 }}>
                                            <CartesianGrid strokeDasharray="3 3" vertical={false} stroke="#f1f5f9" />
                                            <XAxis dataKey="label" axisLine={false} tickLine={false} tick={{ fontSize: 11, fontWeight: 600, fill: '#94a3b8' }} />
                                            <YAxis axisLine={false} tickLine={false} tick={{ fontSize: 11, fontWeight: 600, fill: '#94a3b8' }}
                                                tickFormatter={v => kasMode === 'Nilai' ? (v >= 1_000_000 ? `${(v/1_000_000).toFixed(0)}Jt` : v) : v} />
                                            <Tooltip cursor={{fill: '#f8fafc'}} content={<ChartTooltip />} />
                                            <Bar dataKey={kasMode === 'Nilai' ? 'nilai' : 'unit'} fill="#111827" radius={[4, 4, 0, 0]} maxBarSize={30} />
                                        </BarChart>
                                    </ResponsiveContainer>
                                </div>
                            </div>
                        </div>
                    </div>

                    {/* Row 4: Project Shares (Scrollable on Mobile) */}
                    <div className="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-8">
                         {/* Project Share Section */}
                         <div className="bg-white rounded-xl border border-gray-100 p-6 sm:p-8 shadow-sm overflow-hidden min-h-[300px]">
                            <h3 className="text-base sm:text-lg font-bold text-gray-900 mb-8">Penjualan per Proyek</h3>
                            <div className="overflow-x-auto">
                                <div className="min-w-[450px] flex items-center justify-between gap-10">
                                    <div className="w-52 h-52 shrink-0">
                                        <ResponsiveContainer width="100%" height="100%">
                                            <PieChart>
                                                <Pie data={PP.filter(i => i.nilai > 0)} cx="50%" cy="50%" innerRadius={60} outerRadius={85} paddingAngle={5} dataKey="nilai" stroke="none">
                                                    {PP.map((_, i) => <Cell key={i} fill={COLORS_PIE[i % COLORS_PIE.length]} />)}
                                                </Pie>
                                                <Tooltip formatter={(v) => IDRFull(v)} />
                                            </PieChart>
                                        </ResponsiveContainer>
                                    </div>
                                    <div className="flex-1 space-y-4">
                                        {PP.map((p, i) => (
                                            <div key={i} className="flex flex-col gap-1.5">
                                                <div className="flex justify-between items-center">
                                                    <div className="flex items-center gap-2">
                                                        <span className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: COLORS_PIE[i % COLORS_PIE.length] }} />
                                                        <span className="text-xs font-bold text-gray-700">{p.label}</span>
                                                    </div>
                                                    <span className="text-xs font-bold text-gray-900">{p.persentase}%</span>
                                                </div>
                                                <div className="w-full h-1.5 bg-gray-50 rounded-full overflow-hidden border border-gray-100">
                                                    <div className="h-full rounded-full transition-all duration-1000" style={{ width: `${p.persentase}%`, backgroundColor: COLORS_PIE[i % COLORS_PIE.length] }} />
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                         </div>

                         {/* Project Inventory Value */}
                         <div className="bg-white rounded-xl border border-gray-100 p-6 sm:p-8 shadow-sm overflow-hidden min-h-[300px]">
                            <h3 className="text-base sm:text-lg font-bold text-gray-900 mb-8">Persediaan per Proyek</h3>
                            <div className="overflow-x-auto">
                                <div className="min-w-[450px] flex items-center justify-between gap-10">
                                    <div className="w-52 h-52 shrink-0">
                                        <ResponsiveContainer width="100%" height="100%">
                                            <PieChart>
                                                <Pie data={PP.filter(i => i.unit > 0)} cx="50%" cy="50%" innerRadius={60} outerRadius={85} paddingAngle={5} dataKey="unit" stroke="none">
                                                    {PP.map((_, i) => <Cell key={i} fill={i ===  0 ? '#901C31' : COLORS_PIE[(i+1) % COLORS_PIE.length]} />)}
                                                </Pie>
                                                <Tooltip />
                                            </PieChart>
                                        </ResponsiveContainer>
                                    </div>
                                    <div className="flex-1 space-y-4">
                                        {PP.map((p, i) => (
                                            <div key={i} className="flex flex-col gap-1.5">
                                                <div className="flex justify-between items-center">
                                                    <div className="flex items-center gap-2">
                                                        <span className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: i === 0 ? '#901C31' : COLORS_PIE[(i+1) % COLORS_PIE.length] }} />
                                                        <span className="text-xs font-bold text-gray-700">{p.label}</span>
                                                    </div>
                                                    <span className="text-xs font-bold text-gray-900">{p.unit} Unit</span>
                                                </div>
                                                <div className="w-full h-1.5 bg-gray-50 rounded-full overflow-hidden border border-gray-100">
                                                    <div className="h-full rounded-full transition-all duration-1000" style={{ width: `${(p.unit / (persediaan.total_unit || 1)) * 100}%`, backgroundColor: i === 0 ? '#901C31' : COLORS_PIE[(i+1) % COLORS_PIE.length] }} />
                                                </div>
                                            </div>
                                        ))}
                                    </div>
                                </div>
                            </div>
                         </div>
                    </div>
                </>
            )}
        </div>
    );
}
