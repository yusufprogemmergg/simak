import React, { useState, useEffect } from 'react';
import axios from '../../lib/axios';
import { useOutletContext } from 'react-router-dom';
import { FiDownload, FiPlus, FiEdit, FiTrash2, FiX, FiSearch } from 'react-icons/fi';

export default function Kavling() {
    const { user } = useOutletContext();
    const [kavlings, setKavlings] = useState([]);
    const [projects, setProjects] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    
    // Kavling Modal states
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    
    // Project Modal states (Nested)
    const [showProjectModal, setShowProjectModal] = useState(false);
    
    // Kavling Form states
    const [form, setForm] = useState({
        id: '',
        project_id: '',
        plot_number: '',
        area: '',
        base_price: '',
        status: 'available'
    });

    // Project Form states
    const [projectForm, setProjectForm] = useState({
        name: '',
        location: '',
        notes: '',
        total_units: 10
    });
    
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    useEffect(() => {
        if (user?.role === 'owner') {
            fetchInitialData();
        } else {
            setLoading(false);
            setError("Anda tidak memiliki akses ke halaman ini.");
        }
    }, [user]);

    const fetchInitialData = async () => {
        setLoading(true);
        try {
            const [kavRes, projRes] = await Promise.all([
                axios.get('/api/master/kavling'),
                axios.get('/api/master/project')
            ]);
            setKavlings(kavRes.data);
            setProjects(projRes.data);
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal memuat data');
        } finally {
            setLoading(false);
        }
    };

    const fetchProjects = async () => {
        try {
            const res = await axios.get('/api/master/project');
            setProjects(res.data);
            return res.data;
        } catch (err) {
            console.error('Gagal memuat projects', err);
            return [];
        }
    };

    const handleOpenModal = (kavling = null) => {
        if (kavling) {
            setIsEdit(true);
            setForm({
                id: kavling.id,
                project_id: kavling.project_id,
                plot_number: kavling.plot_number,
                area: kavling.area,
                base_price: kavling.base_price,
                status: kavling.status
            });
        } else {
            setIsEdit(false);
            setForm({
                id: '',
                project_id: projects.length > 0 ? projects[0].id : '',
                plot_number: '',
                area: '',
                base_price: '',
                status: 'available'
            });
        }
        setShowModal(true);
    };

    const handleCloseModal = () => {
        setShowModal(false);
        setError(null);
    };

    const handleOpenProjectModal = () => {
        setProjectForm({
            name: '',
            location: '',
            notes: '',
            total_units: 10
        });
        setShowProjectModal(true);
    };

    const handleCloseProjectModal = () => {
        setShowProjectModal(false);
    };

    const handleChange = (e) => {
        setForm({
            ...form,
            [e.target.name]: e.target.value
        });
    };

    const handleProjectChange = (e) => {
        setProjectForm({
            ...projectForm,
            [e.target.name]: e.target.value
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);
        try {
            const payload = {
                project_id: form.project_id,
                plot_number: form.plot_number,
                area: form.area,
                base_price: form.base_price,
                status: form.status
            };

            if (isEdit) {
                await axios.post(`/api/master/kavling/${form.id}`, payload);
                setSuccess('Berhasil memperbarui kavling');
            } else {
                await axios.post('/api/master/kavling', payload);
                setSuccess('Berhasil menambahkan kavling');
            }
            handleCloseModal();
            fetchInitialData();
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal menyimpan kavling');
        }
    };

    const handleProjectSubmit = async (e) => {
        e.preventDefault();
        try {
            const res = await axios.post('/api/master/project', {
                name: projectForm.name,
                location: projectForm.location,
                notes: projectForm.notes,
                total_units: projectForm.total_units,
            });
            const newProject = res.data.data || res.data;
            
            // Refresh project list
            const updatedProjects = await fetchProjects();
            
            // Auto select new project in kavling form
            setForm(prev => ({
                ...prev,
                project_id: newProject.id
            }));
            
            handleCloseProjectModal();
            setSuccess('Project baru berhasil ditambahkan');
        } catch (err) {
            alert(err.response?.data?.message || 'Gagal menyimpan project');
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Hapus kavling ini?')) return;
        try {
            await axios.delete(`/api/master/kavling/${id}`);
            setSuccess('Kavling berhasil dihapus');
            fetchInitialData();
        } catch (err) {
            alert(err.response?.data?.message || 'Gagal menghapus');
        }
    };

    const handleExport = async () => {
        try {
            const response = await axios.get('/api/master/kavling/export-excel', {
                responseType: 'blob',
            });
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'kavlings.xlsx');
            document.body.appendChild(link);
            link.click();
            link.parentNode.removeChild(link);
        } catch (error) {
            console.error('Export failed', error);
            alert('Gagal mengexport data');
        }
    };

    const formatRupiah = (number) => {
        return new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            minimumFractionDigits: 0
        }).format(number);
    };

    const filteredKavlings = kavlings.filter(k => 
        k.plot_number.toLowerCase().includes(searchTerm.toLowerCase()) ||
        (k.project?.name || '').toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800 tracking-tight">Data Kavling</h1>
                    <p className="text-gray-500 text-sm">Kelola unit kavling dan status ketersediaan</p>
                </div>
                <div className="flex items-center gap-3">
                    <button 
                        onClick={handleExport}
                        className="flex items-center justify-center px-4 py-2.5 border border-gray-200 text-gray-600 bg-white rounded-xl hover:bg-gray-50 transition-all font-bold shadow-sm"
                    >
                        Export <FiDownload className="ml-2 w-4 h-4" />
                    </button>
                    <button 
                        onClick={() => handleOpenModal()}
                        className="flex items-center justify-center px-6 py-2.5 bg-[#901C31] text-white rounded-xl hover:bg-red-900 transition-all font-bold shadow-lg shadow-red-900/20"
                    >
                        <FiPlus className="mr-2 w-5 h-5" /> Tambah Kavling
                    </button>
                </div>
            </div>

            {success && (
                <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-xl relative shadow-sm" role="alert">
                    <span className="block sm:inline">{success}</span>
                    <button className="absolute top-0 bottom-0 right-0 px-4 py-3" onClick={() => setSuccess(null)}>
                        <FiX className="h-5 w-5 text-green-600" />
                    </button>
                </div>
            )}
            
            {error && <div className="bg-red-50 text-red-700 p-4 rounded-xl border border-red-100 shadow-sm">{error}</div>}

            <div className="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div className="p-4 border-b border-gray-50">
                    <div className="relative max-w-md">
                        <FiSearch className="absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" />
                        <input 
                            type="text" 
                            className="w-full pl-12 pr-4 py-2 bg-gray-50 border-none rounded-xl text-sm focus:ring-2 focus:ring-[#901C31]/20 transition-all"
                            placeholder="Cari blok atau project..."
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                </div>

                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-100">
                        <thead className="bg-gray-50/50">
                            <tr>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Project</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Blok / No</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Luas (m2)</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Harga Dasar</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Status</th>
                                <th className="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 bg-white">
                            {loading ? (
                                <tr><td colSpan="6" className="px-6 py-12 text-center text-gray-400 animate-pulse">Memuat data kavling...</td></tr>
                            ) : filteredKavlings.length === 0 ? (
                                <tr><td colSpan="6" className="px-6 py-12 text-center text-gray-400">Tidak ada data kavling found</td></tr>
                            ) : filteredKavlings.map((kavling) => (
                                <tr key={kavling.id} className="hover:bg-gray-50/50 transition-colors">
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className="font-bold text-gray-800">{kavling.project?.name || '-'}</span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-bold">{kavling.plot_number}</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{kavling.area} m²</td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-700 font-medium">{formatRupiah(kavling.base_price)}</td>
                                    <td className="px-6 py-4 whitespace-nowrap">
                                        <span className={`px-2.5 py-1 rounded-full text-[10px] font-bold uppercase ${
                                            kavling.status === 'available' ? 'bg-green-100 text-green-700' :
                                            kavling.status === 'sold' ? 'bg-red-100 text-red-700' :
                                            'bg-blue-100 text-blue-700'
                                        }`}>
                                            {kavling.status}
                                        </span>
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                        <div className="flex items-center justify-center space-x-1">
                                            <button onClick={() => handleOpenModal(kavling)} className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit"><FiEdit /></button>
                                            <button onClick={() => handleDelete(kavling.id)} className="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus"><FiTrash2 /></button>
                                        </div>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Kavling Modal */}
            {showModal && (
                <div className="fixed inset-0 z-50 overflow-y-auto">
                    <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div className="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" onClick={handleCloseModal}></div>
                        <div className="relative inline-block w-full max-w-md p-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-gray-100">
                            <h3 className="text-xl font-bold text-gray-900 mb-6">{isEdit ? 'Edit Kavling' : 'Kavling Baru'}</h3>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Project</label>
                                    <div className="flex gap-2">
                                        <select 
                                            name="project_id"
                                            required 
                                            className="w-full border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/20 focus:border-[#901C31]" 
                                            value={form.project_id} 
                                            onChange={handleChange}
                                        >
                                            <option value="">Pilih Project</option>
                                            {projects.map(p => (
                                                <option key={p.id} value={p.id}>{p.name}</option>
                                            ))}
                                        </select>
                                        <button 
                                            type="button"
                                            onClick={handleOpenProjectModal}
                                            className="p-3 bg-gray-50 text-gray-400 rounded-xl hover:bg-gray-100 hover:text-gray-600 transition-colors border border-gray-200 shadow-sm"
                                            title="Tambah Project Baru"
                                        >
                                            <FiPlus className="w-5 h-5" />
                                        </button>
                                    </div>
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Blok / Nomor</label>
                                    <input 
                                        name="plot_number" 
                                        required 
                                        placeholder="Ex: A-01"
                                        className="w-full border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/20 focus:border-[#901C31]" 
                                        value={form.plot_number} 
                                        onChange={handleChange} 
                                    />
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-1.5">
                                        <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Luas (m2)</label>
                                        <input 
                                            type="number"
                                            step="0.01"
                                            name="area" 
                                            required 
                                            placeholder="60"
                                            className="w-full border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/20 focus:border-[#901C31]" 
                                            value={form.area} 
                                            onChange={handleChange} 
                                        />
                                    </div>
                                    <div className="space-y-1.5">
                                        <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Status</label>
                                        <select 
                                            name="status"
                                            required 
                                            className="w-full border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/20 focus:border-[#901C31]" 
                                            value={form.status} 
                                            onChange={handleChange}
                                        >
                                            <option value="available">Available</option>
                                            <option value="sold">Sold</option>
                                            <option value="reserved">Reserved</option>
                                            <option value="active">Active</option>
                                        </select>
                                    </div>
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Harga Dasar (Rp)</label>
                                    <input 
                                        type="number"
                                        name="base_price" 
                                        required 
                                        placeholder="150000000"
                                        className="w-full border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/20 focus:border-[#901C31]" 
                                        value={form.base_price} 
                                        onChange={handleChange} 
                                    />
                                </div>
                                <div className="pt-6 flex justify-end gap-3 border-t border-gray-100 mt-6">
                                    <button type="button" onClick={handleCloseModal} className="px-6 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Batal</button>
                                    <button type="submit" className="px-8 py-2.5 bg-[#901C31] text-white rounded-xl font-bold text-sm shadow-lg shadow-red-900/20 hover:bg-red-900 transition-all">
                                        {isEdit ? 'Update Kavling' : 'Simpan Kavling'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}

            {/* Project Modal (Nested) */}
            {showProjectModal && (
                <div className="fixed inset-0 z-[60] overflow-y-auto">
                    <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div className="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" onClick={handleCloseProjectModal}></div>
                        <div className="relative inline-block w-full max-w-md p-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-gray-100">
                            <h3 className="text-xl font-bold text-gray-900 mb-6">Tambah Project Baru</h3>
                            <form onSubmit={handleProjectSubmit} className="space-y-4">
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Nama Project</label>
                                    <input 
                                        name="name" 
                                        required 
                                        placeholder="Ex: Perumahan Indah"
                                        className="w-full border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/20 focus:border-[#901C31]" 
                                        value={projectForm.name} 
                                        onChange={handleProjectChange} 
                                    />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Lokasi</label>
                                    <input 
                                        name="location" 
                                        required 
                                        placeholder="Ex: Jl. Merdeka Kav. 12"
                                        className="w-full border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/20 focus:border-[#901C31]" 
                                        value={projectForm.location} 
                                        onChange={handleProjectChange} 
                                    />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Unit</label>
                                    <input 
                                        type="number"
                                        name="total_units" 
                                        required 
                                        className="w-full border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/20 focus:border-[#901C31]" 
                                        value={projectForm.total_units} 
                                        onChange={handleProjectChange} 
                                    />
                                </div>
                                <div className="pt-6 flex justify-end gap-3 border-t border-gray-100 mt-6">
                                    <button type="button" onClick={handleCloseProjectModal} className="px-6 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Batal</button>
                                    <button type="submit" className="px-8 py-2.5 bg-[#901C31] text-white rounded-xl font-bold text-sm shadow-lg shadow-red-900/20 hover:bg-red-900 transition-all">
                                        Simpan Project
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
