<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Firebase extends BaseConfig
{
    /**
     * Firebase Configuration Mode
     * Options: 'api_key' or 'service_account'
     * - 'api_key': Simple API Key (from Firebase Console > Cloud Messaging > Server API Key)
     * - 'service_account': OAuth2 using Service Account Private Key (more secure, recommended for production)
     */
    public $mode = 'service_account'; // Using Service Account (from Firebase)

    /**
     * ========================================
     * METHOD 1: API KEY (Simple, for testing)
     * ========================================
     */

    /**
     * Firebase Server API Key
     * Get from: Firebase Console > Project Settings > Cloud Messaging > Server API Key
     * Example: AAAA1bZ5X0E:APA91bH2s_X...
     */
    public $api_key = 'your_fcm_server_api_key_here_min_32_chars'; // Override dari .env via ANDROID_PUSH_API_KEY

    /**
     * Firebase API Endpoint
     */
    public $api_endpoint = 'https://fcm.googleapis.com/fcm/send';

    /**
     * ========================================
     * METHOD 2: SERVICE ACCOUNT (Recommended for Production)
     * ========================================
     */

    /**
     * Firebase Service Account Private Key
     * Get from: Firebase Console > Project Settings > Service Accounts > Generate New Private Key
     * Keep this SECRET! Store in .env or environment variable
     * Override dari .env via FIREBASE_SERVER_KEY
     */
    public $server_key = '';

    /**
     * Firebase Project ID
     * Get from: Firebase Console > Project Settings > Project ID
     * Example: 'absensi-75955'
     * Override dari .env via FIREBASE_PROJECT_ID
     */
    public $project_id = '';

    /**
     * Service Account Email
     * Get from: Firebase Console > Project Settings > Service Accounts
     * Example: 'firebase-adminsdk-fbsvc@absensi-75955.iam.gserviceaccount.com'
     * Override dari .env via FIREBASE_SERVICE_ACCOUNT_EMAIL
     */
    public $service_account_email = '';

    /**
     * Sender ID (Legacy, for compatibility)
     * Get from: Firebase Console > Project Settings > Sender ID
     * Example: '765951121615'
     */
    public $sender_id = '';

    /**
     * OAuth2 Token Endpoint
     */
    public $token_endpoint = 'https://oauth2.googleapis.com/token';

    /**
     * FCM v1 API Endpoint (Google Cloud Messaging new format)
     * Used with Service Account authentication
     */
    public $fcm_v1_endpoint = 'https://fcm.googleapis.com/v1/projects/{project_id}/messages:send';

    /**
     * ========================================
     * COMMON SETTINGS
     * ========================================
     */

    /**
     * Request timeout in seconds
     */
    public $timeout = 10;

    /**
     * Connection timeout in seconds
     */
    public $connect_timeout = 5;

    /**
     * Maximum retry attempts
     */
    public $max_retries = 5;

    /**
     * Enable SSL verification
     */
    public $verify_ssl = true;

    /**
     * Default notification priority
     * Options: 'high' or 'normal'
     */
    public $priority = 'high';

    /**
     * Default TTL (Time To Live) in seconds
     * How long FCM should retry sending if device is offline
     * 0 = Forever, 3600 = 1 hour
     */
    public $ttl = 2592000; // 30 days

    /**
     * Constructor - Load configuration dari environment variables
     */
    public function __construct()
    {
        parent::__construct();

        // Load dari .env atau environment variables
        if ($mode = getenv('FIREBASE_MODE')) {
            $this->mode = $mode;
        }

        if ($key = getenv('ANDROID_PUSH_API_KEY')) {
            $this->api_key = $key;
        }

        if ($key = getenv('FIREBASE_SERVER_KEY')) {
            $this->server_key = str_replace('\\n', "\n", $key);
        }

        if ($projectId = getenv('FIREBASE_PROJECT_ID')) {
            $this->project_id = $projectId;
        }

        if ($email = getenv('FIREBASE_SERVICE_ACCOUNT_EMAIL')) {
            $this->service_account_email = $email;
        }

        if ($senderId = getenv('FIREBASE_SENDER_ID')) {
            $this->sender_id = $senderId;
        }
    }
}
