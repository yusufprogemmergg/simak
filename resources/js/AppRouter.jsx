import React from 'react';
import { BrowserRouter as Router, Routes, Route, Navigate } from 'react-router-dom';
import AdminLayout from './layouts/AdminLayout';
import Home from './pages/Home';
import Login from './pages/Login';
import Register from './pages/Register';
import ForgotPassword from './pages/auth/ForgotPassword';
import ResetPassword from './pages/auth/ResetPassword';
import Dashboard from './pages/Dashboard';
import LicenseManagement from './pages/admin/LicenseManagement';
import Project from './pages/master/Project';
import Kavling from './pages/master/Kavling';
import CompanyProfile from './pages/master/CompanyProfile';
import SalesTransactionList from './pages/sales/SalesTransactionList';
import SalesTransactionDetail from './pages/sales/SalesTransactionDetail';
import BuyerManagement from './pages/master/BuyerManagement';
import SalesTeamManagement from './pages/master/SalesTeamManagement';

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
                <Route path="/forgot-password" element={<ForgotPassword />} />
                <Route path="/reset-password" element={<ResetPassword />} />
                <Route element={<AdminLayout />}>
                    <Route path="/dashboard" element={<ProtectedRoute><Dashboard /></ProtectedRoute>} />
                    <Route path="/admin/licenses" element={<ProtectedRoute><LicenseManagement /></ProtectedRoute>} />
                    <Route path="/projects" element={<ProtectedRoute><Project /></ProtectedRoute>} />
                    <Route path="/kavling" element={<ProtectedRoute><Kavling /></ProtectedRoute>} />
                    <Route path="/master/buyer" element={<ProtectedRoute><BuyerManagement /></ProtectedRoute>} />
                    <Route path="/master/sales" element={<ProtectedRoute><SalesTeamManagement /></ProtectedRoute>} />
                    <Route path="/penjualan" element={<ProtectedRoute><SalesTransactionList /></ProtectedRoute>} />
                    <Route path="/penjualan/detail/:id" element={<ProtectedRoute><SalesTransactionDetail /></ProtectedRoute>} />
                    <Route path="/profile-perusahaan" element={<ProtectedRoute><CompanyProfile /></ProtectedRoute>} />
                </Route>
                {/* Fallback route */}
                <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
        </Router>
    );
}
