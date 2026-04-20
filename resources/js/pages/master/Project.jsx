import React, { useState, useEffect } from 'react';
import axios from '../../lib/axios';
import { useOutletContext } from 'react-router-dom';
import { FiDownload, FiPlus, FiEdit, FiTrash2, FiX, FiSearch } from 'react-icons/fi';

export default function Project() {
    const { user } = useOutletContext();
    const [projects, setProjects] = useState([]);
    const [loading, setLoading] = useState(true);
    const [searchTerm, setSearchTerm] = useState('');
    
    // Modal states
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    
    // Form states
    const [form, setForm] = useState({
        id: '',
        name: '',
        location: '',
        notes: '',
        total_units: 10
    });
    
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    useEffect(() => {
        if (user?.role === 'owner') {
            fetchProjects();
        } else {
            setLoading(false);
            setError("Anda tidak memiliki akses ke halaman ini.");
        }
    }, [user]);

    const fetchProjects = async () => {
        setLoading(true);
        try {
            const res = await axios.get('/api/master/project');
            setProjects(res.data);
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal memuat projects');
        } finally {
            setLoading(false);
        }
    };

    const handleOpenModal = (project = null) => {
        if (project) {
            setIsEdit(true);
            setForm({
                id: project.id,
                name: project.name,
                location: project.location,
                notes: project.notes || '',
                total_units: project.total_units
            });
        } else {
            setIsEdit(false);
            setForm({
                id: '',
                name: '',
                location: '',
                notes: '',
                total_units: 10
            });
        }
        setShowModal(true);
    };

    const handleCloseModal = () => {
        setShowModal(false);
        setForm({
            id: '',
            name: '',
            location: '',
            notes: '',
            total_units: 10
        });
        setError(null);
    };

    const handleChange = (e) => {
        setForm({
            ...form,
            [e.target.name]: e.target.value
        });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setError(null);
        try {
            const payload = {
                name: form.name,
                location: form.location,
                notes: form.notes,
                total_units: form.total_units
            };

            if (isEdit) {
                await axios.post(`/api/master/project/${form.id}`, payload);
                setSuccess('Berhasil memperbarui project');
            } else {
                await axios.post('/api/master/project', payload);
                setSuccess('Berhasil menambahkan project');
            }
            handleCloseModal();
            fetchProjects();
        } catch (err) {
            setError(err.response?.data?.message || 'Gagal menyimpan project');
        }
    };

    const handleDelete = async (id) => {
        if (!confirm('Hapus project ini? Data terkait mungkin ikut terhapus.')) return;
        try {
            await axios.delete(`/api/master/project/${id}`);
            setSuccess('Project berhasil dihapus');
            fetchProjects();
        } catch (err) {
            alert(err.response?.data?.message || 'Gagal menghapus');
        }
    };

    const handleExport = async () => {
        try {
            const response = await axios.get('/api/master/project/export-excel', {
                responseType: 'blob',
            });
            const url = window.URL.createObjectURL(new Blob([response.data]));
            const link = document.createElement('a');
            link.href = url;
            link.setAttribute('download', 'projects.xlsx');
            document.body.appendChild(link);
            link.click();
            link.parentNode.removeChild(link);
        } catch (error) {
            console.error('Export failed', error);
            alert('Gagal mengexport data');
        }
    };

    const getKavlingStatus = (project) => {
        if (!project.plots || project.plots.length === 0) {
            return {
                terjualText: `0 / ${project.total_units} Terjual`,
                tersediaText: `0 unit tersedia`,
                percentage: 0
            };
        }
        const sold = project.plots.filter(k => k.status === 'sold').length;
        const available = project.plots.filter(k => k.status === 'available').length;
        const total = project.total_units;
        const percentage = total > 0 ? (sold / total) * 100 : 0;
        
        return {
            terjualText: `${sold} / ${total} Terjual`,
            tersediaText: `${available} unit tersedia`,
            percentage: percentage
        };
    };

    const filteredProjects = projects.filter(p => 
        p.name.toLowerCase().includes(searchTerm.toLowerCase()) ||
        p.location.toLowerCase().includes(searchTerm.toLowerCase())
    );

    return (
        <div className="space-y-6">
            <div className="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 className="text-2xl font-bold text-gray-800 tracking-tight">Data Project</h1>
                    <p className="text-gray-500 text-sm">Kelola master data project perumahan</p>
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
                        <FiPlus className="mr-2 w-5 h-5" /> Tambah Project
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
                            placeholder="Cari nama project atau lokasi..."
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
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Lokasi</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Keterangan</th>
                                <th className="px-6 py-4 text-left text-[10px] font-bold text-gray-400 uppercase tracking-widest">Progress Kavling</th>
                                <th className="px-6 py-4 text-center text-[10px] font-bold text-gray-400 uppercase tracking-widest">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-gray-100 bg-white">
                            {loading ? (
                                <tr><td colSpan="5" className="px-6 py-12 text-center text-gray-400 animate-pulse">Memuat data project...</td></tr>
                            ) : filteredProjects.length === 0 ? (
                                <tr><td colSpan="5" className="px-6 py-12 text-center text-gray-400">Tidak ada project ditemukan</td></tr>
                            ) : filteredProjects.map((project, index) => {
                                const status = getKavlingStatus(project);
                                return (
                                    <tr key={project.id} className="hover:bg-gray-50/50 transition-colors">
                                        <td className="px-6 py-4 whitespace-nowrap">
                                            <span className="font-bold text-gray-800">{project.name}</span>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{project.location}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 italic">{project.notes || '-'}</td>
                                        <td className="px-6 py-4 min-w-[200px]">
                                            <div className="flex justify-between text-[10px] font-bold uppercase tracking-wider mb-1.5">
                                                <span className="text-[#901C31]">{status.terjualText}</span>
                                                <span className="text-gray-400">{status.tersediaText}</span>
                                            </div>
                                            <div className="w-full bg-gray-100 rounded-full h-1.5 overflow-hidden">
                                                <div 
                                                    className="bg-[#901C31] h-full rounded-full transition-all duration-500" 
                                                    style={{ width: `${status.percentage}%` }}
                                                ></div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-center text-sm font-medium">
                                            <div className="flex items-center justify-center space-x-1">
                                                <button onClick={() => handleOpenModal(project)} className="p-2 text-gray-600 hover:bg-gray-100 rounded-lg transition-colors" title="Edit"><FiEdit /></button>
                                                <button onClick={() => handleDelete(project.id)} className="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" title="Hapus"><FiTrash2 /></button>
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Modal */}
            {showModal && (
                <div className="fixed inset-0 z-50 overflow-y-auto">
                    <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        <div className="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" onClick={handleCloseModal}></div>
                        <div className="relative inline-block w-full max-w-md p-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-gray-100">
                            <h3 className="text-xl font-bold text-gray-900 mb-6">{isEdit ? 'Edit Project' : 'Project Baru'}</h3>
                            <form onSubmit={handleSubmit} className="space-y-4">
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Nama Project</label>
                                    <input 
                                        name="name" 
                                        required 
                                        placeholder="Ex: Perumahan Indah"
                                        className="w-full border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/20 focus:border-[#901C31]" 
                                        value={form.name} 
                                        onChange={handleChange} 
                                    />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Lokasi</label>
                                    <input 
                                        name="location" 
                                        required 
                                        placeholder="Ex: Jl. Merdeka Kav. 12"
                                        className="w-full border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/20 focus:border-[#901C31]" 
                                        value={form.location} 
                                        onChange={handleChange} 
                                    />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Total Unit (Kavling Target)</label>
                                    <input 
                                        type="number"
                                        name="total_units" 
                                        required 
                                        className="w-full border-gray-200 rounded-xl py-3 text-sm focus:ring-[#901C31]/20 focus:border-[#901C31]" 
                                        value={form.total_units} 
                                        onChange={handleChange} 
                                    />
                                </div>
                                <div className="space-y-1.5">
                                    <label className="text-xs font-bold text-gray-400 uppercase tracking-widest">Keterangan / Catatan</label>
                                    <textarea 
                                        name="notes" 
                                        className="w-full border-gray-200 rounded-xl py-3 text-sm min-h-[100px] focus:ring-[#901C31]/20 focus:border-[#901C31]" 
                                        value={form.notes} 
                                        onChange={handleChange} 
                                        placeholder="Catatan tambahan (opsional)"
                                    ></textarea>
                                </div>
                                <div className="pt-6 flex justify-end gap-3 border-t border-gray-100 mt-6">
                                    <button type="button" onClick={handleCloseModal} className="px-6 py-2 text-sm font-bold text-gray-500 hover:text-gray-700">Batal</button>
                                    <button type="submit" className="px-8 py-2.5 bg-[#901C31] text-white rounded-xl font-bold text-sm shadow-lg shadow-red-900/20 hover:bg-red-900 transition-all">
                                        {isEdit ? 'Update Project' : 'Simpan Project'}
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
