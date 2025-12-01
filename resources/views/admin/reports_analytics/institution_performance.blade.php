<!-- Tab Content: Institution Performance -->
<div id="tab-institution" class="tab-content hidden">
    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-filter mr-2 text-indigo-600"></i>
            Filter Periode
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                <input type="date" id="institutionStartDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                <input type="date" id="institutionEndDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div class="flex items-end">
                <button onclick="InstitutionPerformance.loadData()" class="w-full px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
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
                    <p class="text-sm text-gray-600 mb-1">Total Institutions</p>
                    <p class="text-2xl font-bold text-gray-900" id="instTotalInstitutions">0</p>
                </div>
                <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-building text-blue-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Entries</p>
                    <p class="text-2xl font-bold text-gray-900" id="instTotalEntries">0</p>
                </div>
                <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-file-alt text-green-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Total Active Users</p>
                    <p class="text-2xl font-bold text-gray-900" id="instTotalActiveUsers">0</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-users text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-600 mb-1">Avg Entries/Institution</p>
                    <p class="text-2xl font-bold text-gray-900" id="instAvgEntries">0</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-chart-bar text-yellow-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <!-- Entries by Institution Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-bar mr-2 text-indigo-600"></i>
                Entries per Institution
            </h3>
            <div class="h-80">
                <canvas id="entriesByInstitutionChart"></canvas>
            </div>
        </div>
        
        <!-- Active Users by Institution Chart -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">
                <i class="fas fa-chart-pie mr-2 text-indigo-600"></i>
                Active Users per Institution
            </h3>
            <div class="h-80">
                <canvas id="activeUsersByInstitutionChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Institution Performance Table -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-table mr-2 text-indigo-600"></i>
                Detail Performance per Institution
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rank</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Institution</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Active Users</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Templates</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Entries</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verified</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Verification Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Avg/User</th>
                    </tr>
                </thead>
                <tbody id="institutionPerformanceTable" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td colspan="9" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-building text-4xl mb-4 text-gray-300"></i>
                            <p>Klik "Generate" untuk melihat data</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
