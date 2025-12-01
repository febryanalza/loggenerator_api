<!-- Tab Content: Export Center -->
<div id="tab-export" class="tab-content hidden">
    <!-- Export Options -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">
            <i class="fas fa-download mr-2 text-indigo-600"></i>
            Export Data
        </h3>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tipe Data</label>
                <select id="exportType" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="logbook_entries">Logbook Entries</option>
                    <option value="users">Users</option>
                    <option value="institutions">Institutions</option>
                    <option value="audit_logs">Audit Logs</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Mulai</label>
                <input type="date" id="exportStartDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Akhir</label>
                <input type="date" id="exportEndDate" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Format</label>
                <select id="exportFormat" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500">
                    <option value="csv">CSV (Spreadsheet)</option>
                    <option value="json">JSON</option>
                </select>
            </div>
            <div class="flex items-end">
                <button onclick="ExportCenter.exportData()" class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                    <i class="fas fa-file-export mr-2"></i>
                    Export
                </button>
            </div>
        </div>
    </div>

    <!-- Export Types Info -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-blue-500">
            <div class="flex items-center mb-3">
                <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-file-alt text-blue-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Logbook Entries</h4>
            </div>
            <p class="text-sm text-gray-600">Export semua data logbook termasuk template, user, dan status verifikasi.</p>
            <p class="text-xs text-gray-400 mt-2">Max: 5000 rows</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-green-500">
            <div class="flex items-center mb-3">
                <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-users text-green-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Users</h4>
            </div>
            <p class="text-sm text-gray-600">Export daftar user dengan detail institusi dan status akun.</p>
            <p class="text-xs text-gray-400 mt-2">Max: 5000 rows</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-purple-500">
            <div class="flex items-center mb-3">
                <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-building text-purple-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Institutions</h4>
            </div>
            <p class="text-sm text-gray-600">Export daftar institusi dengan jumlah user dan template.</p>
            <p class="text-xs text-gray-400 mt-2">All institutions</p>
        </div>
        
        <div class="bg-white rounded-lg shadow-md p-6 border-l-4 border-yellow-500">
            <div class="flex items-center mb-3">
                <div class="w-10 h-10 bg-yellow-100 rounded-full flex items-center justify-center mr-3">
                    <i class="fas fa-history text-yellow-600"></i>
                </div>
                <h4 class="font-semibold text-gray-900">Audit Logs</h4>
            </div>
            <p class="text-sm text-gray-600">Export log aktivitas sistem untuk audit dan keamanan.</p>
            <p class="text-xs text-gray-400 mt-2">Max: 5000 rows</p>
        </div>
    </div>

    <!-- Recent Exports (Dummy) -->
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-history mr-2 text-indigo-600"></i>
                Riwayat Export Terbaru
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Waktu</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Format</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Periode</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rows</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody id="exportHistoryTable" class="bg-white divide-y divide-gray-200">
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">Just now</td>
                        <td class="px-6 py-4 text-sm text-gray-600">Logbook Entries</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">CSV</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">Nov 1 - Nov 30, 2025</td>
                        <td class="px-6 py-4 text-sm text-gray-600">1,234</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                <i class="fas fa-check-circle mr-1"></i>Success
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">2 hours ago</td>
                        <td class="px-6 py-4 text-sm text-gray-600">Users</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">JSON</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">Oct 1 - Oct 31, 2025</td>
                        <td class="px-6 py-4 text-sm text-gray-600">456</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                <i class="fas fa-check-circle mr-1"></i>Success
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td class="px-6 py-4 text-sm text-gray-900">Yesterday</td>
                        <td class="px-6 py-4 text-sm text-gray-600">Audit Logs</td>
                        <td class="px-6 py-4 text-sm text-gray-600">
                            <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">CSV</span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-600">Nov 1 - Nov 15, 2025</td>
                        <td class="px-6 py-4 text-sm text-gray-600">2,890</td>
                        <td class="px-6 py-4">
                            <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">
                                <i class="fas fa-check-circle mr-1"></i>Success
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
