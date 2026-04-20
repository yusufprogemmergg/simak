import { useState } from 'react';

/**
 * Custom hook untuk mengelola toast notifikasi
 * @returns {{ toast: object|null, showToast: function }}
 */
export function useToast() {
    const [toast, setToast] = useState(null);

    const showToast = (message, type = 'success') => {
        setToast({ message, type });
        setTimeout(() => setToast(null), 3000);
    };

    return { toast, showToast };
}
