<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LogbookTemplate;
use App\Models\LogbookData;
use App\Models\LogbookExport;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Style\Font;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use Barryvdh\DomPDF\Facade\Pdf;

class LogbookExportController extends Controller
{
    /**
     * Export logbook template with all its data to Word document
     *
     * @param Request $request
     * @param string $templateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportToWord(Request $request, string $templateId)
    {
        $user = $request->user();
        $export = null;
        $filename = null;

        try {
            // Check if ZIP extension is loaded (required for PHPWord)
            if (!extension_loaded('zip')) {
                Log::error('PHP ZIP extension is not loaded. Required for Word document export.');
                return response()->json([
                    'success' => false,
                    'message' => 'Server configuration error: ZIP extension is required for export. Please contact administrator.'
                ], 500);
            }

            // Get the logbook template with relationships
            $template = LogbookTemplate::with(['fields', 'institution', 'owner'])
                ->findOrFail($templateId);

            // Check user access to this template
            $hasAccess = $this->checkUserAccess($user, $template);
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to export this logbook'
                ], 403);
            }

            // Get all data for this template
            $logbookData = LogbookData::where('template_id', $templateId)
                ->with(['writer', 'verifier'])
                ->orderBy('created_at', 'asc')
                ->get();

            // Generate Word document
            $phpWord = new PhpWord();
            
            // Set document properties
            $properties = $phpWord->getDocInfo();
            $properties->setCreator('LogGenerator System');
            $properties->setCompany($template->institution?->name ?? 'LogGenerator');
            $properties->setTitle('Logbook Export - ' . $template->name);
            $properties->setDescription('Exported logbook data from LogGenerator');
            $properties->setCreated(time());

            // Define styles
            $this->defineStyles($phpWord);

            // Add section
            $section = $phpWord->addSection([
                'marginTop' => 1000,
                'marginBottom' => 1000,
                'marginLeft' => 1200,
                'marginRight' => 1200,
            ]);

            // Add document header/title
            $this->addDocumentHeader($section, $template);

            // Add template identity table
            $this->addIdentityTable($section, $template, $logbookData->count());

            // Add spacing
            $section->addTextBreak(1);

            // Add data table if there are fields and data
            if ($template->fields->count() > 0) {
                $this->addDataTable($section, $template, $logbookData);
            } else {
                $section->addText(
                    'No fields defined for this logbook template.',
                    'normalText'
                );
            }

            // Add footer with export info
            $this->addDocumentFooter($section, $user);

            // Generate filename
            $filename = $this->generateFilename($template);
            
            // Ensure export directory exists with proper error handling
            $exportPath = 'export_logbook';
            $fullExportDir = storage_path('app/public/' . $exportPath);
            
            // Create directory if it doesn't exist (using PHP's mkdir for reliability)
            if (!is_dir($fullExportDir)) {
                // Try to create directory with recursive flag and proper permissions
                if (!mkdir($fullExportDir, 0755, true) && !is_dir($fullExportDir)) {
                    Log::error('Failed to create export directory', [
                        'path' => $fullExportDir,
                        'user_id' => $user->id
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create export directory. Please contact administrator.'
                    ], 500);
                }
            }
            
            // Verify directory is writable
            if (!is_writable($fullExportDir)) {
                Log::error('Export directory is not writable', [
                    'path' => $fullExportDir,
                    'user_id' => $user->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Export directory is not writable. Please contact administrator.'
                ], 500);
            }

            // Save file
            $relativePath = $exportPath . '/' . $filename;
            $fullPath = $fullExportDir . '/' . $filename;
            $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save($fullPath);

            // Get file size
            $fileSize = filesize($fullPath);

            // Generate public URL
            $fileUrl = url('storage/' . $relativePath);

            // Calculate expiration (7 days from now)
            $expiresAt = now()->addDays(7);

            // Create export record in database
            $export = LogbookExport::create([
                'template_id' => $template->id,
                'exported_by' => $user->id,
                'file_name' => $filename,
                'file_type' => 'docx',
                'file_path' => $relativePath,
                'file_url' => $fileUrl,
                'file_size' => $fileSize,
                'total_entries' => $logbookData->count(),
                'total_fields' => $template->fields->count(),
                'status' => 'completed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expires_at' => $expiresAt,
            ]);

            // Create audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'EXPORT_LOGBOOK',
                'description' => "User exported logbook '{$template->name}' to Word document",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logbook exported successfully',
                'data' => [
                    'id' => $export->id,
                    'template_id' => $export->template_id,
                    'template_name' => $template->name,
                    'file_name' => $export->file_name,
                    'file_type' => $export->file_type,
                    'file_url' => $export->file_url,
                    'file_size' => $export->file_size,
                    'file_size_formatted' => $export->formatted_file_size,
                    'total_entries' => $export->total_entries,
                    'total_fields' => $export->total_fields,
                    'status' => $export->status,
                    'exported_by' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'expires_at' => $export->expires_at->toIso8601String(),
                    'created_at' => $export->created_at->toIso8601String(),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logbook template not found'
            ], 404);

        } catch (\Exception $e) {
            // Create failed export record
            if (isset($template)) {
                LogbookExport::create([
                    'template_id' => $template->id,
                    'exported_by' => $user->id,
                    'file_name' => $filename ?? 'unknown',
                    'file_type' => 'docx',
                    'file_path' => '',
                    'file_url' => '',
                    'file_size' => 0,
                    'total_entries' => 0,
                    'total_fields' => 0,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            Log::error('Failed to export logbook: ' . $e->getMessage(), [
                'template_id' => $templateId,
                'user_id' => $user?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export logbook. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Export logbook template with all its data to PDF document
     *
     * @param Request $request
     * @param string $templateId
     * @return \Illuminate\Http\JsonResponse
     */
    public function exportToPdf(Request $request, string $templateId)
    {
        $user = $request->user();
        $export = null;
        $filename = null;

        try {
            // Get the logbook template with relationships
            $template = LogbookTemplate::with(['fields', 'institution', 'owner'])
                ->findOrFail($templateId);

            // Check user access to this template
            $hasAccess = $this->checkUserAccess($user, $template);
            
            if (!$hasAccess) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to export this logbook'
                ], 403);
            }

            // Get all data for this template
            $logbookData = LogbookData::where('template_id', $templateId)
                ->with(['writer', 'verifier'])
                ->orderBy('created_at', 'asc')
                ->get();

            // Get fields ordered by name
            $fields = $template->fields->sortBy('name');

            // Generate PDF using blade view
            $pdf = Pdf::loadView('exports.logbook-pdf', [
                'template' => $template,
                'logbookData' => $logbookData,
                'fields' => $fields,
                'user' => $user,
                'exportDate' => now(),
            ]);

            // Set paper size and orientation
            $pdf->setPaper('a4', 'portrait');
            
            // Set PDF options for better rendering
            $pdf->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'sans-serif',
            ]);

            // Generate filename
            $filename = $this->generatePdfFilename($template);
            
            // Ensure export directory exists with proper error handling
            $exportPath = 'export_logbook';
            $fullExportDir = storage_path('app/public/' . $exportPath);
            
            // Create directory if it doesn't exist
            if (!is_dir($fullExportDir)) {
                if (!mkdir($fullExportDir, 0755, true) && !is_dir($fullExportDir)) {
                    Log::error('Failed to create export directory for PDF', [
                        'path' => $fullExportDir,
                        'user_id' => $user->id
                    ]);
                    
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to create export directory. Please contact administrator.'
                    ], 500);
                }
            }
            
            // Verify directory is writable
            if (!is_writable($fullExportDir)) {
                Log::error('Export directory is not writable for PDF', [
                    'path' => $fullExportDir,
                    'user_id' => $user->id
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Export directory is not writable. Please contact administrator.'
                ], 500);
            }

            // Save file
            $relativePath = $exportPath . '/' . $filename;
            $fullPath = $fullExportDir . '/' . $filename;
            $pdf->save($fullPath);

            // Get file size
            $fileSize = filesize($fullPath);

            // Generate public URL
            $fileUrl = url('storage/' . $relativePath);

            // Calculate expiration (7 days from now)
            $expiresAt = now()->addDays(7);

            // Create export record in database
            $export = LogbookExport::create([
                'template_id' => $template->id,
                'exported_by' => $user->id,
                'file_name' => $filename,
                'file_type' => 'pdf',
                'file_path' => $relativePath,
                'file_url' => $fileUrl,
                'file_size' => $fileSize,
                'total_entries' => $logbookData->count(),
                'total_fields' => $template->fields->count(),
                'status' => 'completed',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'expires_at' => $expiresAt,
            ]);

            // Create audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'EXPORT_LOGBOOK_PDF',
                'description' => "User exported logbook '{$template->name}' to PDF document",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Logbook exported to PDF successfully',
                'data' => [
                    'id' => $export->id,
                    'template_id' => $export->template_id,
                    'template_name' => $template->name,
                    'file_name' => $export->file_name,
                    'file_type' => $export->file_type,
                    'file_url' => $export->file_url,
                    'file_size' => $export->file_size,
                    'file_size_formatted' => $export->formatted_file_size,
                    'total_entries' => $export->total_entries,
                    'total_fields' => $export->total_fields,
                    'status' => $export->status,
                    'exported_by' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                    ],
                    'expires_at' => $export->expires_at->toIso8601String(),
                    'created_at' => $export->created_at->toIso8601String(),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logbook template not found'
            ], 404);

        } catch (\Exception $e) {
            // Create failed export record
            if (isset($template)) {
                LogbookExport::create([
                    'template_id' => $template->id,
                    'exported_by' => $user->id,
                    'file_name' => $filename ?? 'unknown',
                    'file_type' => 'pdf',
                    'file_path' => '',
                    'file_url' => '',
                    'file_size' => 0,
                    'total_entries' => 0,
                    'total_fields' => 0,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            }

            Log::error('Failed to export logbook to PDF: ' . $e->getMessage(), [
                'template_id' => $templateId,
                'user_id' => $user?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export logbook to PDF. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }

    /**
     * Get current user's export history across all templates they have access to
     * 
     * This endpoint returns exports made by the current user
     */
    public function getMyExports(Request $request)
    {
        try {
            $user = $request->user();
            $perPage = $request->get('per_page', 20);
            
            // Get user's exports
            $exports = LogbookExport::where('exported_by', $user->id)
                ->with(['template:id,name,institution_id', 'template.institution:id,name'])
                ->orderBy('created_at', 'desc')
                ->paginate($perPage);

            $exportData = $exports->map(function ($export) {
                return [
                    'id' => $export->id,
                    'template' => $export->template ? [
                        'id' => $export->template->id,
                        'name' => $export->template->name,
                        'institution' => $export->template->institution ? [
                            'id' => $export->template->institution->id,
                            'name' => $export->template->institution->name,
                        ] : null,
                    ] : null,
                    'file_name' => $export->file_name,
                    'file_type' => $export->file_type,
                    'file_url' => $export->file_url,
                    'file_size' => $export->file_size,
                    'file_size_formatted' => $export->formatted_file_size,
                    'total_entries' => $export->total_entries,
                    'total_fields' => $export->total_fields,
                    'status' => $export->status,
                    'error_message' => $export->error_message,
                    'is_expired' => $export->isExpired(),
                    'expires_at' => $export->expires_at?->toIso8601String(),
                    'created_at' => $export->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Your export history retrieved successfully',
                'data' => [
                    'exports' => $exportData,
                    'pagination' => [
                        'current_page' => $exports->currentPage(),
                        'per_page' => $exports->perPage(),
                        'total' => $exports->total(),
                        'last_page' => $exports->lastPage(),
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get user exports: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve your export history'
            ], 500);
        }
    }

    /**
     * Get list of exports for a template
     */
    public function getExportHistory(Request $request, string $templateId)
    {
        try {
            $template = LogbookTemplate::findOrFail($templateId);
            
            // Check access
            $user = $request->user();
            if (!$this->checkUserAccess($user, $template)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view export history'
                ], 403);
            }

            // Get exports from database
            $exports = LogbookExport::where('template_id', $templateId)
                ->with('exporter:id,name,email')
                ->orderBy('created_at', 'desc')
                ->paginate($request->get('per_page', 20));

            $exportData = $exports->map(function ($export) {
                return [
                    'id' => $export->id,
                    'file_name' => $export->file_name,
                    'file_type' => $export->file_type,
                    'file_url' => $export->file_url,
                    'file_size' => $export->file_size,
                    'file_size_formatted' => $export->formatted_file_size,
                    'total_entries' => $export->total_entries,
                    'total_fields' => $export->total_fields,
                    'status' => $export->status,
                    'error_message' => $export->error_message,
                    'exported_by' => $export->exporter ? [
                        'id' => $export->exporter->id,
                        'name' => $export->exporter->name,
                        'email' => $export->exporter->email,
                    ] : null,
                    'is_expired' => $export->isExpired(),
                    'expires_at' => $export->expires_at?->toIso8601String(),
                    'created_at' => $export->created_at->toIso8601String(),
                ];
            });

            return response()->json([
                'success' => true,
                'message' => 'Export history retrieved successfully',
                'data' => [
                    'template_id' => $template->id,
                    'template_name' => $template->name,
                    'exports' => $exportData,
                    'pagination' => [
                        'current_page' => $exports->currentPage(),
                        'per_page' => $exports->perPage(),
                        'total' => $exports->total(),
                        'last_page' => $exports->lastPage(),
                    ]
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Logbook template not found'
            ], 404);
        }
    }

    /**
     * Get single export details
     */
    public function getExportDetail(Request $request, string $exportId)
    {
        try {
            $export = LogbookExport::with(['template', 'exporter:id,name,email'])
                ->findOrFail($exportId);
            
            // Check access
            $user = $request->user();
            if (!$this->checkUserAccess($user, $export->template)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to view this export'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Export details retrieved successfully',
                'data' => [
                    'id' => $export->id,
                    'template' => [
                        'id' => $export->template->id,
                        'name' => $export->template->name,
                    ],
                    'file_name' => $export->file_name,
                    'file_type' => $export->file_type,
                    'file_url' => $export->file_url,
                    'file_size' => $export->file_size,
                    'file_size_formatted' => $export->formatted_file_size,
                    'total_entries' => $export->total_entries,
                    'total_fields' => $export->total_fields,
                    'status' => $export->status,
                    'error_message' => $export->error_message,
                    'exported_by' => $export->exporter ? [
                        'id' => $export->exporter->id,
                        'name' => $export->exporter->name,
                        'email' => $export->exporter->email,
                    ] : null,
                    'ip_address' => $export->ip_address,
                    'is_expired' => $export->isExpired(),
                    'expires_at' => $export->expires_at?->toIso8601String(),
                    'created_at' => $export->created_at->toIso8601String(),
                ]
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export not found'
            ], 404);
        }
    }

    /**
     * Download exported file by export ID
     */
    public function downloadExport(Request $request, string $exportId)
    {
        try {
            $export = LogbookExport::with('template')->findOrFail($exportId);
            
            // Check access
            $user = $request->user();
            if (!$this->checkUserAccess($user, $export->template)) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to download this export'
                ], 403);
            }

            // Check if expired
            if ($export->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This export has expired and is no longer available for download'
                ], 410);
            }

            // Check if file exists
            if (!Storage::disk('public')->exists($export->file_path)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Export file not found on server'
                ], 404);
            }

            // Create audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'DOWNLOAD_EXPORT',
                'description' => "User downloaded export file: {$export->file_name}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            $fullPath = Storage::disk('public')->path($export->file_path);
            
            $contentTypes = [
                'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'pdf' => 'application/pdf',
                'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'csv' => 'text/csv',
            ];
            
            $contentType = $contentTypes[$export->file_type] ?? 'application/octet-stream';
            
            return response()->download($fullPath, $export->file_name, [
                'Content-Type' => $contentType
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export not found'
            ], 404);
        }
    }

    /**
     * Delete a specific export
     * 
     * Authorization:
     * - Super Admin, Admin, Manager: Can delete any export
     * - Institution Admin: Can delete exports within their institution
     * - Template creator: Can delete exports of their templates
     * - Export owner: Can delete their own exports
     */
    public function deleteExport(Request $request, string $exportId)
    {
        try {
            $export = LogbookExport::with('template')->findOrFail($exportId);
            
            $user = $request->user();
            
            // Check deletion permission with proper hierarchy
            $canDelete = false;
            
            // Administrative override - Super Admin, Admin, Manager can delete any export
            if ($user->hasAnyRole(['Super Admin', 'Admin', 'Manager'])) {
                $canDelete = true;
            }
            // Institution Admin can delete exports within their institution
            elseif ($user->hasRole('Institution Admin') && $export->template->institution_id === $user->institution_id) {
                $canDelete = true;
            }
            // Template creator can delete exports of their templates
            elseif ($export->template->created_by === $user->id) {
                $canDelete = true;
            }
            // Export owner can delete their own exports
            elseif ($export->exported_by === $user->id) {
                $canDelete = true;
            }
            
            if (!$canDelete) {
                return response()->json([
                    'success' => false,
                    'message' => 'You do not have permission to delete this export'
                ], 403);
            }

            // Delete file from storage
            if (Storage::disk('public')->exists($export->file_path)) {
                Storage::disk('public')->delete($export->file_path);
            }

            $fileName = $export->file_name;
            
            // Delete record
            $export->delete();

            // Create audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'DELETE_EXPORT',
                'description' => "User deleted export file: {$fileName}",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Export deleted successfully'
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Export not found'
            ], 404);
        }
    }

    /**
     * Delete old export files (cleanup)
     * Can be called via scheduler or manually by admin
     * 
     * Authorization: Super Admin, Admin, Manager, Institution Admin
     */
    public function cleanupExports(Request $request)
    {
        $user = $request->user();
        
        if (!$this->hasAdministrativeOverride($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can cleanup export files'
            ], 403);
        }

        try {
            // Get expired exports
            $expiredExports = LogbookExport::expired()->get();
            $deletedCount = 0;
            $failedCount = 0;

            foreach ($expiredExports as $export) {
                try {
                    // Delete file from storage
                    if (Storage::disk('public')->exists($export->file_path)) {
                        Storage::disk('public')->delete($export->file_path);
                    }
                    
                    // Delete record
                    $export->delete();
                    $deletedCount++;
                } catch (\Exception $e) {
                    $failedCount++;
                    Log::warning('Failed to delete export: ' . $e->getMessage(), [
                        'export_id' => $export->id
                    ]);
                }
            }

            // Also clean up orphan files (files without database records)
            $exportPath = 'export_logbook';
            $files = Storage::disk('public')->files($exportPath);
            $orphanDeleted = 0;
            $threshold = now()->subDays(7);

            foreach ($files as $file) {
                $fileName = basename($file);
                $existsInDb = LogbookExport::where('file_name', $fileName)->exists();
                
                if (!$existsInDb) {
                    $lastModified = Storage::disk('public')->lastModified($file);
                    if (\Carbon\Carbon::createFromTimestamp($lastModified)->lt($threshold)) {
                        Storage::disk('public')->delete($file);
                        $orphanDeleted++;
                    }
                }
            }

            // Create audit log
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'CLEANUP_EXPORTS',
                'description' => "Admin cleaned up {$deletedCount} expired exports and {$orphanDeleted} orphan files",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);

            return response()->json([
                'success' => true,
                'message' => "Cleanup completed successfully",
                'data' => [
                    'expired_exports_deleted' => $deletedCount,
                    'orphan_files_deleted' => $orphanDeleted,
                    'failed_deletions' => $failedCount,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to cleanup exports: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to cleanup export files'
            ], 500);
        }
    }

    /**
     * Get export statistics for admin dashboard
     * 
     * Authorization: Super Admin, Admin, Manager, Institution Admin
     * Note: Institution Admin will see statistics scoped to their institution
     */
    public function getExportStats(Request $request)
    {
        $user = $request->user();
        
        if (!$this->hasAdministrativeOverride($user)) {
            return response()->json([
                'success' => false,
                'message' => 'Only administrators can view export statistics'
            ], 403);
        }

        try {
            $totalExports = LogbookExport::count();
            $completedExports = LogbookExport::where('status', 'completed')->count();
            $failedExports = LogbookExport::where('status', 'failed')->count();
            $totalFileSize = LogbookExport::where('status', 'completed')->sum('file_size');
            $expiredExports = LogbookExport::expired()->count();
            
            // Exports by file type
            $exportsByType = LogbookExport::where('status', 'completed')
                ->selectRaw('file_type, COUNT(*) as count')
                ->groupBy('file_type')
                ->pluck('count', 'file_type');

            // Recent exports (last 7 days)
            $recentExports = LogbookExport::where('created_at', '>=', now()->subDays(7))
                ->where('status', 'completed')
                ->count();

            // Top exporters
            $topExporters = LogbookExport::where('status', 'completed')
                ->selectRaw('exported_by, COUNT(*) as count')
                ->groupBy('exported_by')
                ->orderByDesc('count')
                ->limit(5)
                ->with('exporter:id,name,email')
                ->get()
                ->map(function ($item) {
                    return [
                        'user' => $item->exporter ? [
                            'id' => $item->exporter->id,
                            'name' => $item->exporter->name,
                        ] : null,
                        'count' => $item->count,
                    ];
                });

            return response()->json([
                'success' => true,
                'message' => 'Export statistics retrieved successfully',
                'data' => [
                    'total_exports' => $totalExports,
                    'completed_exports' => $completedExports,
                    'failed_exports' => $failedExports,
                    'expired_exports' => $expiredExports,
                    'total_file_size' => $totalFileSize,
                    'total_file_size_formatted' => $this->formatFileSize($totalFileSize),
                    'exports_by_type' => $exportsByType,
                    'recent_exports_7d' => $recentExports,
                    'top_exporters' => $topExporters,
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to get export stats: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve export statistics'
            ], 500);
        }
    }

    /**
     * Check if user has access to the template
     * 
     * Access hierarchy:
     * 1. Super Admin, Admin, Manager - Full access to all templates
     * 2. Institution Admin - Access to templates within their institution
     * 3. Template creator - Access to their own templates
     * 4. Users with logbook access - Access to templates they have been granted access to
     */
    private function checkUserAccess($user, LogbookTemplate $template): bool
    {
        // Super Admin, Admin, and Manager have full access to all templates
        if ($user->hasAnyRole(['Super Admin', 'Admin', 'Manager'])) {
            return true;
        }

        // Institution Admin has access to templates within their institution
        if ($user->hasRole('Institution Admin')) {
            // Check if template belongs to the same institution as the user
            if ($template->institution_id === $user->institution_id) {
                return true;
            }
        }

        // Check if user is the creator of this template
        if ($template->created_by === $user->id) {
            return true;
        }

        // Check user logbook access (for regular users with granted access)
        $access = $user->logbookAccess()
            ->where('logbook_template_id', $template->id)
            ->first();

        return $access !== null;
    }

    /**
     * Check if user has administrative roles that can override logbook permissions
     * Used for operations that require elevated privileges
     *
     * @param  User  $user
     * @return bool
     */
    private function hasAdministrativeOverride($user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Admin', 'Manager', 'Institution Admin']);
    }

    /**
     * Define document styles
     */
    private function defineStyles(PhpWord $phpWord): void
    {
        // Title style
        $phpWord->addFontStyle('titleStyle', [
            'name' => 'Arial',
            'size' => 18,
            'bold' => true,
            'color' => '1a365d'
        ]);

        // Subtitle style
        $phpWord->addFontStyle('subtitleStyle', [
            'name' => 'Arial',
            'size' => 12,
            'italic' => true,
            'color' => '4a5568'
        ]);

        // Section header style
        $phpWord->addFontStyle('sectionHeader', [
            'name' => 'Arial',
            'size' => 14,
            'bold' => true,
            'color' => '2d3748'
        ]);

        // Normal text style
        $phpWord->addFontStyle('normalText', [
            'name' => 'Arial',
            'size' => 10,
            'color' => '000000'
        ]);

        // Bold text style
        $phpWord->addFontStyle('boldText', [
            'name' => 'Arial',
            'size' => 10,
            'bold' => true,
            'color' => '000000'
        ]);

        // Small text style
        $phpWord->addFontStyle('smallText', [
            'name' => 'Arial',
            'size' => 8,
            'color' => '718096'
        ]);

        // Table header style
        $phpWord->addFontStyle('tableHeader', [
            'name' => 'Arial',
            'size' => 10,
            'bold' => true,
            'color' => 'ffffff'
        ]);

        // Table cell style
        $phpWord->addFontStyle('tableCell', [
            'name' => 'Arial',
            'size' => 9,
            'color' => '000000'
        ]);
    }

    /**
     * Add document header/title
     */
    private function addDocumentHeader($section, LogbookTemplate $template): void
    {
        // Main title
        $section->addText(
            'LOGBOOK EXPORT',
            'titleStyle',
            ['alignment' => Jc::CENTER]
        );

        // Template name as subtitle
        $section->addText(
            $template->name,
            'subtitleStyle',
            ['alignment' => Jc::CENTER]
        );

        // Institution name if exists
        if ($template->institution) {
            $section->addText(
                $template->institution->name,
                'smallText',
                ['alignment' => Jc::CENTER]
            );
        }

        $section->addTextBreak(1);
    }

    /**
     * Add identity table with template information
     */
    private function addIdentityTable($section, LogbookTemplate $template, int $dataCount): void
    {
        $section->addText('INFORMASI LOGBOOK', 'sectionHeader');
        $section->addTextBreak(0);

        // Create identity table
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => 'e2e8f0',
            'cellMargin' => 80,
        ];

        $table = $section->addTable($tableStyle);

        // Define cell styles
        $labelCellStyle = [
            'bgColor' => 'f7fafc',
            'valign' => 'center',
        ];
        $valueCellStyle = [
            'valign' => 'center',
        ];

        // Identity rows
        $identityData = [
            ['Nama Template', $template->name],
            ['Deskripsi', $template->description ?? '-'],
            ['Institution', $template->institution?->name ?? '-'],
            ['Dibuat Oleh', $template->owner?->name ?? '-'],
            ['Tanggal Dibuat', $template->created_at?->format('d F Y, H:i') ?? '-'],
            ['Terakhir Diupdate', $template->updated_at?->format('d F Y, H:i') ?? '-'],
            ['Jumlah Field', $template->fields->count() . ' field'],
            ['Jumlah Data', $dataCount . ' entri'],
        ];

        foreach ($identityData as $row) {
            $tableRow = $table->addRow();
            $tableRow->addCell(3000, $labelCellStyle)->addText($row[0], 'boldText');
            $tableRow->addCell(7000, $valueCellStyle)->addText($row[1], 'normalText');
        }

        $section->addTextBreak(1);
    }

    /**
     * Add data table with logbook entries
     */
    private function addDataTable($section, LogbookTemplate $template, $logbookData): void
    {
        $section->addText('DATA LOGBOOK', 'sectionHeader');
        $section->addTextBreak(0);

        if ($logbookData->isEmpty()) {
            $section->addText('Belum ada data yang dimasukkan ke dalam logbook ini.', 'normalText');
            return;
        }

        // Get fields ordered by name
        $fields = $template->fields->sortBy('name');
        
        // Calculate column widths dynamically
        $totalWidth = 9500; // Total available width in twips
        $numColumn = 800; // Width for No. column
        $metaColumnsWidth = 2400; // Width for meta columns (Writer, Created, Status)
        $remainingWidth = $totalWidth - $numColumn - $metaColumnsWidth;
        
        $fieldCount = $fields->count();
        $fieldColumnWidth = $fieldCount > 0 ? (int)($remainingWidth / $fieldCount) : $remainingWidth;
        
        // Minimum column width
        if ($fieldColumnWidth < 1000) {
            $fieldColumnWidth = 1000;
        }

        // Table style
        $tableStyle = [
            'borderSize' => 6,
            'borderColor' => 'cbd5e0',
            'cellMargin' => 50,
        ];

        $table = $section->addTable($tableStyle);

        // Header row style
        $headerCellStyle = [
            'bgColor' => '4a5568',
            'valign' => 'center',
        ];

        // Add header row
        $headerRow = $table->addRow(400);
        $headerRow->addCell($numColumn, $headerCellStyle)->addText('No.', 'tableHeader', ['alignment' => Jc::CENTER]);
        
        foreach ($fields as $field) {
            $headerRow->addCell($fieldColumnWidth, $headerCellStyle)
                ->addText(ucfirst($field->name), 'tableHeader', ['alignment' => Jc::CENTER]);
        }
        
        // Add meta columns
        $headerRow->addCell(1200, $headerCellStyle)->addText('Penulis', 'tableHeader', ['alignment' => Jc::CENTER]);
        $headerRow->addCell(1200, $headerCellStyle)->addText('Status', 'tableHeader', ['alignment' => Jc::CENTER]);

        // Add data rows
        $rowNum = 1;
        $evenRowStyle = ['bgColor' => 'f7fafc'];
        $oddRowStyle = [];

        foreach ($logbookData as $entry) {
            $rowStyle = $rowNum % 2 === 0 ? $evenRowStyle : $oddRowStyle;
            $dataRow = $table->addRow();
            
            // Row number
            $dataRow->addCell($numColumn, $rowStyle)->addText((string) $rowNum, 'tableCell', ['alignment' => Jc::CENTER]);
            
            // Field values
            $entryData = $entry->data ?? [];
            foreach ($fields as $field) {
                $value = $this->formatFieldValue($entryData[$field->name] ?? '-', $field->data_type);
                $dataRow->addCell($fieldColumnWidth, $rowStyle)->addText($value, 'tableCell');
            }
            
            // Writer
            $dataRow->addCell(1200, $rowStyle)->addText(
                $entry->writer?->name ?? '-',
                'tableCell',
                ['alignment' => Jc::CENTER]
            );
            
            // Status
            $status = $entry->is_verified ? 'Verified' : 'Pending';
            $dataRow->addCell(1200, $rowStyle)->addText($status, 'tableCell', ['alignment' => Jc::CENTER]);
            
            $rowNum++;
        }

        $section->addTextBreak(1);

        // Add summary
        $section->addText(
            "Total: {$logbookData->count()} entri data",
            'smallText'
        );
    }

    /**
     * Format field value based on data type
     */
    private function formatFieldValue($value, string $dataType): string
    {
        if ($value === null || $value === '-') {
            return '-';
        }

        switch ($dataType) {
            case 'date':
                try {
                    return \Carbon\Carbon::parse($value)->format('d/m/Y');
                } catch (\Exception $e) {
                    return (string) $value;
                }

            case 'datetime':
                try {
                    return \Carbon\Carbon::parse($value)->format('d/m/Y H:i');
                } catch (\Exception $e) {
                    return (string) $value;
                }

            case 'time':
                try {
                    return \Carbon\Carbon::parse($value)->format('H:i');
                } catch (\Exception $e) {
                    return (string) $value;
                }

            case 'boolean':
                return $value ? 'Ya' : 'Tidak';

            case 'number':
            case 'integer':
                return number_format((float) $value, 0, ',', '.');

            case 'decimal':
            case 'float':
                return number_format((float) $value, 2, ',', '.');

            case 'array':
            case 'json':
                if (is_array($value)) {
                    return implode(', ', $value);
                }
                return (string) $value;

            case 'image':
            case 'file':
                return '[File]';

            default:
                // Truncate long text
                $stringValue = (string) $value;
                if (strlen($stringValue) > 100) {
                    return substr($stringValue, 0, 97) . '...';
                }
                return $stringValue;
        }
    }

    /**
     * Add document footer
     */
    private function addDocumentFooter($section, $user): void
    {
        $section->addTextBreak(2);

        // Horizontal line
        $section->addText(
            str_repeat('─', 80),
            'smallText'
        );

        // Export info
        $section->addText(
            'Dokumen ini diekspor dari LogGenerator System',
            'smallText'
        );
        $section->addText(
            'Diekspor oleh: ' . $user->name . ' (' . $user->email . ')',
            'smallText'
        );
        $section->addText(
            'Tanggal ekspor: ' . now()->format('d F Y, H:i:s T'),
            'smallText'
        );
        $section->addText(
            'Dokumen ini dibuat secara otomatis dan bersifat rahasia.',
            'smallText'
        );
    }

    /**
     * Generate unique filename for export
     */
    private function generateFilename(LogbookTemplate $template): string
    {
        $safeName = Str::slug($template->name);
        $timestamp = now()->format('Ymd_His');
        $randomStr = Str::random(6);
        
        return "logbook_{$safeName}_{$timestamp}_{$randomStr}.docx";
    }

    /**
     * Generate unique filename for PDF export
     */
    private function generatePdfFilename(LogbookTemplate $template): string
    {
        $safeName = Str::slug($template->name);
        $timestamp = now()->format('Ymd_His');
        $randomStr = Str::random(6);
        
        return "logbook_{$safeName}_{$timestamp}_{$randomStr}.pdf";
    }

    /**
     * Format file size to human readable
     */
    private function formatFileSize(int $bytes): string
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }
}
