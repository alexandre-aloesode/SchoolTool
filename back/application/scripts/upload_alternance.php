<?php
//Script pour fetch la google sheet des étudiants, 
//et ajouter ceux qui ont un contrat avec une entreprise dans la nouvelle table alternance

use Google\Client;
use Google\Service\Drive;
use Google\Service\Drive\DriveFile;


//////VARIABLES A REMPLACER ////////

$db_host = 'localhost:3308';
$db_name = 'laplateforme';
$db_user = 'root';
$db_password = 'root';

//////VARIABLES A REMPLACER ////////

$db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);

$students_found = 0;
$students_with_contract_found = 0;
$students_with_contract_added = 0;
$students_with_contract_already_in_db = 0;
$students_with_contract_failed_to_add = 0;
$emails_not_found = 0;

$students_without_contract_found = 0;

$path = realpath(dirname(__FILE__));
$path = str_replace('scripts', 'third_party/googleApi/', $path);
require_once $path . 'vendor/autoload.php';

try {
    $client = new Client();
    $client->setAuthConfig(array(
        "type" => "service_account",
        "client_id" => "108943179163543703723",
        "client_email" => "atlas-793@atelier-385117.iam.gserviceaccount.com",
        "private_key" =>
        "-----BEGIN PRIVATE KEY-----\nMIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCq1DvgxEWctrXo\nok8A46iVO1oQKfWSsWISMB7Au12QM08VFrlOiROFJi7iP39+U9hVg3vvoLAH29qj\nxkPS9Z6r+yvz15ELlZyTjz623FaEAcustjKgdwrgpd7y3IRI8PbV4ljLr2yZTl89\n0mXwnMcspRzvDAc3ffXG4JeXoYDrG7XLhi4+me2G0ZMLNsMi0j/xW3uO4r0/ioYn\nacXShmtKXKo+ZsaRAyDBVGYYEvLimWHOd9dUdniGjrLW3WeX1lXIeW6JrZu07a/g\nn5ZMy4FxIJp8MPjU+ZLtP6S5BBB1QKBDbquABW7UTJWH9EByWc6EHrf6+WOw0OPJ\nvr2kzNu7AgMBAAECggEAH/9T3eR1k4aHqTTprmV2GMm+s2Ngm6L9FyILSJdvzYhG\nylDw0byMOvbtdjRlmZBz54SSzM4g2r/8Aowq/RNDrrwDvg9FHFHgO6Vrnk3EKMK7\nVzTjY7oYf/6htYTHhHAaE/LrJe/MXCTZYxeLP5fmGL8unbn3ihXeWOuNiXnXT3IZ\nI/FCUnmsLaILpPsd7hU6HPvKdlFEoN0ihjiUOdekgIrePzN8yoVhrIAdjN8rn0Tm\neeAOPBrSvkD/72UVfAkW4Bn00jT6tvzqRK9Jr0KMOCgb6lnhirWjBKquYmQU2yAL\nIZjz4kp5Sz7MPCzfs4v2Je4PCmulz03xLa0jqDwzWQKBgQDkcweABePaF5ES5nhq\nLiGP2xK3vOqlEDiXSj2oUGQUUWpafhP7UJal2PNOw0faMNas9ZIV36m406azaziN\nU1pqq5QO58mK0qo6cvoSBdOa93KfaOoSHQw8tmcWfVuwqCidfzZYtpfx8/r1n5D6\noB29KbwCIUBJlqdbLGaCdALK6QKBgQC/bkeOdIWuAzXWjcWjVFNsFh6RHtrjiOfb\n1lHHmYPL2RAInW3KUs2t2PDcgDU5sq1+A9DDZx41uTsECBLSNxVeNy/IwbrBNbB9\nyfVBvWLVGWXSkPC2LXhT3J1T3r2xXpEY7pLnzu+uVAdEphlt6m7ViLhW+SBGaJ9K\nCcAZy23DAwKBgQCJOrAer2sX72AuQlPDNMLkb2znAozRatUTzH0NRn1X1zBT+7h1\nFwvnxFMj1RqsbvoGG94NVbXWWQ2iaZ4nBxMhUMA30/S1d6baRYcCnI1oYYxxRcyV\n5O0c61UxpUwW6my7b1duIwFTToRKV/f3FYfHwfI2NVMw4VbW5e0OIDItQQKBgQCo\nftQDIrMLoI3B5RXiFnY7PBj5mVQHVNjoWnOvIYOtaMjBHHinzkx1ye7v1vWCbLBi\nq88UP26K0RiOCuEuIQfw3thzd3n/WFeZ0KrMi3szoOBMAAGwCMPR5OyiBvum5FsI\nu+2Ylj/HjPS8ywq1AdU+pNHE1BFBiBM04vIwgiuBqwKBgAWoTUBmdo0vm1SlZJu/\nE0SV53+Ubir0zWLwL/4F8Yi69Rf2LbQ2HdtWgbIdXYfDXrtWzX6fx5j0aX19Jv6u\nsx5Xl3af+j43U2uRDvG2Q5043pgBEu9Zn01BYd3KAUKotcID2+SdUF5XfDlFdQzX\nZ6zDnlLza1WZWEBLc7A4sHZg\n-----END PRIVATE KEY-----\n",
        "signing_algorithm" => "HS256"
    ));

    $client->useApplicationDefaultCredentials();
    $client->setScopes(
        array(
            'https://www.googleapis.com/auth/drive',
            'https://www.googleapis.com/auth/drive.file',
            'https://www.googleapis.com/auth/drive.readonly'
        )
    );
    $driveService = new Drive($client);

    $students_file_id = '1cHN5unvEV4vAHCJZmxIQ4vEDR-kbt2hT_8dfJtsPZUs';
    $results = $driveService->files->export($students_file_id, 'text/csv', array('alt' => 'media'));
    $csv = $results->getBody()->getContents();

    $csv = explode("\n", $csv);
    $clean_array = [];
    foreach ($csv as $key => $value) {
        $data = str_getcsv($value);
        if (!isset($data[1]) || $data[1] == "Civilité" || $data[1] == "") {
            continue;
        } else {
            if ($data[1] == "Monsieur" || $data[1] == "Madame") {
                $students_found++;
                if ($data[8] == "Signé") {
                    $students_with_contract_found++;
                    $clean_array[] = $data;
                }
            }
        }
    }
    foreach ($clean_array as $key => $student) {
        $student_email = $student[4];
        $student_email = str_replace(' ', '', $student_email);
        $student_id_sql = "SELECT id FROM student WHERE email = :email";
        $student_id = $db->prepare($student_id_sql);
        $student_id->execute([
            'email' => $student_email
        ]);
        $student_id = $student_id->fetchAll(PDO::FETCH_ASSOC);

        if (count($student_id) == 0) {
            echo "User not found in student database: " . $student[2] . " " . $student[3] . "\n";
            $emails_not_found++;
            continue;
        }

        $alternant_already_exists_sql = "SELECT id FROM alternance WHERE student_fk = :student_fk";
        $alternant_already_exists = $db->prepare($alternant_already_exists_sql);
        $alternant_already_exists->execute([
            'student_fk' => $student_id[0]['id']
        ]);
        $alternant_already_exists = $alternant_already_exists->fetchAll(PDO::FETCH_ASSOC);

        if (count($alternant_already_exists) > 0) {
            $students_with_contract_already_in_db++;
        } else {
            try {
                $contract_type = $student[7];
                $contract_type = str_replace(' ', '', $contract_type);
                $params = [
                    'contract_type' => $contract_type == "Aprentissage" ? "1" : "2",
                    // 'OPCO' => $student[8],
                    // 'start_date' => $student[9],
                    // 'end_date' => $student[10],
                    'student_fk' => $student_id[0]['id'],
                    'company' => $student[10],
                    // 'tutor_firstname' => $student[11],
                    'tutor_lastname' => $student[11],
                    'tutor_email' => $student[12],
                ];
                $alternance_sql = "INSERT INTO alternance (contract_type, student_fk, company, tutor_lastname, tutor_email) VALUES (:contract_type, :student_fk, :company, :tutor_lastname, :tutor_email)";
                $alternance = $db->prepare($alternance_sql);
                $alternance->execute($params);
                $students_with_contract_added++;
            }
            catch (Exception $e) {
                var_dump($e);
                echo "failed to add student " . $student[2] . " " . $student[3] . "\n";
                $students_with_contract_failed_to_add++;
            }
            // if ($alternance) {
            //     $students_with_contract_added++;
            // } else {
            //     $students_with_contract_failed_to_add++;
            // }
        }
    }
    echo "\n ------------------------------------ \n";
    echo "Students found: " . $students_found . "\n";
    echo "Students with contract found: " . $students_with_contract_found . "\n";
    echo "Students with contract already in table: " . $students_with_contract_already_in_db . "\n";
    echo "Students with contract added: " . $students_with_contract_added . "\n";
    echo $emails_not_found > 0 ? "Students with contract failed to add because email not found: " . $emails_not_found . "\n" : "";
    echo $students_with_contract_failed_to_add > 0 ? "Students with contract failed to add because of database error: " . $students_with_contract_failed_to_add . "\n" : "";
} catch (Exception $e) {
    echo "Error Message: " . $e;
    return false;
}
