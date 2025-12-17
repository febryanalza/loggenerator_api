<?php

namespace App\Mail;

use Illuminate\Support\Facades\Http;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Illuminate\Support\Facades\Log;

/**
 * Brevo API Mail Transport
 * 
 * Custom mail transport yang menggunakan Brevo Transactional Email API
 * Mengatasi masalah port 587 yang diblok oleh cloud provider
 * 
 * @see https://developers.brevo.com/reference/sendtransacemail
 */
class BrevoApiTransport extends AbstractTransport
{
    protected string $apiKey;
    protected string $apiUrl = 'https://api.brevo.com/v3/smtp/email';

    public function __construct(string $apiKey)
    {
        parent::__construct();
        $this->apiKey = $apiKey;
    }

    /**
     * Send email via Brevo API
     */
    protected function doSend(SentMessage $message): void
    {
        $email = $message->getOriginalMessage();

        if (!$email instanceof Email) {
            throw new \InvalidArgumentException('Message must be an instance of Email');
        }

        $from = $email->getFrom()[0] ?? null;
        if (!$from) {
            throw new \RuntimeException('Email must have a sender');
        }

        // Build payload sesuai Brevo API format
        $payload = [
            'sender' => [
                'email' => $from->getAddress(),
                'name' => $from->getName() ?: $from->getAddress(),
            ],
            'to' => $this->formatAddresses($email->getTo()),
            'subject' => $email->getSubject(),
        ];

        // Add HTML content (prioritas utama)
        if ($email->getHtmlBody()) {
            $payload['htmlContent'] = $email->getHtmlBody();
        } elseif ($email->getTextBody()) {
            // Fallback ke text, convert ke HTML
            $payload['htmlContent'] = nl2br(e($email->getTextBody()));
        }

        // Add CC if present
        if ($cc = $email->getCc()) {
            $payload['cc'] = $this->formatAddresses($cc);
        }

        // Add BCC if present
        if ($bcc = $email->getBcc()) {
            $payload['bcc'] = $this->formatAddresses($bcc);
        }

        // Add reply-to if present
        if ($replyTo = $email->getReplyTo()) {
            $replyToAddress = reset($replyTo);
            $payload['replyTo'] = [
                'email' => $replyToAddress->getAddress(),
                'name' => $replyToAddress->getName() ?: $replyToAddress->getAddress(),
            ];
        }

        // Add attachments if present
        $attachments = [];
        foreach ($email->getAttachments() as $attachment) {
            $attachments[] = [
                'content' => base64_encode($attachment->getBody()),
                'name' => $attachment->getPreparedHeaders()->getHeaderParameter('Content-Disposition', 'filename'),
            ];
        }
        if (!empty($attachments)) {
            $payload['attachment'] = $attachments;
        }

        // Send via Brevo API
        $response = Http::withHeaders([
            'accept' => 'application/json',
            'api-key' => $this->apiKey,
            'content-type' => 'application/json',
        ])->post($this->apiUrl, $payload);

        if (!$response->successful()) {
            $errorBody = $response->json();
            $errorMessage = $errorBody['message'] ?? $response->body();
            
            throw new \RuntimeException(
                "Brevo API error ({$response->status()}): {$errorMessage}"
            );
        }

        // Log successful send (optional)
        $responseData = $response->json();
        if (isset($responseData['messageId'])) {
            Log::info("Email sent via Brevo API", [
                'message_id' => $responseData['messageId'],
                'to' => array_map(fn($addr) => $addr['email'], $payload['to']),
                'subject' => $payload['subject'],
            ]);
        }
    }

    /**
     * Format addresses untuk Brevo API
     * 
     * @param array $addresses
     * @return array
     */
    protected function formatAddresses(array $addresses): array
    {
        return array_map(function (Address $address) {
            return [
                'email' => $address->getAddress(),
                'name' => $address->getName() ?: $address->getAddress(),
            ];
        }, $addresses);
    }

    /**
     * Get transport identifier
     */
    public function __toString(): string
    {
        return 'brevo+api';
    }
}
