<?php

namespace App\Mail\Transport;

use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\MessageConverter;
use Illuminate\Support\Facades\Http;

class BrevoTransport extends AbstractTransport
{
    protected $key;

    public function __construct(string $key)
    {
        parent::__construct();
        $this->key = $key;
    }

    protected function doSend(SentMessage $message): void
    {
        $email = MessageConverter::toEmail($message->getOriginalMessage());
        
        $payload = [
            'sender' => [
                'name' => config('mail.from.name'),
                'email' => config('mail.from.address'),
            ],
            'to' => array_map(function ($recipient) {
                return [
                    'email' => $recipient->getAddress(),
                    'name' => $recipient->getName() ?: explode('@', $recipient->getAddress())[0],
                ];
            }, $email->getTo()),
            'subject' => $email->getSubject(),
            'htmlContent' => $email->getHtmlBody(),
        ];

        if ($email->getTextBody()) {
            $payload['textContent'] = $email->getTextBody();
        }

        $response = Http::withHeaders([
            'api-key' => $this->key,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])->post('https://api.brevo.com/v3/smtp/email', $payload);

        if ($response->failed()) {
            throw new \Exception('Brevo API Error: ' . $response->body());
        }
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
