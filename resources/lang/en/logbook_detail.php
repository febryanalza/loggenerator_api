<?php

return [
    // Page headers
    'page_title' => 'Logbook Detail',
    'page_description' => 'View details and all data within the logbook',
    
    // Back button
    'back' => 'Back',
    
    // Header info
    'created_by' => 'Created by',
    'institution' => 'Institution',
    'date' => 'Date',
    'refresh' => 'Refresh',
    
    // Statistics cards
    'stats' => [
        'total_entries' => 'Total Entries',
        'total_writers' => 'Total Writers',
        'verified_entries' => 'Verified Entries',
        'latest_entry' => 'Latest Entry',
    ],
    
    // Filters
    'filter_writer' => 'Filter Writer',
    'all_writers' => 'All Writers',
    'from_date' => 'From Date',
    'to_date' => 'To Date',
    'filter_status' => 'Filter Status',
    'all_status' => 'All Status',
    'verified' => 'Verified',
    'unverified' => 'Unverified',
    'apply_filter' => 'Apply Filter',
    'reset_filter' => 'Reset Filter',
    'export' => 'Export Data',
    'add_entry' => 'Add Entry',
    
    // Table headers
    'table' => [
        'number' => 'No',
        'entry_date' => 'Entry Date',
        'writer' => 'Writer',
        'data' => 'Data',
        'status' => 'Status',
        'actions' => 'Actions',
    ],
    
    // Entry status
    'status_verified' => 'Verified',
    'status_unverified' => 'Unverified',
    
    // Actions
    'actions_view' => 'View Details',
    'actions_edit' => 'Edit',
    'actions_delete' => 'Delete',
    'actions_verify' => 'Verify',
    'actions_unverify' => 'Unverify',
    
    // Pagination
    'showing' => 'Showing',
    'to' => 'to',
    'of' => 'of',
    'entries' => 'entries',
    'previous' => 'Previous',
    'next' => 'Next',
    
    // Entry Detail Modal
    'detail_title' => 'Entry Detail',
    'detail_writer' => 'Writer',
    'detail_date' => 'Entry Date',
    'detail_status' => 'Status',
    'detail_data' => 'Entry Data',
    'detail_created_at' => 'Created',
    'detail_updated_at' => 'Last Updated',
    'close' => 'Close',
    
    // Create/Edit Entry Modal
    'modal_create_title' => 'Add New Entry',
    'modal_edit_title' => 'Edit Entry',
    'modal_entry_date' => 'Entry Date',
    'modal_data_fields' => 'Entry Data',
    'required_field' => 'Required field',
    'cancel' => 'Cancel',
    'save' => 'Save',
    'update' => 'Update',
    
    // Delete Confirmation Modal
    'delete_title' => 'Confirm Deletion',
    'delete_message' => 'Are you sure you want to delete this entry',
    'delete_warning' => 'This action cannot be undone!',
    'delete_button' => 'Delete',
    
    // Empty state
    'no_data' => 'No entries found',
    'no_data_desc' => 'Click "Add Entry" button to create a new entry',
    
    // Loading
    'loading' => 'Loading data...',
    
    // Messages
    'success_create' => 'Entry successfully created',
    'success_update' => 'Entry successfully updated',
    'success_delete' => 'Entry successfully deleted',
    'success_verify' => 'Entry successfully verified',
    'success_unverify' => 'Entry verification removed',
    'error_load' => 'Failed to load data',
    'error_save' => 'Failed to save entry',
    'error_delete' => 'Failed to delete entry',
    'error_verify' => 'Failed to verify entry',
    'error_not_found' => 'Entry not found',
    'error_export' => 'Failed to export data',
];
