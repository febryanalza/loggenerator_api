@extends('admin.layout')

@section('title', 'Notifications')
@section('page-title', 'Notifikasi')
@section('page-description', 'Kelola, kirim, dan pantau notifikasi')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h3 class="text-xl font-semibold text-gray-800">Panel Notifikasi</h3>
            <p class="text-sm text-gray-500">Kirim notifikasi ke user, role, atau anggota logbook tertentu dan pantau semua notifikasi.</p>
        </div>
        <div class="flex items-center space-x-3">
            <button id="refreshNotifications" class="px-4 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-50">
                <i class="fas fa-rotate mr-2"></i>Reload
            </button>
        </div>
    </div>

    <div class="bg-white shadow rounded-lg">
        <div class="border-b flex gap-2 px-6 pt-4">
            <button data-tab="compose" class="tab-button active px-4 py-2 rounded-t-lg text-sm font-semibold text-indigo-600 border-b-2 border-indigo-600">Buat Notifikasi</button>
            <button data-tab="list" class="tab-button px-4 py-2 rounded-t-lg text-sm font-semibold text-gray-600 hover:text-indigo-600">Lihat Notifikasi</button>
        </div>

        <div id="composeTab" class="tab-panel p-6">
            <form id="notificationForm" class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Judul</label>
                        <input type="text" name="title" class="mt-1 w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Contoh: Pembaruan Sistem" required>
                    </div>
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700">Teks Tombol (opsional)</label>
                        <input type="text" name="action_text" class="mt-1 w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Buka" />
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pesan</label>
                    <textarea name="message" rows="3" class="mt-1 w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="Isi pesan yang ingin dikirim"></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">URL Aksi (opsional)</label>
                    <input type="url" name="action_url" class="mt-1 w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500" placeholder="https://contoh.com/detail" />
                </div>

                <div class="border rounded-lg p-4 space-y-3 bg-gray-50">
                    <div class="font-semibold text-gray-800">Tujuan</div>
                    <div class="grid md:grid-cols-2 gap-3">
                        <label class="inline-flex items-center space-x-2">
                            <input type="radio" name="target" value="users" class="text-indigo-600" checked>
                            <span>User tertentu</span>
                        </label>
                        <label class="inline-flex items-center space-x-2">
                            <input type="radio" name="target" value="all" class="text-indigo-600">
                            <span>Semua user</span>
                        </label>
                        <label class="inline-flex items-center space-x-2">
                            <input type="radio" name="target" value="role" class="text-indigo-600">
                            <span>Role tertentu</span>
                        </label>
                        <label class="inline-flex items-center space-x-2">
                            <input type="radio" name="target" value="template" class="text-indigo-600">
                            <span>Anggota logbook (template)</span>
                        </label>
                    </div>

                    <div id="userSelector" class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="text-sm font-medium text-gray-700">Pilih User</label>
                            <input id="userSearch" type="text" class="border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm" placeholder="Cari nama / email">
                        </div>
                        <div id="userList" class="max-h-48 overflow-y-auto border rounded-lg p-3 bg-white text-sm text-gray-700 space-y-2"></div>
                    </div>

                    <div id="roleSelector" class="space-y-2 hidden">
                        <label class="text-sm font-medium text-gray-700">Pilih Role</label>
                        <select id="roleSelect" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm"></select>
                    </div>

                    <div id="templateSelector" class="space-y-2 hidden">
                        <label class="text-sm font-medium text-gray-700">Pilih Logbook Template</label>
                        <select id="templateSelect" class="w-full border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm"></select>
                    </div>
                </div>

                <div class="flex items-center space-x-3">
                    <button type="submit" id="submitNotification" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:ring-2 focus:ring-indigo-500 focus:ring-offset-1 flex items-center space-x-2">
                        <i class="fas fa-paper-plane"></i>
                        <span>Kirim</span>
                    </button>
                    <span id="formStatus" class="text-sm text-gray-500"></span>
                </div>
            </form>
        </div>

        <div id="listTab" class="tab-panel p-6 hidden">
            <div class="flex flex-wrap items-center gap-3 mb-4">
                <select id="scopeSelect" class="border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="self">Notifikasi saya</option>
                    <option value="all">Semua notifikasi (admin)
                    </option>
                </select>
                <select id="filterSelect" class="border-gray-300 rounded-lg focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    <option value="all">Semua</option>
                    <option value="unread">Belum dibaca</option>
                    <option value="read">Sudah dibaca</option>
                </select>
                <button id="markAllRead" class="px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm text-gray-700 hover:bg-gray-50"><i class="fas fa-envelope-open mr-2"></i>Tandai semua dibaca</button>
            </div>
            <div id="notificationsList" class="divide-y divide-gray-100"></div>
            <div id="notificationEmpty" class="text-center text-gray-500 text-sm py-6 hidden">Belum ada notifikasi.</div>
        </div>
    </div>
</div>

<!-- Toast -->
<div id="toast" class="fixed bottom-4 right-4 bg-gray-900 text-white px-4 py-3 rounded-lg shadow-lg opacity-0 pointer-events-none transition" aria-live="polite"></div>
@endsection

@push('scripts')
<script>
(function() {
    const token = localStorage.getItem('admin_token');
    if (!token && window.AdminTokenManager) {
        AdminTokenManager.init();
    }

    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': `Bearer ${localStorage.getItem('admin_token') || ''}`
    };

    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanels = document.querySelectorAll('.tab-panel');
    const form = document.getElementById('notificationForm');
    const userListEl = document.getElementById('userList');
    const userSearch = document.getElementById('userSearch');
    const roleSelect = document.getElementById('roleSelect');
    const templateSelect = document.getElementById('templateSelect');
    const targetRadios = document.querySelectorAll('input[name="target"]');
    const userSelector = document.getElementById('userSelector');
    const roleSelector = document.getElementById('roleSelector');
    const templateSelector = document.getElementById('templateSelector');
    const notificationsList = document.getElementById('notificationsList');
    const notificationEmpty = document.getElementById('notificationEmpty');
    const filterSelect = document.getElementById('filterSelect');
    const scopeSelect = document.getElementById('scopeSelect');
    const toast = document.getElementById('toast');

    function showToast(message, type = 'success') {
        if (!toast) return;
        toast.textContent = message;
        toast.classList.remove('opacity-0');
        toast.classList.toggle('bg-red-600', type === 'error');
        toast.classList.toggle('bg-gray-900', type !== 'error');
        setTimeout(() => toast.classList.add('opacity-0'), 2500);
    }

    // Tabs
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            tabButtons.forEach(b => b.classList.remove('text-indigo-600', 'border-indigo-600', 'active'));
            tabPanels.forEach(p => p.classList.add('hidden'));
            btn.classList.add('text-indigo-600', 'border-indigo-600', 'active');
            const tab = btn.getAttribute('data-tab');
            document.getElementById(`${tab}Tab`).classList.remove('hidden');
        });
    });

    // Target switching
    targetRadios.forEach(r => r.addEventListener('change', updateTargetUI));
    function updateTargetUI() {
        const value = document.querySelector('input[name="target"]:checked').value;
        userSelector.classList.toggle('hidden', value !== 'users');
        roleSelector.classList.toggle('hidden', value !== 'role');
        templateSelector.classList.toggle('hidden', value !== 'template');
    }
    updateTargetUI();

    async function fetchJson(url, options = {}) {
        const res = await fetch(url, { ...options, headers: { ...headers, ...(options.headers || {}) } });
        if (!res.ok) throw new Error((await res.json()).message || 'Request failed');
        return res.json();
    }

    async function loadUsers(keyword = '') {
        userListEl.innerHTML = '<div class="text-gray-500 text-sm">Memuat user...</div>';
        try {
            const data = await fetchJson(`/api/users/search?per_page=50&search=${encodeURIComponent(keyword)}`);
            const users = data.data || [];
            if (!users.length) {
                userListEl.innerHTML = '<div class="text-gray-400 text-sm">Tidak ada user ditemukan.</div>';
                return;
            }
            userListEl.innerHTML = users.map(u => `
                <label class="flex items-center space-x-3">
                    <input type="checkbox" value="${u.id}" class="user-checkbox text-indigo-600">
                    <div>
                        <div class="font-medium text-gray-800">${u.name}</div>
                        <div class="text-xs text-gray-500">${u.email} • ${u.roles.join(', ')}</div>
                    </div>
                </label>
            `).join('');
        } catch (e) {
            userListEl.innerHTML = '<div class="text-red-500 text-sm">Gagal memuat user.</div>';
        }
    }

    async function loadRoles() {
        try {
            const data = await fetchJson('/api/roles?per_page=50');
            const roles = data.data || [];
            roleSelect.innerHTML = roles.map(r => `<option value="${r.name}">${r.name}</option>`).join('');
        } catch (e) {
            roleSelect.innerHTML = '<option disabled>Gagal memuat role</option>';
        }
    }

    async function loadTemplates() {
        try {
            const data = await fetchJson('/api/templates/admin/all');
            const templates = data.data || data.templates || [];
            templateSelect.innerHTML = templates.map(t => `<option value="${t.id}">${t.name}</option>`).join('');
        } catch (e) {
            templateSelect.innerHTML = '<option disabled>Gagal memuat template</option>';
        }
    }

    async function loadNotifications() {
        const filter = filterSelect.value;
        const scope = scopeSelect.value;
        let query = '?per_page=20';
        if (filter === 'unread') query += '&unread_only=1';
        if (filter === 'read') query += '&read_only=1';
        if (scope === 'all') query += '&scope=all';
        notificationsList.innerHTML = '<div class="text-gray-500 text-sm py-4">Memuat notifikasi...</div>';
        try {
            const data = await fetchJson(`/api/notifications${query}`);
            const items = data.data?.notifications || [];
            notificationEmpty.classList.toggle('hidden', items.length > 0);
            notificationsList.innerHTML = items.map(n => {
                const isUnread = !n.read_at;
                const actionUrl = n.data?.action_url;
                return `
                    <div class="py-3 flex justify-between items-start">
                        <div>
                            <div class="flex items-center space-x-2">
                                ${isUnread ? '<span class="h-2 w-2 rounded-full bg-indigo-500"></span>' : ''}
                                <span class="font-semibold text-gray-800">${n.data?.title || 'Tanpa judul'}</span>
                                <span class="text-xs text-gray-400">${new Date(n.created_at).toLocaleString('id-ID')}</span>
                            </div>
                            <div class="text-sm text-gray-700 mt-1">${n.data?.message || ''}</div>
                            ${actionUrl ? `<a href="${actionUrl}" target="_blank" class="text-indigo-600 text-sm hover:underline">${n.data?.action_text || 'Buka tautan'}</a>` : ''}
                        </div>
                        <div class="flex items-center space-x-2 text-sm">
                            ${isUnread ? `<button data-id="${n.id}" class="mark-read text-green-600 hover:underline">Tandai dibaca</button>` : ''}
                            <button data-id="${n.id}" class="delete text-red-600 hover:underline">Hapus</button>
                        </div>
                    </div>
                `;
            }).join('');
        } catch (e) {
            notificationsList.innerHTML = '<div class="text-red-500 text-sm py-4">Gagal memuat notifikasi.</div>';
        }
    }

    async function markAllRead() {
        try {
            await fetchJson('/api/notifications/mark-all-read', { method: 'POST' });
            showToast('Semua notifikasi ditandai dibaca');
            loadNotifications();
        } catch (e) {
            showToast('Gagal menandai semua', 'error');
        }
    }

    async function markRead(id) {
        try {
            await fetchJson(`/api/notifications/${id}/read`, { method: 'POST' });
            loadNotifications();
        } catch (e) {
            showToast('Gagal menandai', 'error');
        }
    }

    async function deleteNotification(id) {
        try {
            await fetchJson(`/api/notifications/${id}`, { method: 'DELETE' });
            loadNotifications();
        } catch (e) {
            showToast('Gagal menghapus', 'error');
        }
    }

    notificationsList.addEventListener('click', (e) => {
        if (e.target.classList.contains('mark-read')) {
            markRead(e.target.dataset.id);
        }
        if (e.target.classList.contains('delete')) {
            deleteNotification(e.target.dataset.id);
        }
    });

    document.getElementById('markAllRead').addEventListener('click', markAllRead);
    filterSelect.addEventListener('change', loadNotifications);
    scopeSelect.addEventListener('change', loadNotifications);
    document.getElementById('refreshNotifications').addEventListener('click', loadNotifications);
    userSearch.addEventListener('input', (e) => loadUsers(e.target.value));

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const formData = new FormData(form);
        const payload = {
            title: formData.get('title'),
            message: formData.get('message'),
            action_text: formData.get('action_text'),
            action_url: formData.get('action_url')
        };

        const target = formData.get('target');
        let endpoint = '';
        let method = 'POST';

        try {
            if (target === 'users') {
                const userIds = Array.from(document.querySelectorAll('.user-checkbox:checked')).map(cb => cb.value);
                if (!userIds.length) {
                    showToast('Pilih minimal satu user', 'error');
                    return;
                }
                endpoint = '/api/notifications/send';
                payload.user_ids = userIds;
            } else if (target === 'all') {
                endpoint = '/api/notifications/send-all';
            } else if (target === 'role') {
                const roleName = roleSelect.value;
                if (!roleName) {
                    showToast('Pilih role', 'error');
                    return;
                }
                endpoint = '/api/notifications/send-to-role';
                payload.role_name = roleName;
            } else if (target === 'template') {
                const templateId = templateSelect.value;
                if (!templateId) {
                    showToast('Pilih template logbook', 'error');
                    return;
                }
                endpoint = '/api/notifications/send-to-template';
                payload.template_id = templateId;
            }

            document.getElementById('submitNotification').disabled = true;
            const res = await fetch(endpoint, {
                method,
                headers,
                body: JSON.stringify(payload)
            });
            const data = await res.json();
            document.getElementById('submitNotification').disabled = false;
            if (!res.ok) throw new Error(data.message || 'Gagal mengirim notifikasi');
            showToast('Notifikasi dikirim');
            form.reset();
            updateTargetUI();
            loadNotifications();
        } catch (err) {
            document.getElementById('submitNotification').disabled = false;
            showToast(err.message || 'Terjadi kesalahan', 'error');
        }
    });

    // Initial load
    loadUsers();
    loadRoles();
    loadTemplates();
    loadNotifications();
})();
</script>
@endpush
