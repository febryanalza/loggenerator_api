<!-- Tab Content: User Activity Reports -->
<div id="tab-user-activity" class="tab-content hidden">
    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-filter mr-2 text-indigo-600"></i>
            Filter Aktivitas User
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                <input type="date" id="userActivityStartDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                <input type="date" id="userActivityEndDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Institusi</label>
                <select id="userActivityInstitution" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua Institusi</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="UserActivityReports.loadData()" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-search mr-2"></i>
                    Generate
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Logins</p>
                    <p class="text-2xl font-bold text-gray-900" id="userTotalLogins">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-sign-in-alt text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Unique Users</p>
                    <p class="text-2xl font-bold text-gray-900" id="userUniqueUsers">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">New Registrations</p>
                    <p class="text-2xl font-bold text-gray-900" id="userNewRegistrations">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-plus text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Avg Login/Day</p>
                    <p class="text-2xl font-bold text-gray-900" id="userAvgLoginPerDay">0</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-line text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Login Frequency Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-bar mr-2 text-indigo-600"></i>
                Login Frequency per Hari
            </h3>
            <div class="h-80">
                <canvas id="loginFrequencyChart"></canvas>
            </div>
        </div>
        
        <!-- Active Users Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-area mr-2 text-indigo-600"></i>
                Active Users per Hari
            </h3>
            <div class="h-80">
                <canvas id="activeUsersChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Registration Trend & Top Users -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Registration Trend -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-user-plus mr-2 text-indigo-600"></i>
                Trend Registrasi User
            </h3>
            <div class="h-64">
                <canvas id="registrationTrendChart"></canvas>
            </div>
        </div>
        
        <!-- Top Users by Entries -->
        <div class="bg-white rounded-lg shadow-md overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900">
                    <i class="fas fa-trophy mr-2 text-indigo-600"></i>
                    Top Users by Logbook Entries
                </h3>
            </div>
            <div class="overflow-x-auto max-h-64 overflow-y-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50 sticky top-0">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entries</th>
                        </tr>
                    </thead>
                    <tbody id="topUsersTable" class="bg-white divide-y divide-gray-200">
                        <tr>
                            <td colspan="3" class="px-6 py-8 text-center text-gray-500">
                                <i class="fas fa-users text-3xl mb-2 text-gray-300"></i>
                                <p>Klik "Generate" untuk melihat data</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
