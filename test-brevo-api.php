<?php

/**
 * Test Email via Brevo API
 * 
 * Usage: php test-brevo-api.php your-email@example.com
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "🧪 Testing Brevo API Email Configuration\n";
echo str_repeat("=", 60) . "\n\n";

// Check configuration
echo "📋 Configuration Check:\n";
echo "MAIL_MAILER: " . config('mail.default') . "\n";
echo "BREVO_API_KEY: " . (config('services.brevo.api_key') ? '***' . substr(config('services.brevo.api_key'), -8) : '❌ NOT SET') . "\n";
echo "MAIL_FROM_ADDRESS: " . config('mail.from.address') . "\n";
echo "MAIL_FROM_NAME: " . config('mail.from.name') . "\n";
echo "\n";

// Validate API key
if (!config('services.brevo.api_key')) {
    echo "❌ BREVO_API_KEY not configured in .env file!\n";
    echo "\n";
    echo "Please add to .env:\n";
    echo "BREVO_API_KEY=xkeysib-your-api-key-here\n";
    exit(1);
}

// Get recipient email
$toEmail = $argv[1] ?? 'febryanalzaqri27@gmail.com';

if (!filter_var($toEmail, FILTER_VALIDATE_EMAIL)) {
    echo "❌ Invalid email address: $toEmail\n";
    exit(1);
}

echo "📧 Sending test email to: $toEmail\n";
echo "⏳ Please wait...\n\n";

try {
    $startTime = microtime(true);
    
    Mail::raw('This is a test email sent via Brevo API from Laravel. If you receive this, the configuration is working correctly!', function($message) use ($toEmail) {
        $message->to($toEmail)
                ->subject('✅ Test Email - Brevo API - ' . now()->format('Y-m-d H:i:s'));
    });
    
    $duration = round((microtime(true) - $startTime) * 1000, 2);
    
    echo "✅ Email sent successfully via Brevo API!\n";
    echo "⏱️  Duration: {$duration}ms\n\n";
    
    echo "Next steps:\n";
    echo "1. ✉️  Check inbox: $toEmail\n";
    echo "2. 📊 Check Brevo logs: https://app.brevo.com/transactional/logs\n";
    echo "3. 📁 If email not received, check spam folder\n";
    echo "4. 🔍 Check Laravel logs: storage/logs/laravel.log\n\n";
    
    echo "🎉 Configuration is working! No need for SMTP port 587.\n";
    
} catch (\Exception $e) {
    echo "❌ Error sending email: " . $e->getMessage() . "\n\n";
    
    echo "Troubleshooting:\n";
    echo "1. Check BREVO_API_KEY in .env file\n";
    echo "2. Verify API key has 'Transactional emails' permission\n";
    echo "3. Get API key from: https://app.brevo.com/settings/keys/api\n";
    echo "4. Run: php artisan config:clear\n";
    echo "5. Check logs: tail -f storage/logs/laravel.log\n\n";
    
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
    
    exit(1);
}
