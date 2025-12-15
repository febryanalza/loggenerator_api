<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Mail;

echo "Testing email configuration...\n";
echo "MAIL_MAILER: " . config('mail.default') . "\n";
echo "MAIL_HOST: " . config('mail.mailers.smtp.host') . "\n";
echo "MAIL_PORT: " . config('mail.mailers.smtp.port') . "\n";
echo "MAIL_USERNAME: " . config('mail.mailers.smtp.username') . "\n";
echo "MAIL_FROM_ADDRESS: " . config('mail.from.address') . "\n\n";

echo "Sending test email to febryanalzaqri27@gmail.com...\n";

try {
    Mail::raw('This is a test email from LogGenerator API', function($message) {
        $message->to('febryanalzaqri27@gmail.com')
                ->subject('Test Email - LogGenerator');
    });
    
    echo "✅ Email sent successfully!\n";
    echo "Check your inbox: febryanalzaqri27@gmail.com\n";
} catch (Exception $e) {
    echo "❌ Error sending email: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}
