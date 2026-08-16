<?php

namespace Tests;

use Config\Firebase;
use PHPUnit\Framework\TestCase;

final class FirebaseConfigTest extends TestCase
{
    public function testPrivateKeyLoadsFromEnvironment(): void
    {
        $original = getenv('FIREBASE_SERVER_KEY');
        $encoded = '-----BEGIN PRIVATE KEY-----\nexample\n-----END PRIVATE KEY-----';
        putenv("FIREBASE_SERVER_KEY={$encoded}");

        try {
            self::assertSame(str_replace('\\n', "\n", $encoded), (new Firebase())->server_key);
        } finally {
            $original === false
                ? putenv('FIREBASE_SERVER_KEY')
                : putenv("FIREBASE_SERVER_KEY={$original}");
        }
    }
}
