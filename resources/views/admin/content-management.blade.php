@extends('admin.layout')

@section('title', 'Manajemen Konten')
@section('page-title', 'Manajemen Konten')
@section('page-description', 'Kelola konten website dan aplikasi')

@section('content')
    <div class="flex items-center justify-center min-h-[60vh]">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-32 w-32 bg-gradient-to-r from-green-400 to-blue-500 rounded-full mb-8">
                <i class="fas fa-file-alt text-white text-5xl"></i>
            </div>
            
            <h2 class="text-3xl font-bold text-gray-800 mb-4">Sistem Dalam Pengembangan</h2>
            <p class="text-lg text-gray-600 mb-8 max-w-md mx-auto">
                Halaman Manajemen Konten sedang dalam tahap pengembangan. 
                Fitur akan segera tersedia untuk mengelola konten website, artikel, dan media.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-2xl mx-auto mb-8">
                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <i class="fas fa-newspaper text-green-600 text-2xl mb-3"></i>
                    <h3 class="font-semibold text-gray-800 mb-2">Artikel & Blog</h3>
                    <p class="text-sm text-gray-600">Kelola artikel, blog post, dan konten editorial</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <i class="fas fa-images text-green-600 text-2xl mb-3"></i>
                    <h3 class="font-semibold text-gray-800 mb-2">Media Library</h3>
                    <p class="text-sm text-gray-600">Manajemen gambar, video, dan file multimedia</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <i class="fas fa-globe text-green-600 text-2xl mb-3"></i>
                    <h3 class="font-semibold text-gray-800 mb-2">Website Content</h3>
                    <p class="text-sm text-gray-600">Kelola konten halaman website dan landing page</p>
                </div>
                
                <div class="bg-white p-6 rounded-lg shadow-sm border">
                    <i class="fas fa-bullhorn text-green-600 text-2xl mb-3"></i>
                    <h3 class="font-semibold text-gray-800 mb-2">Announcement</h3>
                    <p class="text-sm text-gray-600">Buat dan kelola pengumuman untuk pengguna</p>
                </div>
            </div>
            
            <div class="text-sm text-gray-500">
                <p>Estimasi pengembangan: <span class="font-semibold text-green-600">Q2 2025</span></p>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
// Check authentication when page loads
document.addEventListener('DOMContentLoaded', function() {
    const token = localStorage.getItem('admin_token');
    const user = localStorage.getItem('admin_user');
    
    if (!token || !user) {
        console.error('No authentication found');
        window.location.href = '/admin/login';
        return;
    }
});
</script>
@endpush