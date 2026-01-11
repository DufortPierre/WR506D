<?php

namespace App\Service;

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\Writer\PngWriter;

class Google2FAService
{
    private GoogleAuthenticator $googleAuthenticator;

    public function __construct()
    {
        $this->googleAuthenticator = new GoogleAuthenticator();
    }

    public function generateSecret(): string
    {
        return $this->googleAuthenticator->generateSecret();
    }

    public function getQRCodeUrl(string $username, string $secret, string $issuer = 'WR506D'): string
    {
        return GoogleQrUrl::generate($username, $secret, $issuer);
    }

    public function verifyCode(string $secret, string $code): bool
    {
        return $this->googleAuthenticator->checkCode($secret, $code);
    }

    public function generateRecoveryCodes(int $count = 8): array
    {
        $codes = [];
        for ($i = 0; $i < $count; $i++) {
            $codes[] = bin2hex(random_bytes(4)); // 8 caractères hex
        }
        return $codes;
    }
}
