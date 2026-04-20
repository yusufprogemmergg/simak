import React from 'react';
import { FiCheckCircle, FiAlertCircle } from 'react-icons/fi';

/**
 * Komponen Toast notifikasi
 * @param {{ toast: { message: string, type: 'success'|'error' } | null }} props
 */
export default function Toast({ toast }) {
    if (!toast) return null;

    return (
        <div
            className={`fixed top-6 right-6 z-999 px-6 py-3 rounded-xl shadow-2xl flex items-center gap-3 animate-in slide-in-from-right-10 duration-300 ${
                toast.type === 'error' ? 'bg-red-600 text-white' : 'bg-gray-900 text-white'
            }`}
        >
            {toast.type === 'error' ? (
                <FiAlertCircle />
            ) : (
                <FiCheckCircle className="text-green-400" />
            )}
            <span className="text-sm font-bold whitespace-pre-line">{toast.message}</span>
        </div>
    );
}
