/**
 * Reports & Analytics JavaScript Module
 */

// ========================================
// GLOBAL STATE & CONFIG
// ========================================
const API_BASE = '/api/admin/reports';
let authToken = localStorage.getItem('admin_token');

// Charts instances
let entriesByPeriodChart = null;
let entriesByTemplateChart = null;
let loginFrequencyChart = null;
let activeUsersChart = null;
let registrationTrendChart = null;
let entriesByInstitutionChart = null;
let activeUsersByInstitutionChart = null;

// ========================================
// UTILITY FUNCTIONS
// ========================================
function showLoading() {
    document.getElementById('loadingOverlay').classList.remove('hidden');
}

function hideLoading() {
    document.getElementById('loadingOverlay').classList.add('hidden');
}

function showToast(message, type = 'success') {
    const toast = document.getElementById('toast');
    const toastIcon = document.getElementById('toastIcon');
    const toastMessage = document.getElementById('toastMessage');
    
    toastMessage.textContent = message;
    
    if (type === 'success') {
        toastIcon.className = 'fas fa-check-circle text-2xl text-green-500';
    } else if (type === 'error') {
        toastIcon.className = 'fas fa-times-circle text-2xl text-red-500';
    } else {
        toastIcon.className = 'fas fa-info-circle text-2xl text-blue-500';
    }
    
    toast.classList.remove('hidden');
    setTimeout(() => toast.classList.add('hidden'), 3000);
}

function formatNumber(num) {
    return new Intl.NumberFormat('id-ID').format(num);
}

function getDefaultDates() {
    const end = new Date();
    const start = new Date();
    start.setMonth(start.getMonth() - 1);
    
    return {
        start: start.toISOString().split('T')[0],
        end: end.toISOString().split('T')[0]
    };
}

async function apiRequest(endpoint, options = {}) {
    const response = await fetch(endpoint, {
        ...options,
        headers: {
            'Authorization': `Bearer ${authToken}`,
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            ...options.headers
        }
    });
    
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    
    return response.json();
}

// ========================================
// REPORTS MANAGER (Tab Controller)
// ========================================
const ReportsManager = {
    currentTab: 'logbook',
    initialized: {
        logbook: false,
        'user-activity': false,
        institution: false,
        export: false,
        scheduled: false
    },

    init() {
        // Check auth
        if (!authToken) {
            window.location.href = '/login';
            return;
        }

        // Set default dates
        const dates = getDefaultDates();
        this.setDefaultDates(dates);

        // Load institutions for dropdowns
        this.loadInstitutions();

        // Initialize first tab
        this.switchTab('logbook');
    },

    switchTab(tabName) {
        this.currentTab = tabName;
        
        // Hide all tab contents
        document.querySelectorAll('.tab-content').forEach(content => {
            content.classList.add('hidden');
        });
        
        // Show selected tab content
        const selectedContent = document.getElementById(`tab-${tabName}`);
        if (selectedContent) {
            selectedContent.classList.remove('hidden');
        }
        
        // Update tab buttons styling
        document.querySelectorAll('.tab-button').forEach(btn => {
            btn.classList.remove('border-indigo-500', 'text-indigo-600');
            btn.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
        });
        
        const selectedBtn = document.getElementById(`tab-btn-${tabName}`);
        if (selectedBtn) {
            selectedBtn.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            selectedBtn.classList.add('border-indigo-500', 'text-indigo-600');
        }

        // Initialize tab data if not done
        if (!this.initialized[tabName]) {
            this.initializeTab(tabName);
            this.initialized[tabName] = true;
        }
    },

    initializeTab(tabName) {
        switch(tabName) {
            case 'logbook':
                LogbookReports.loadData();
                break;
            case 'user-activity':
                UserActivityReports.loadData();
                break;
            case 'institution':
                InstitutionPerformance.loadData();
                break;
            case 'export':
                ExportCenter.init();
                break;
            case 'scheduled':
                ScheduledReports.loadData();
                break;
        }
    },

    setDefaultDates(dates) {
        // Logbook
        const logbookStart = document.getElementById('logbookStartDate');
        const logbookEnd = document.getElementById('logbookEndDate');
        if (logbookStart) logbookStart.value = dates.start;
        if (logbookEnd) logbookEnd.value = dates.end;

        // User Activity
        const userStart = document.getElementById('userActivityStartDate');
        const userEnd = document.getElementById('userActivityEndDate');
        if (userStart) userStart.value = dates.start;
        if (userEnd) userEnd.value = dates.end;

        // Institution
        const instStart = document.getElementById('institutionStartDate');
        const instEnd = document.getElementById('institutionEndDate');
        if (instStart) instStart.value = dates.start;
        if (instEnd) instEnd.value = dates.end;

        // Export
        const exportStart = document.getElementById('exportStartDate');
        const exportEnd = document.getElementById('exportEndDate');
        if (exportStart) exportStart.value = dates.start;
        if (exportEnd) exportEnd.value = dates.end;
    },

    async loadInstitutions() {
        try {
            const response = await apiRequest('/api/institutions');
            const institutions = response.data || response;

            const selects = [
                document.getElementById('logbookInstitution'),
                document.getElementById('userActivityInstitution')
            ];

            selects.forEach(select => {
                if (select) {
                    institutions.forEach(inst => {
                        const option = document.createElement('option');
                        option.value = inst.id;
                        option.textContent = inst.name;
                        select.appendChild(option);
                    });
                }
            });
        } catch (error) {
            console.error('Failed to load institutions:', error);
        }
    }
};

// ========================================
// LOGBOOK REPORTS
// ========================================
const LogbookReports = {
    async loadData() {
        showLoading();
        try {
            const period = document.getElementById('logbookPeriod').value;
            const startDate = document.getElementById('logbookStartDate').value;
            const endDate = document.getElementById('logbookEndDate').value;
            const institutionId = document.getElementById('logbookInstitution').value;

            const params = new URLSearchParams({
                period,
                start_date: startDate,
                end_date: endDate,
                ...(institutionId && { institution_id: institutionId })
            });

            const response = await apiRequest(`${API_BASE}/logbook?${params}`);
            
            if (response.success) {
                this.updateUI(response.data);
            } else {
                showToast(response.message || 'Failed to load data', 'error');
            }
        } catch (error) {
            console.error('Error loading logbook reports:', error);
            showToast('Failed to load logbook reports', 'error');
        } finally {
            hideLoading();
        }
    },

    updateUI(data) {
        // Update summary cards
        document.getElementById('logbookTotalEntries').textContent = formatNumber(data.summary.total_entries);
        document.getElementById('logbookTotalTemplates').textContent = formatNumber(data.summary.total_templates);
        document.getElementById('logbookAvgPerDay').textContent = data.summary.avg_entries_per_day;
        document.getElementById('logbookVerificationRate').textContent = data.summary.verification_rate + '%';

        // Update charts
        this.updateEntriesByPeriodChart(data.entries_by_period);
        this.updateEntriesByTemplateChart(data.entries_by_template);

        // Update table
        this.updateTable(data.entries_by_template, data.summary.total_entries);
    },

    updateEntriesByPeriodChart(data) {
        const ctx = document.getElementById('entriesByPeriodChart').getContext('2d');
        
        if (entriesByPeriodChart) {
            entriesByPeriodChart.destroy();
        }

        entriesByPeriodChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.period),
                datasets: [{
                    label: 'Entries',
                    data: data.map(d => d.count),
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    },

    updateEntriesByTemplateChart(data) {
        const ctx = document.getElementById('entriesByTemplateChart').getContext('2d');
        
        if (entriesByTemplateChart) {
            entriesByTemplateChart.destroy();
        }

        const colors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#f97316', '#ec4899', '#14b8a6', '#6366f1'];

        entriesByTemplateChart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: data.map(d => d.template_name),
                datasets: [{
                    data: data.map(d => d.count),
                    backgroundColor: colors.slice(0, data.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 12 }
                    }
                }
            }
        });
    },

    updateTable(data, totalEntries) {
        const tbody = document.getElementById('logbookTemplateTable');
        
        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-inbox text-4xl mb-4 text-gray-300"></i>
                        <p>Tidak ada data untuk periode ini</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = data.map((item, index) => {
            const percentage = totalEntries > 0 ? ((item.count / totalEntries) * 100).toFixed(1) : 0;
            return `
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 text-sm text-gray-900">${index + 1}</td>
                    <td class="px-6 py-4 text-sm text-gray-900 font-medium">${item.template_name}</td>
                    <td class="px-6 py-4 text-sm text-gray-600">${formatNumber(item.count)}</td>
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                <div class="bg-indigo-600 h-2 rounded-full" style="width: ${percentage}%"></div>
                            </div>
                            <span class="text-sm text-gray-600">${percentage}%</span>
                        </div>
                    </td>
                </tr>
            `;
        }).join('');
    }
};

// ========================================
// USER ACTIVITY REPORTS
// ========================================
const UserActivityReports = {
    async loadData() {
        showLoading();
        try {
            const startDate = document.getElementById('userActivityStartDate').value;
            const endDate = document.getElementById('userActivityEndDate').value;
            const institutionId = document.getElementById('userActivityInstitution').value;

            const params = new URLSearchParams({
                start_date: startDate,
                end_date: endDate,
                ...(institutionId && { institution_id: institutionId })
            });

            const response = await apiRequest(`${API_BASE}/user-activity?${params}`);
            
            if (response.success) {
                this.updateUI(response.data);
            } else {
                showToast(response.message || 'Failed to load data', 'error');
            }
        } catch (error) {
            console.error('Error loading user activity reports:', error);
            showToast('Failed to load user activity reports', 'error');
        } finally {
            hideLoading();
        }
    },

    updateUI(data) {
        // Update summary cards
        document.getElementById('userTotalLogins').textContent = formatNumber(data.summary.total_logins);
        document.getElementById('userUniqueUsers').textContent = formatNumber(data.summary.unique_logged_in_users);
        document.getElementById('userNewRegistrations').textContent = formatNumber(data.summary.new_registrations);
        document.getElementById('userAvgLoginPerDay').textContent = data.summary.avg_logins_per_day;

        // Update charts
        this.updateLoginFrequencyChart(data.login_frequency);
        this.updateActiveUsersChart(data.active_users_per_day);
        this.updateRegistrationTrendChart(data.registration_trend);

        // Update top users table
        this.updateTopUsersTable(data.top_users_by_entries);
    },

    updateLoginFrequencyChart(data) {
        const ctx = document.getElementById('loginFrequencyChart').getContext('2d');
        
        if (loginFrequencyChart) {
            loginFrequencyChart.destroy();
        }

        loginFrequencyChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.date),
                datasets: [{
                    label: 'Logins',
                    data: data.map(d => d.count),
                    backgroundColor: '#4f46e5'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    },

    updateActiveUsersChart(data) {
        const ctx = document.getElementById('activeUsersChart').getContext('2d');
        
        if (activeUsersChart) {
            activeUsersChart.destroy();
        }

        activeUsersChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.map(d => d.date),
                datasets: [{
                    label: 'Active Users',
                    data: data.map(d => d.active_users),
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    },

    updateRegistrationTrendChart(data) {
        const ctx = document.getElementById('registrationTrendChart').getContext('2d');
        
        if (registrationTrendChart) {
            registrationTrendChart.destroy();
        }

        registrationTrendChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: data.map(d => d.date),
                datasets: [{
                    label: 'New Users',
                    data: data.map(d => d.count),
                    backgroundColor: '#8b5cf6'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });
    },

    updateTopUsersTable(data) {
        const tbody = document.getElementById('topUsersTable');
        
        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                        <i class="fas fa-users text-3xl mb-2 text-gray-300"></i>
                        <p>Tidak ada data</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = data.map((user, index) => `
            <tr class="hover:bg-gray-50">
                <td class="px-4 py-3">
                    <span class="${index < 3 ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800'} px-2 py-1 rounded-full text-xs font-medium">
                        ${index < 3 ? '🏆' : ''} #${index + 1}
                    </span>
                </td>
                <td class="px-4 py-3">
                    <div class="text-sm font-medium text-gray-900">${user.name}</div>
                    <div class="text-xs text-gray-500">${user.email}</div>
                </td>
                <td class="px-4 py-3 text-sm text-gray-600">${formatNumber(user.entries_count)}</td>
            </tr>
        `).join('');
    }
};

// ========================================
// INSTITUTION PERFORMANCE
// ========================================
const InstitutionPerformance = {
    async loadData() {
        showLoading();
        try {
            const startDate = document.getElementById('institutionStartDate').value;
            const endDate = document.getElementById('institutionEndDate').value;

            const params = new URLSearchParams({
                start_date: startDate,
                end_date: endDate
            });

            const response = await apiRequest(`${API_BASE}/institution-performance?${params}`);
            
            if (response.success) {
                this.updateUI(response.data);
            } else {
                showToast(response.message || 'Failed to load data', 'error');
            }
        } catch (error) {
            console.error('Error loading institution performance:', error);
            showToast('Failed to load institution performance', 'error');
        } finally {
            hideLoading();
        }
    },

    updateUI(data) {
        // Update summary cards
        document.getElementById('instTotalInstitutions').textContent = formatNumber(data.summary.total_institutions);
        document.getElementById('instTotalEntries').textContent = formatNumber(data.summary.total_entries);
        document.getElementById('instTotalActiveUsers').textContent = formatNumber(data.summary.total_active_users);
        document.getElementById('instAvgEntries').textContent = data.summary.avg_entries_per_institution;

        // Update charts
        this.updateEntriesByInstitutionChart(data.institutions);
        this.updateActiveUsersByInstitutionChart(data.institutions);

        // Update table
        this.updateTable(data.institutions);
    },

    updateEntriesByInstitutionChart(data) {
        const ctx = document.getElementById('entriesByInstitutionChart').getContext('2d');
        
        if (entriesByInstitutionChart) {
            entriesByInstitutionChart.destroy();
        }

        const top10 = data.slice(0, 10);

        entriesByInstitutionChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: top10.map(d => d.name.length > 20 ? d.name.substring(0, 20) + '...' : d.name),
                datasets: [{
                    label: 'Entries',
                    data: top10.map(d => d.entries_count),
                    backgroundColor: '#4f46e5'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: { legend: { display: false } },
                scales: { x: { beginAtZero: true } }
            }
        });
    },

    updateActiveUsersByInstitutionChart(data) {
        const ctx = document.getElementById('activeUsersByInstitutionChart').getContext('2d');
        
        if (activeUsersByInstitutionChart) {
            activeUsersByInstitutionChart.destroy();
        }

        const top10 = data.filter(d => d.active_users > 0).slice(0, 10);
        const colors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4', '#f97316', '#ec4899', '#14b8a6', '#6366f1'];

        activeUsersByInstitutionChart = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: top10.map(d => d.name),
                datasets: [{
                    data: top10.map(d => d.active_users),
                    backgroundColor: colors.slice(0, top10.length)
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { boxWidth: 12 }
                    }
                }
            }
        });
    },

    updateTable(data) {
        const tbody = document.getElementById('institutionPerformanceTable');
        
        if (data.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                        <i class="fas fa-building text-4xl mb-4 text-gray-300"></i>
                        <p>Tidak ada data</p>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = data.map((inst, index) => `
            <tr class="hover:bg-gray-50">
                <td class="px-6 py-4 text-sm text-gray-900">${index + 1}</td>
                <td class="px-6 py-4 text-sm font-medium text-gray-900">${inst.name}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${formatNumber(inst.total_users)}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${formatNumber(inst.active_users)}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${formatNumber(inst.total_templates)}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${formatNumber(inst.entries_count)}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${formatNumber(inst.verified_entries)}</td>
                <td class="px-6 py-4">
                    <span class="px-2 py-1 ${inst.verification_rate >= 80 ? 'bg-green-100 text-green-800' : inst.verification_rate >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'} rounded-full text-xs">
                        ${inst.verification_rate}%
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">${inst.avg_entries_per_user}</td>
            </tr>
        `).join('');
    }
};

// ========================================
// EXPORT CENTER
// ========================================
const ExportCenter = {
    init() {
        // Nothing to initialize, UI is ready
    },

    async exportData() {
        showLoading();
        try {
            const type = document.getElementById('exportType').value;
            const startDate = document.getElementById('exportStartDate').value;
            const endDate = document.getElementById('exportEndDate').value;
            const format = document.getElementById('exportFormat').value;

            const params = new URLSearchParams({
                type,
                start_date: startDate,
                end_date: endDate,
                format
            });

            if (format === 'json') {
                const response = await apiRequest(`${API_BASE}/export?${params}`);
                if (response.success) {
                    // Download as JSON file
                    const blob = new Blob([JSON.stringify(response.data, null, 2)], { type: 'application/json' });
                    const url = URL.createObjectURL(blob);
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `${type}_${startDate}_${endDate}.json`;
                    a.click();
                    URL.revokeObjectURL(url);
                    showToast('Export berhasil!', 'success');
                }
            } else {
                // For CSV, trigger direct download
                window.location.href = `${API_BASE}/export?${params}&download=true`;
                showToast('Export berhasil!', 'success');
            }
        } catch (error) {
            console.error('Export error:', error);
            showToast('Failed to export data', 'error');
        } finally {
            hideLoading();
        }
    }
};

// ========================================
// SCHEDULED REPORTS
// ========================================
const ScheduledReports = {
    async loadData() {
        // Data is already in the UI (dummy), no need to load
    },

    openCreateModal() {
        document.getElementById('createScheduleModal').classList.remove('hidden');
    },

    closeCreateModal() {
        document.getElementById('createScheduleModal').classList.add('hidden');
    },

    async create() {
        const name = document.getElementById('scheduleName').value;
        const type = document.getElementById('scheduleType').value;
        const schedule = document.getElementById('scheduleFrequency').value;
        const recipients = document.getElementById('scheduleRecipients').value;

        if (!name || !recipients) {
            showToast('Nama dan recipients harus diisi', 'error');
            return;
        }

        showToast('Schedule created (demo mode)', 'success');
        this.closeCreateModal();
    },

    async toggle(id) {
        showToast('Schedule status toggled (demo mode)', 'success');
    },

    async delete(id) {
        if (confirm('Yakin ingin menghapus scheduled report ini?')) {
            showToast('Schedule deleted (demo mode)', 'success');
        }
    }
};

// ========================================
// INITIALIZATION
// ========================================
document.addEventListener('DOMContentLoaded', function() {
    ReportsManager.init();
});
