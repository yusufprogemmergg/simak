import React from 'react';
import { FiInfo } from 'react-icons/fi';

/**
 * Komponen Modal Konfirmasi reusable
 * @param {{
 *   show: boolean,
 *   title: string,
 *   message: string,
 *   onConfirm: function,
 *   onClose: function,
 *   confirmLabel?: string,
 *   confirmClass?: string,
 *   icon?: React.ReactNode,
 * }} props
 */
export default function ConfirmModal({
    show,
    title,
    message,
    onConfirm,
    onClose,
    confirmLabel = 'Ya, Lanjutkan',
    confirmClass = 'bg-[#901C31] text-white shadow-lg shadow-red-900/20',
    icon = null,
}) {
    if (!show) return null;

    return (
        <div className="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
            <div className="bg-white rounded-2xl p-8 max-w-sm w-full shadow-2xl transform animate-in zoom-in-95 duration-200">
                {icon ? (
                    <div className="mb-4">{icon}</div>
                ) : (
                    <FiInfo className="w-12 h-12 text-[#901C31] mb-4" />
                )}
                <h3 className="text-xl font-bold mb-2">{title}</h3>
                <p className="text-gray-500 text-sm mb-8">{message}</p>
                <div className="flex gap-4">
                    <button
                        onClick={onClose}
                        className="flex-1 py-3 text-sm font-bold text-gray-400 hover:text-gray-600 transition-colors"
                    >
                        Batal
                    </button>
                    <button
                        onClick={() => {
                            onConfirm();
                            onClose();
                        }}
                        className={`flex-1 py-3 rounded-xl font-bold text-sm ${confirmClass}`}
                    >
                        {confirmLabel}
                    </button>
                </div>
            </div>
        </div>
    );
}
