<?php

namespace Tests;

use FilesystemIterator;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

final class TlsVerificationTest extends TestCase
{
    public function testApplicationDoesNotDisableTlsVerification(): void
    {
        $patterns = [
            '/CURLOPT_SSL_VERIFYPEER\s*(?:=>|,)\s*(?:false|0)/i',
            '/CURLOPT_SSL_VERIFYHOST\s*(?:=>|,)\s*(?:false|0|1)/i',
            "/['\"]verify(?:_peer|_peer_name)?['\"]\s*=>\s*false/i",
            "/['\"]allow_self_signed['\"]\s*=>\s*true/i",
            '/verify_ssl\s*=\s*false/i',
        ];
        $violations = [];
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(APPPATH, FilesystemIterator::SKIP_DOTS),
        );

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $source = file_get_contents($file->getPathname());
            foreach ($patterns as $pattern) {
                if (preg_match($pattern, $source) === 1) {
                    $violations[] = $file->getPathname();
                    break;
                }
            }
        }

        self::assertSame([], $violations, implode(PHP_EOL, $violations));
    }
}
