<?php

namespace Tests;

use App\Helpers\WhatsAppHelper;
use PHPUnit\Framework\TestCase;

final class WhatsAppHelperTest extends TestCase
{
    public function testNetworkFailureIsReportedToTheQueueWorker(): void
    {
        $originalEndpoint = getenv('ONESENDER_API_ENDPOINT');
        $originalKey = getenv('ONESENDER_API_KEY');
        putenv('ONESENDER_API_ENDPOINT=https://127.0.0.1:1');
        putenv('ONESENDER_API_KEY=test-key');

        try {
            self::assertFalse(WhatsAppHelper::sendMessage('08123456789', 'test'));
        } finally {
            $originalEndpoint === false
                ? putenv('ONESENDER_API_ENDPOINT')
                : putenv("ONESENDER_API_ENDPOINT={$originalEndpoint}");
            $originalKey === false
                ? putenv('ONESENDER_API_KEY')
                : putenv("ONESENDER_API_KEY={$originalKey}");
        }
    }
}
