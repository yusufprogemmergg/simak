import React from 'react';
import { Link } from 'react-router-dom';

export default function Home() {
    return (
        <div className="min-h-screen flex flex-col items-center justify-center bg-gray-100">
            <h1 className="text-4xl font-bold mb-4 text-gray-800">Welcome to Simak SPA</h1>
            <p className="text-lg text-gray-600 mb-8">This is the public home page.</p>
            <div className="space-x-4">
                <Link to="/login" className="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    Login
                </Link>
                <Link to="/dashboard" className="px-4 py-2 bg-gray-200 text-gray-800 rounded hover:bg-gray-300">
                    Go to Dashboard
                </Link>
            </div>
        </div>
    );
}
