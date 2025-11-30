@extends('institution_admin.layout')

@section('title', 'Pengaturan')
@section('page-title', 'Pengaturan')
@section('page-description', 'Kelola pengaturan akun dan institusi Anda')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <svg class="w-3 h-3 text-gray-400 mx-1" fill="none" viewBox="0 0 6 10" aria-hidden="true">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 9 4-4-4-4"/>
        </svg>
        <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Pengaturan</span>
    </div>
</li>
@endsection

@section('content')
<div id="pageLoading" class="flex flex-col items-center justify-center py-20">
    <div class="w-16 h-16 border-4 border-green-500 border-t-transparent rounded-full animate-spin"></div>
    <p class="mt-4 text-gray-600">Memuat pengaturan...</p>
</div>

<div id="mainContent" class="hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm overflow-hidden">
                <nav class="divide-y">
                    <button onclick="showSection('profile')" 
                        class="settings-nav w-full px-6 py-4 text-left hover:bg-gray-50 flex items-center active" 
                        data-section="profile">
                        <i class="fas fa-user w-5 text-gray-500"></i>
                        <span class="ml-3 font-medium">Profil Saya</span>
                    </button>
                    <button onclick="showSection('institution')" 
                        class="settings-nav w-full px-6 py-4 text-left hover:bg-gray-50 flex items-center" 
                        data-section="institution">
                        <i class="fas fa-building w-5 text-gray-500"></i>
                        <span class="ml-3 font-medium">Info Institusi</span>
                    </button>
                    <button onclick="showSection('notification')" 
                        class="settings-nav w-full px-6 py-4 text-left hover:bg-gray-50 flex items-center" 
                        data-section="notification">
                        <i class="fas fa-bell w-5 text-gray-500"></i>
                        <span class="ml-3 font-medium">Notifikasi</span>
                    </button>
                    <button onclick="showSection('security')" 
                        class="settings-nav w-full px-6 py-4 text-left hover:bg-gray-50 flex items-center" 
                        data-section="security">
                        <i class="fas fa-lock w-5 text-gray-500"></i>
                        <span class="ml-3 font-medium">Keamanan</span>
                    </button>
                </nav>
            </div>
        </div>
        
        <!-- Content -->
        <div class="lg:col-span-2">
            <!-- Profile Section -->
            <div id="section-profile" class="settings-section">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Profil Saya</h3>
                    
                    <form id="profileForm">
                        <div class="flex items-center mb-6">
                            <div class="relative">
                                <div id="avatarContainer" class="w-20 h-20 bg-gradient-to-r from-green-400 to-emerald-500 rounded-full flex items-center justify-center text-white text-2xl font-bold overflow-hidden">
                                    <span id="avatarInitial">A</span>
                                    <img id="avatarImage" src="" alt="Avatar" class="w-full h-full object-cover hidden">
                                </div>
                                <input type="file" id="avatarInput" accept="image/jpeg,image/png,image/gif" class="hidden">
                            </div>
                            <div class="ml-4">
                                <button type="button" onclick="document.getElementById('avatarInput').click()" class="text-sm text-green-600 hover:text-green-700 font-medium">
                                    Ubah Foto
                                </button>
                                <button type="button" id="removeAvatarBtn" onclick="removeAvatar()" class="text-sm text-red-600 hover:text-red-700 font-medium ml-3 hidden">
                                    Hapus
                                </button>
                                <p class="text-xs text-gray-500 mt-1">JPG, PNG maksimal 2MB</p>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                                <input type="text" id="profileName" 
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" id="profileEmail" disabled
                                    class="w-full px-4 py-2 border rounded-lg bg-gray-50 text-gray-500">
                                <p class="text-xs text-gray-500 mt-1">Email tidak dapat diubah</p>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                            <input type="tel" id="profilePhone" placeholder="+62xxx"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        
                        <div class="flex justify-end mt-6">
                            <button type="submit" id="profileSubmitBtn" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Institution Section -->
            <div id="section-institution" class="settings-section hidden">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Info Institusi</h3>
                    
                    <form id="institutionForm">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Institusi</label>
                            <input type="text" id="instName" disabled
                                class="w-full px-4 py-2 border rounded-lg bg-gray-50 text-gray-500">
                            <p class="text-xs text-gray-500 mt-1">Hubungi admin untuk mengubah nama institusi</p>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <textarea id="instDescription" rows="3" placeholder="Deskripsi singkat tentang institusi..."
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <textarea id="instAddress" rows="2" placeholder="Alamat lengkap institusi..."
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"></textarea>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Telepon Institusi</label>
                                <input type="tel" id="instPhone" placeholder="+62xxx"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email Institusi</label>
                                <input type="email" id="instEmail" placeholder="info@company.com"
                                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Perusahaan</label>
                            <input type="text" id="instCompanyType" placeholder="Contoh: PT, CV, Startup, dll"
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        
                        <div class="flex justify-end mt-6">
                            <button type="submit" id="institutionSubmitBtn" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                                <i class="fas fa-save mr-2"></i>
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Notification Section -->
            <div id="section-notification" class="settings-section hidden">
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Pengaturan Notifikasi</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between py-4 border-b">
                            <div>
                                <p class="font-medium text-gray-800">Notifikasi Email</p>
                                <p class="text-sm text-gray-500">Terima pemberitahuan melalui email</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="notifEmail" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between py-4 border-b">
                            <div>
                                <p class="font-medium text-gray-800">Notifikasi Push</p>
                                <p class="text-sm text-gray-500">Terima notifikasi push di browser</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="notifPush" class="sr-only peer">
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between py-4 border-b">
                            <div>
                                <p class="font-medium text-gray-800">Ringkasan Harian</p>
                                <p class="text-sm text-gray-500">Terima ringkasan aktivitas setiap hari</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="notifDaily" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                        
                        <div class="flex items-center justify-between py-4">
                            <div>
                                <p class="font-medium text-gray-800">Aktivitas Anggota</p>
                                <p class="text-sm text-gray-500">Pemberitahuan saat anggota baru bergabung</p>
                            </div>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="checkbox" id="notifMember" class="sr-only peer" checked>
                                <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-green-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                    </div>
                    
                    <div class="flex justify-end mt-6">
                        <button onclick="saveNotificationSettings()" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                            <i class="fas fa-save mr-2"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Security Section -->
            <div id="section-security" class="settings-section hidden">
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Ubah Password</h3>
                    
                    <form id="passwordForm">
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password Saat Ini</label>
                            <input type="password" id="currentPassword" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                            <input type="password" id="newPassword" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                            <p class="text-xs text-gray-500 mt-1">Minimal 8 karakter dengan kombinasi huruf dan angka</p>
                        </div>
                        
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password Baru</label>
                            <input type="password" id="confirmPassword" required
                                class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500">
                        </div>
                        
                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg font-medium transition-colors">
                            <i class="fas fa-lock mr-2"></i>
                            Ubah Password
                        </button>
                    </form>
                </div>
                
                <!-- Active Sessions -->
                <div class="bg-white rounded-xl shadow-sm p-6">
                    <h3 class="text-lg font-semibold text-gray-800 mb-6">Sesi Aktif</h3>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-4 bg-green-50 border border-green-200 rounded-lg">
                            <div class="flex items-center">
                                <i class="fas fa-laptop text-green-600 text-xl mr-4"></i>
                                <div>
                                    <p class="font-medium text-gray-800">Perangkat Ini</p>
                                    <p class="text-sm text-gray-500">Windows • Chrome • Terakhir aktif: Sekarang</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full">Aktif</span>
                        </div>
                    </div>
                    
                    <button onclick="logoutAllDevices()" class="mt-4 text-red-600 hover:text-red-700 font-medium text-sm">
                        <i class="fas fa-sign-out-alt mr-2"></i>
                        Keluar dari semua perangkat
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const API_BASE = '/api';
let currentUserData = null;
let currentInstitutionData = null;
let selectedAvatarBase64 = null;

function getToken() {
    return localStorage.getItem('admin_token');
}

document.addEventListener('DOMContentLoaded', async function() {
    const token = getToken();
    if (!token) {
        window.location.href = '/login';
        return;
    }
    
    await loadAllData();
    
    // Setup form handlers
    document.getElementById('profileForm').addEventListener('submit', handleProfileUpdate);
    document.getElementById('institutionForm').addEventListener('submit', handleInstitutionUpdate);
    document.getElementById('passwordForm').addEventListener('submit', handlePasswordChange);
    
    // Setup avatar input handler
    document.getElementById('avatarInput').addEventListener('change', handleAvatarSelect);
});

async function loadAllData() {
    try {
        document.getElementById('pageLoading').classList.remove('hidden');
        document.getElementById('mainContent').classList.add('hidden');
        
        // Load profile and institution data in parallel
        await Promise.all([
            loadProfileData(),
            loadInstitutionData()
        ]);
        
        document.getElementById('pageLoading').classList.add('hidden');
        document.getElementById('mainContent').classList.remove('hidden');
    } catch (error) {
        console.error('Failed to load data:', error);
        showAlert('error', 'Error', 'Gagal memuat data');
        document.getElementById('pageLoading').classList.add('hidden');
        document.getElementById('mainContent').classList.remove('hidden');
    }
}

async function loadProfileData() {
    try {
        const response = await fetch(`${API_BASE}/profile`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Failed to fetch profile');
        
        const result = await response.json();
        if (result.success) {
            currentUserData = result.data;
            populateProfileForm(result.data);
        }
    } catch (error) {
        console.error('Failed to load profile:', error);
    }
}

async function loadInstitutionData() {
    try {
        const response = await fetch(`${API_BASE}/institution/my-institution`, {
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) {
            if (response.status === 404) {
                console.log('No institution assigned');
                return;
            }
            throw new Error('Failed to fetch institution');
        }
        
        const result = await response.json();
        if (result.success) {
            currentInstitutionData = result.data;
            populateInstitutionForm(result.data);
        }
    } catch (error) {
        console.error('Failed to load institution:', error);
    }
}

function populateProfileForm(user) {
    document.getElementById('profileName').value = user.name || '';
    document.getElementById('profileEmail').value = user.email || '';
    document.getElementById('profilePhone').value = user.phone_number || '';
    
    // Handle avatar
    const avatarInitial = document.getElementById('avatarInitial');
    const avatarImage = document.getElementById('avatarImage');
    const removeAvatarBtn = document.getElementById('removeAvatarBtn');
    
    avatarInitial.textContent = (user.name || 'A').charAt(0).toUpperCase();
    
    if (user.avatar_url) {
        avatarImage.src = user.avatar_url;
        avatarImage.classList.remove('hidden');
        avatarInitial.classList.add('hidden');
        removeAvatarBtn.classList.remove('hidden');
    } else {
        avatarImage.classList.add('hidden');
        avatarInitial.classList.remove('hidden');
        removeAvatarBtn.classList.add('hidden');
    }
}

function populateInstitutionForm(institution) {
    document.getElementById('instName').value = institution.name || '';
    document.getElementById('instDescription').value = institution.description || '';
    document.getElementById('instAddress').value = institution.address || '';
    document.getElementById('instPhone').value = institution.phone_number || '';
    document.getElementById('instEmail').value = institution.company_email || '';
    document.getElementById('instCompanyType').value = institution.company_type || '';
}

function handleAvatarSelect(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    // Validate file size (max 2MB)
    if (file.size > 2 * 1024 * 1024) {
        showAlert('error', 'Error', 'Ukuran file maksimal 2MB');
        return;
    }
    
    // Validate file type
    if (!['image/jpeg', 'image/png', 'image/gif'].includes(file.type)) {
        showAlert('error', 'Error', 'Format file harus JPG, PNG, atau GIF');
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        selectedAvatarBase64 = e.target.result;
        
        // Preview
        const avatarImage = document.getElementById('avatarImage');
        const avatarInitial = document.getElementById('avatarInitial');
        const removeAvatarBtn = document.getElementById('removeAvatarBtn');
        
        avatarImage.src = selectedAvatarBase64;
        avatarImage.classList.remove('hidden');
        avatarInitial.classList.add('hidden');
        removeAvatarBtn.classList.remove('hidden');
    };
    reader.readAsDataURL(file);
}

async function removeAvatar() {
    if (!confirm('Hapus foto profil?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/profile/picture`, {
            method: 'DELETE',
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            }
        });
        
        if (!response.ok) throw new Error('Failed to delete avatar');
        
        const result = await response.json();
        if (result.success) {
            selectedAvatarBase64 = null;
            
            const avatarImage = document.getElementById('avatarImage');
            const avatarInitial = document.getElementById('avatarInitial');
            const removeAvatarBtn = document.getElementById('removeAvatarBtn');
            
            avatarImage.src = '';
            avatarImage.classList.add('hidden');
            avatarInitial.classList.remove('hidden');
            removeAvatarBtn.classList.add('hidden');
            
            showAlert('success', 'Berhasil', 'Foto profil berhasil dihapus');
        }
    } catch (error) {
        console.error('Failed to remove avatar:', error);
        showAlert('error', 'Error', 'Gagal menghapus foto profil');
    }
}

function showSection(sectionName) {
    // Hide all sections
    document.querySelectorAll('.settings-section').forEach(s => s.classList.add('hidden'));
    
    // Show selected section
    document.getElementById('section-' + sectionName).classList.remove('hidden');
    
    // Update nav active state
    document.querySelectorAll('.settings-nav').forEach(nav => {
        nav.classList.remove('active', 'bg-green-50', 'text-green-600');
        if (nav.dataset.section === sectionName) {
            nav.classList.add('active', 'bg-green-50', 'text-green-600');
        }
    });
}

async function handleProfileUpdate(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('profileSubmitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    
    try {
        const payload = {
            name: document.getElementById('profileName').value,
            phone_number: document.getElementById('profilePhone').value || null
        };
        
        // Add avatar if selected
        if (selectedAvatarBase64) {
            payload.avatar_url = selectedAvatarBase64;
        }
        
        const response = await fetch(`${API_BASE}/profile`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || 'Gagal memperbarui profil');
        }
        
        if (result.success) {
            selectedAvatarBase64 = null;
            
            // Update localStorage
            const storedUser = localStorage.getItem('admin_user');
            if (storedUser) {
                const user = JSON.parse(storedUser);
                user.name = payload.name;
                user.phone_number = payload.phone_number;
                if (result.data && result.data.avatar_url) {
                    user.avatar_url = result.data.avatar_url;
                }
                localStorage.setItem('admin_user', JSON.stringify(user));
            }
            
            showAlert('success', 'Berhasil', 'Profil berhasil diperbarui');
            await loadProfileData();
        }
    } catch (error) {
        console.error('Failed to update profile:', error);
        showAlert('error', 'Error', error.message || 'Gagal memperbarui profil');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

async function handleInstitutionUpdate(e) {
    e.preventDefault();
    
    const submitBtn = document.getElementById('institutionSubmitBtn');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
    
    try {
        const payload = {
            description: document.getElementById('instDescription').value || null,
            address: document.getElementById('instAddress').value || null,
            phone_number: document.getElementById('instPhone').value || null,
            company_email: document.getElementById('instEmail').value || null,
            company_type: document.getElementById('instCompanyType').value || null
        };
        
        const response = await fetch(`${API_BASE}/institution/my-institution`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || 'Gagal memperbarui info institusi');
        }
        
        if (result.success) {
            showAlert('success', 'Berhasil', 'Info institusi berhasil diperbarui');
            await loadInstitutionData();
        }
    } catch (error) {
        console.error('Failed to update institution:', error);
        showAlert('error', 'Error', error.message || 'Gagal memperbarui info institusi');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

async function handlePasswordChange(e) {
    e.preventDefault();
    
    const currentPassword = document.getElementById('currentPassword').value;
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;
    
    if (newPassword !== confirmPassword) {
        showAlert('error', 'Error', 'Konfirmasi password tidak cocok');
        return;
    }
    
    if (newPassword.length < 8) {
        showAlert('error', 'Error', 'Password minimal 8 karakter');
        return;
    }
    
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengubah...';
    
    try {
        const response = await fetch(`${API_BASE}/profile`, {
            method: 'PUT',
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                current_password: currentPassword,
                password: newPassword,
                password_confirmation: confirmPassword
            })
        });
        
        const result = await response.json();
        
        if (!response.ok) {
            throw new Error(result.message || 'Gagal mengubah password');
        }
        
        if (result.success) {
            showAlert('success', 'Berhasil', 'Password berhasil diubah');
            document.getElementById('passwordForm').reset();
        }
    } catch (error) {
        console.error('Failed to change password:', error);
        showAlert('error', 'Error', error.message || 'Gagal mengubah password');
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }
}

function saveNotificationSettings() {
    // Notification settings are not yet implemented in the API
    showAlert('info', 'Info', 'Fitur pengaturan notifikasi akan segera hadir');
}

async function logoutAllDevices() {
    if (!confirm('Anda akan keluar dari semua perangkat. Lanjutkan?')) return;
    
    try {
        const response = await fetch(`${API_BASE}/logout`, {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${getToken()}`,
                'Accept': 'application/json'
            }
        });
        
        if (response.ok) {
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_user');
            window.location.href = '/login';
        }
    } catch (error) {
        console.error('Failed to logout:', error);
        showAlert('error', 'Error', 'Gagal keluar dari semua perangkat');
    }
}
</script>

<style>
.settings-nav.active {
    background-color: rgb(240 253 244);
    color: rgb(22 163 74);
    border-left: 3px solid rgb(22 163 74);
}
</style>
@endpush
