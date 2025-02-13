<?php

require_once './application/third_party/googleApi/vendor/autoload.php';

use Google\Client;
use Google\Service\Drive;

class Google_Drive_Helper
{
    public function uploadBasic($fileToUpload, $fileName, $fileType, $folder)
    {
        try {
            $client = new Client();
            $client->setAuthConfig(array(
                "type" => "service_account",
                "client_id" => GOOGLE_CLIENT_ID,
                "client_email" => GOOGLE_CLIENT_EMAIL,
                "private_key" => GOOGLE_PRIVATE_KEY,
                "signing_algorithm" => "HS256"
            ));

            $client->useApplicationDefaultCredentials();
            $client->addScope(Drive::DRIVE);
            $driveService = new Drive($client);
            $fileMetadata = new Drive\DriveFile(array(
                'name' => $fileName,
                'parents' => array($folder),
            ));

            $file1 = $driveService->files->create($fileMetadata, array(
                'data' => $fileToUpload,
                'mimeType' => $fileType,
                'uploadType' => 'media',
                'fields' => 'id,webViewLink,webContentLink,thumbnailLink',
                'supportsAllDrives' => true
            ));

            return $file1->id;
        } catch (Exception $e) {
            echo "Error Message: " . $e;
            return false;
        }
    }
}
