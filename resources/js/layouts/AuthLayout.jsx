import React from 'react';
import { FiBox } from 'react-icons/fi';

export default function AuthLayout({ children, title, subtitle }) {
    return (
        <div className="min-h-screen bg-gray-50 flex">
            {/* Left Box - Image/Brand */}
            <div className="hidden lg:flex lg:w-1/2 relative bg-[#901C31] justify-center items-center overflow-hidden">
                <div className="absolute inset-0 opacity-20 bg-[url('https://images.unsplash.com/photo-1497366216548-37526070297c?q=80&w=2069&auto=format&fit=crop')] bg-cover bg-center mix-blend-overlay"></div>
                <div className="absolute inset-0 bg-gradient-to-br from-[#901C31]/90 to-[#4a0e19]/90 mix-blend-multiply"></div>
                <div className="relative z-10 px-12 text-white">
                    <h1 className="text-5xl font-extrabold tracking-tight mb-6">Simak App</h1>
                    <p className="text-xl text-rose-100 max-w-lg leading-relaxed">
                        Manajemen pemasaran properti cerdas. Terintegrasi, transparan, dan dapat diandalkan untuk membangun masa depan bisnis Anda.
                    </p>
                </div>
            </div>

            {/* Right Box - Form */}
            <div className="flex-1 flex flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:w-1/2 lg:px-20 xl:px-24">
                <div className="mx-auto w-full max-w-sm lg:w-96">
                    <div>
                        <div className="w-12 h-12 bg-[#901C31] rounded-lg flex items-center justify-center text-white mb-8 shadow-lg">
                            <FiBox className="w-8 h-8" />
                        </div>
                        <h2 className="text-3xl font-bold tracking-tight text-gray-900">
                            {title}
                        </h2>
                        {subtitle && (
                            <p className="mt-2 text-sm text-gray-600">
                                {subtitle}
                            </p>
                        )}
                    </div>

                    <div className="mt-8">
                        {children}
                    </div>
                </div>
            </div>
        </div>
    );
}
