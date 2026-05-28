<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GoogleDriveService
{
    protected $clientEmail;
    protected $privateKey;
    protected $parentFolderId;
    public $lastError = null;

    public function __construct()
    {
        $this->clientEmail = config('services.google_drive.client_email');
        $this->privateKey = config('services.google_drive.private_key');
        
        $folderId = config('services.google_drive.parent_folder_id');
        // Jika pengguna memasukkan URL lengkap, bersihkan secara otomatis untuk mengambil ID foldernya saja
        if (!empty($folderId) && strpos($folderId, 'drive.google.com') !== false) {
            if (preg_match('/folders\/([a-zA-Z0-9-_]+)/', $folderId, $matches)) {
                $folderId = $matches[1];
            }
        }
        $this->parentFolderId = $folderId;
    }

    /**
     * Generate base64Url encoding.
     */
    private function base64UrlEncode($data)
    {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    /**
     * Generate an OAuth2 access token using the Service Account JWT flow.
     * 
     * @return string|null The access token or null on failure
     */
    public function getAccessToken()
    {
        if (empty($this->clientEmail) || empty($this->privateKey)) {
            Log::warning('Google Drive service account credentials are not fully configured in .env.');
            return null;
        }

        // Handle newlines in private key securely
        $privateKey = str_replace('\n', "\n", $this->privateKey);
        
        // Ensure private key headers are correct
        if (strpos($privateKey, '-----BEGIN PRIVATE KEY-----') === false) {
            $privateKey = "-----BEGIN PRIVATE KEY-----\n" . trim($privateKey) . "\n-----END PRIVATE KEY-----";
        }

        $now = time();
        $header = $this->base64UrlEncode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64UrlEncode(json_encode([
            'iss' => $this->clientEmail,
            'scope' => 'https://www.googleapis.com/auth/drive.file',
            'aud' => 'https://oauth2.googleapis.com/token',
            'exp' => $now + 3600,
            'iat' => $now
        ]));

        $signature = '';
        $success = openssl_sign("$header.$payload", $signature, $privateKey, OPENSSL_ALGO_SHA256);

        if (!$success) {
            $this->lastError = 'Gagal tanda tangani JWT (Check GOOGLE_DRIVE_PRIVATE_KEY)';
            Log::error('Google Drive authentication failed: Unable to sign JWT. Please check if GOOGLE_DRIVE_PRIVATE_KEY is a valid private key.');
            return null;
        }

        $signature = $this->base64UrlEncode($signature);
        $assertion = "$header.$payload.$signature";

        try {
            $response = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion' => $assertion,
            ]);

            if ($response->successful()) {
                return $response->json('access_token');
            }

            $this->lastError = 'OAuth Token Request Failed: ' . $response->body();
            Log::error('Google Drive OAuth token request failed: ' . $response->body());
        } catch (\Exception $e) {
            $this->lastError = 'OAuth Connection Error: ' . $e->getMessage();
            Log::error('Google Drive OAuth connection error: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get or create a folder in Google Drive.
     * 
     * @param string $folderName Name of the folder to find/create
     * @param string|null $parentFolderId Parent folder ID
     * @return string|null The folder ID or null on failure
     */
    public function getOrCreateFolder($folderName, $parentFolderId = null)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return null;
        }

        $folderId = $parentFolderId ?: $this->parentFolderId;
        if (empty($folderId)) {
            Log::warning('Google Drive root parent folder ID is not configured.');
            return null;
        }
        
        // Clean up single quotes for query safety
        $escapedFolderName = str_replace("'", "\\'", $folderName);

        // Step 1: Search if folder already exists
        $query = "name = '{$escapedFolderName}' and mimeType = 'application/vnd.google-apps.folder' and trashed = false and '{$folderId}' in parents";

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
            ])->get('https://www.googleapis.com/drive/v3/files', [
                'q' => $query,
                'spaces' => 'drive',
                'fields' => 'files(id, name)',
            ]);

            if ($response->successful()) {
                $files = $response->json('files');
                if (!empty($files) && isset($files[0]['id'])) {
                    return $files[0]['id'];
                }
            } else {
                $this->lastError = "Gagal mencari folder '{$folderName}': " . $response->body();
                Log::error("Search folder '{$folderName}' in Google Drive failed: " . $response->body());
            }
        } catch (\Exception $e) {
            $this->lastError = "Koneksi pencarian folder '{$folderName}' error: " . $e->getMessage();
            Log::error("Search folder '{$folderName}' Google Drive connection error: " . $e->getMessage());
        }

        // Step 2: Folder does not exist, create it
        try {
            $metadata = [
                'name' => $folderName,
                'mimeType' => 'application/vnd.google-apps.folder',
                'parents' => [$folderId],
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'application/json',
            ])->post('https://www.googleapis.com/drive/v3/files', $metadata);

            if ($response->successful()) {
                $newFolderId = $response->json('id');
                Log::info("Successfully created folder '{$folderName}' in Google Drive with ID: {$newFolderId}");
                return $newFolderId;
            }

            $this->lastError = "Gagal membuat folder '{$folderName}': " . $response->body();
            Log::error("Create folder '{$folderName}' in Google Drive failed: " . $response->body());
        } catch (\Exception $e) {
            $this->lastError = "Koneksi pembuatan folder '{$folderName}' error: " . $e->getMessage();
            Log::error("Create folder '{$folderName}' Google Drive connection error: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Upload a file into a dynamically resolved nested folder structure in Google Drive.
     * 
     * @param string $filePath Full path to the file on local disk
     * @param string $fileName Target file name on Google Drive
     * @param array $folderPathArray Array of nested folder names (e.g. ['2026', 'Futsal', '28-05-2026'])
     * @return string|null The uploaded Google Drive file ID, or null on failure
     */
    public function uploadFileToNestedFolders($filePath, $fileName, array $folderPathArray)
    {
        $currentParentId = $this->parentFolderId;
        
        if (empty($currentParentId)) {
            Log::warning('Skipping Google Drive upload: Root folder ID is not configured in .env.');
            return null;
        }

        // Dynamically resolve/create each folder level
        foreach ($folderPathArray as $folderName) {
            $folderId = $this->getOrCreateFolder($folderName, $currentParentId);
            if (!$folderId) {
                Log::error("Failed to resolve or create folder '{$folderName}' in Google Drive. Uploading to current parent ID '{$currentParentId}'.");
                break;
            }
            $currentParentId = $folderId;
        }

        // Upload the file to the deepest resolved folder ID
        return $this->uploadFile($filePath, $fileName, $currentParentId);
    }

    /**
     * Upload a file to Google Drive.
     * 
     * @param string $filePath Full path to the file on local disk
     * @param string $fileName Target file name on Google Drive
     * @param string|null $parentFolderId Optional specific parent folder ID
     * @return string|null The Google Drive file ID, or null on failure
     */
    public function uploadFile($filePath, $fileName, $parentFolderId = null)
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::warning('Skipping Google Drive upload: Access token could not be generated.');
            return null;
        }

        if (!file_exists($filePath)) {
            Log::error("Local file does not exist for Google Drive upload: {$filePath}");
            return null;
        }

        $folderId = $parentFolderId ?: $this->parentFolderId;

        // Step 1: Create metadata for multipart upload
        $metadata = [
            'name' => $fileName,
        ];
        if (!empty($folderId)) {
            $metadata['parents'] = [$folderId];
        }

        $mimeType = mime_content_type($filePath) ?: 'application/octet-stream';
        $fileData = file_get_contents($filePath);

        $boundary = '-------314159265358979323846';
        $delimiter = "\r\n--" . $boundary . "\r\n";
        $closeDelimiter = "\r\n--" . $boundary . "--";

        $multipartBody = $delimiter
            . "Content-Type: application/json; charset=UTF-8\r\n\r\n"
            . json_encode($metadata)
            . $delimiter
            . "Content-Type: " . $mimeType . "\r\n"
            . "Content-Transfer-Encoding: base64\r\n\r\n"
            . base64_encode($fileData)
            . $closeDelimiter;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type' => 'multipart/related; boundary=' . $boundary,
            ])->withBody($multipartBody, 'multipart/related')
              ->post('https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart');

            if ($response->successful()) {
                $fileId = $response->json('id');
                Log::info("Successfully uploaded file '{$fileName}' to Google Drive with ID: {$fileId}");
                return $fileId;
            }

            $this->lastError = "Gagal mengunggah berkas '{$fileName}': " . $response->body();
            Log::error('Google Drive file upload failed: ' . $response->body());
        } catch (\Exception $e) {
            $this->lastError = "Koneksi unggah berkas '{$fileName}' error: " . $e->getMessage();
            Log::error('Google Drive upload connection error: ' . $e->getMessage());
        }

        return null;
    }
}
