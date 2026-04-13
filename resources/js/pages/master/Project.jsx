import React, { useState, useEffect } from 'react';
import axios from '../../lib/axios';
import AdminLayout from '../../layouts/AdminLayout';
import { FiDownload, FiPlus, FiEdit, FiTrash2, FiX } from 'react-icons/fi';
export default function Project() {
    const [user, setUser] = useState(null);
    const [projects, setProjects] = useState([]);
    const [loading, setLoading] = useState(true);
    
    // Modal states
    const [showModal, setShowModal] = useState(false);
    const [isEdit, setIsEdit] = useState(false);
    
    // Form states
    const [form, setForm] = useState({
        id: '',
        nama_project: '',
        lokasi: '',
        catatan: '',
        total_unit: 10
    });
    
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(null);

    useEffect(() => {
        const fetchInitialData = async () => {
            try {
                const userRes = await axios.get('/api/me');
                setUser(userRes.data);
                if (userRes.data.role === 'owner') {
                    fetchProjects();
                } else {
                    setLoading(false);
                    setError("Anda tidak memiliki akses ke halaman ini.");
                }
            } catch (err) {
                console.error(err);
                setLoading(false);
            }
        };
        fetchInitialData();
    }, []);

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
                nama_project: project.nama_project,
                lokasi: project.lokasi,
                catatan: project.catatan || '',
                total_unit: project.total_unit
            });
        } else {
            setIsEdit(false);
            setForm({
                id: '',
                nama_project: '',
                lokasi: '',
                catatan: '',
                total_unit: 10
            });
        }
        setShowModal(true);
    };

    const handleCloseModal = () => {
        setShowModal(false);
        setForm({
            id: '',
            nama_project: '',
            lokasi: '',
            catatan: '',
            total_unit: 10
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
                nama_project: form.nama_project,
                lokasi: form.lokasi,
                catatan: form.catatan,
                total_unit: form.total_unit
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
        if (!project.kavling || project.kavling.length === 0) {
            return {
                terjualText: `0 / ${project.total_unit} Terjual`,
                tersediaText: `0 unit tersedia`,
                percentage: 0
            };
        }
        const sold = project.kavling.filter(k => k.status === 'sold' || k.status === 'terjual').length;
        const available = project.kavling.filter(k => k.status === 'available' || k.status === 'tersedia').length;
        const total = project.total_unit;
        const percentage = total > 0 ? (sold / total) * 100 : 0;
        
        return {
            terjualText: `${sold} / ${total} Terjual`,
            tersediaText: `${available} unit tersedia`,
            percentage: percentage
        };
    };

    if (loading && projects.length === 0) return (
        <AdminLayout user={user}>
            <div className="flex justify-center p-12">
                <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-red-800"></div>
            </div>
        </AdminLayout>
    );

    if (error && projects.length === 0) return (
        <AdminLayout user={user}>
            <div className="bg-red-50 p-6 rounded-lg text-red-700">{error}</div>
        </AdminLayout>
    );

    return (
        <AdminLayout user={user}>
            <div className="mb-6 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                {/* Title removed, user didn't want it */}
                <div className="flex space-x-3 w-full sm:w-auto justify-end">
                    <button 
                        onClick={handleExport}
                        className="flex items-center px-4 py-2 border border-[#901C31] text-[#901C31] bg-white rounded-lg hover:bg-gray-50 font-medium whitespace-nowrap transition-colors"
                    >
                        Export Data <FiDownload className="w-4 h-4 ml-2" />
                    </button>
                    <button 
                        onClick={() => handleOpenModal()}
                        className="flex items-center px-4 py-2 bg-[#901C31] text-white rounded-lg hover:bg-red-900 font-medium whitespace-nowrap transition-colors"
                    >
                        <FiPlus className="w-5 h-5 mr-2" />
                        Add Project
                    </button>
                </div>
            </div>

            {success && (
                <div className="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span className="block sm:inline">{success}</span>
                    <button className="absolute top-0 bottom-0 right-0 px-4 py-3" onClick={() => setSuccess(null)}>
                        <FiX className="h-5 w-5 text-green-600 mt-1" />
                    </button>
                </div>
            )}
            
            {error && <div className="bg-red-50 text-red-700 p-4 rounded-lg mb-6">{error}</div>}

            {/* Table */}
            <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div className="overflow-x-auto">
                    <table className="min-w-full divide-y divide-gray-100">
                        <thead className="bg-gray-50">
                            <tr>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-bold text-gray-500 tracking-wider">No.</th>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-bold text-gray-500 tracking-wider">Project Name</th>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-bold text-gray-500 tracking-wider">Location</th>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-bold text-gray-500 tracking-wider">Keterangan</th>
                                <th scope="col" className="px-6 py-4 text-left text-xs font-bold text-gray-500 tracking-wider">Status Kavling</th>
                                <th scope="col" className="px-6 py-4 text-center text-xs font-bold text-gray-500 tracking-wider">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="bg-white divide-y divide-gray-100">
                            {projects.map((project, index) => {
                                const status = getKavlingStatus(project);
                                return (
                                    <tr key={project.id} className="hover:bg-gray-50 transition-colors">
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{index + 1}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-800">{project.nama_project}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{project.lokasi}</td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-600">{project.catatan || '-'}</td>
                                        <td className="px-6 py-4 min-w-[200px]">
                                            <div className="flex justify-between text-xs mb-1">
                                                <span className="text-gray-700 font-medium">{status.terjualText}</span>
                                                <span className="text-gray-500">{status.tersediaText}</span>
                                            </div>
                                            <div className="w-full bg-gray-200 rounded-full h-2">
                                                <div 
                                                    className="bg-[#901C31] h-2 rounded-full" 
                                                    style={{ width: `${status.percentage}%` }}
                                                ></div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <div className="flex justify-center space-x-2">
                                                <button onClick={() => handleOpenModal(project)} className="flex items-center text-gray-700 bg-white border border-gray-200 px-3 py-1.5 rounded-lg shadow-sm hover:bg-gray-50 transition-colors font-medium">
                                                    <FiEdit className="mr-1.5" /> Edit
                                                </button>
                                                <button onClick={() => handleDelete(project.id)} className="flex items-center text-[#901C31] bg-white border border-gray-200 px-3 py-1.5 rounded-lg shadow-sm hover:bg-gray-50 transition-colors font-medium">
                                                    <FiTrash2 className="mr-1.5" /> Delete
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                );
                            })}
                            {projects.length === 0 && (
                                <tr>
                                    <td colSpan="6" className="px-6 py-8 text-center text-gray-500">Belum ada project. Mulai dengan membuat project baru.</td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>

            {/* Modal Form with glassmorphism/dark blur background */}
            {showModal && (
                <div className="fixed inset-0 z-50 overflow-y-auto">
                    <div className="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
                        {/* THE REQUESTED TRANSPARENT BLUR DARK BACKGROUND */}
                        <div 
                            className="fixed inset-0 transition-opacity bg-black/60 backdrop-blur-sm" 
                            onClick={handleCloseModal}
                        ></div>
                        
                        <div className="relative inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-2xl border border-gray-100">
                            <h3 className="text-xl font-bold leading-6 text-gray-900 mb-6 border-b pb-4">
                                {isEdit ? 'Edit Project' : 'Add New Project'}
                            </h3>
                            <form onSubmit={handleSubmit}>
                                <div className="space-y-4">
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">Project Name <span className="text-red-500">*</span></label>
                                        <input 
                                            type="text" 
                                            name="nama_project"
                                            required 
                                            className="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-[#901C31] focus:border-[#901C31] sm:text-sm transition-colors" 
                                            value={form.nama_project} 
                                            onChange={handleChange}
                                            placeholder="Ex: Perumahan Indah"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">Location <span className="text-red-500">*</span></label>
                                        <input 
                                            type="text" 
                                            name="lokasi"
                                            required 
                                            className="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-[#901C31] focus:border-[#901C31] sm:text-sm transition-colors" 
                                            value={form.lokasi} 
                                            onChange={handleChange}
                                            placeholder="Ex: Jl. Merdeka Kav. 12"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">Total Unit (Kavling) <span className="text-red-500">*</span></label>
                                        <input 
                                            type="number" 
                                            name="total_unit"
                                            min="1"
                                            required 
                                            className="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-[#901C31] focus:border-[#901C31] sm:text-sm transition-colors" 
                                            value={form.total_unit} 
                                            onChange={handleChange}
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-sm font-medium text-gray-700 mb-1">Keterangan</label>
                                        <textarea 
                                            name="catatan"
                                            className="block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:ring-[#901C31] focus:border-[#901C31] sm:text-sm transition-colors" 
                                            value={form.catatan} 
                                            onChange={handleChange}
                                            rows="3"
                                            placeholder="Opsional"
                                        ></textarea>
                                    </div>
                                </div>
                                <div className="mt-8 flex justify-end space-x-3 pt-4 border-t">
                                    <button 
                                        type="button" 
                                        onClick={handleCloseModal} 
                                        className="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#901C31]"
                                    >
                                        Cancel
                                    </button>
                                    <button 
                                        type="submit" 
                                        className="px-4 py-2 text-sm font-medium text-white bg-[#901C31] rounded-lg shadow-sm hover:bg-red-900 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#901C31]"
                                    >
                                        {isEdit ? 'Update Project' : 'Save Project'}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
