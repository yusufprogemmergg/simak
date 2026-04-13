import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import Home from './pages/Home';
import Login from './pages/Login';
import Register from './pages/Register';
import Dashboard from './pages/Dashboard';
import LicenseManagement from './pages/admin/LicenseManagement';
import Project from './pages/master/Project';

const ProtectedRoute = ({ children }) => {
    const isLoggedIn = localStorage.getItem('isLoggedIn') === 'true';
    if (!isLoggedIn) {
        return <Navigate to="/login" replace />;
    }
    return children;
};

export default function AppRouter() {
    return (
        <Router>
            <Routes>
                <Route path="/" element={<Home />} />
                <Route path="/login" element={<Login />} />
                <Route path="/register" element={<Register />} />
                <Route path="/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />
                <Route path="/admin/licenses" element={<ProtectedRoute><LicenseManagement /></ProtectedRoute>} />
                <Route path="/projects" element={<ProtectedRoute><Project /></ProtectedRoute>} />
                {/* Fallback route */}
                <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
        </Router>
    );
}
