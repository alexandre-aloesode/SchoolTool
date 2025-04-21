<?php

//////VARIABLES A REMPLACER ////////

$db_host = 'localhost:3308';
$db_name = 'laplateforme';
$db_user = 'root';
$db_password = 'root';

//////VARIABLES A REMPLACER ////////

$db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);

$alerts_without_followup_found = 0;
$alerts_updated = 0;
$applicant_not_found = 0;
$followups_not_found = 0;

$alerts_without_followup_sql = "SELECT id, student_fk, followup_fk, date FROM alert where followup_fk IS NULL";
$alerts_without_followup = $db->prepare($alerts_without_followup_sql);
$alerts_without_followup->execute();
$alerts_without_followup = $alerts_without_followup->fetchAll(PDO::FETCH_ASSOC);

$alerts_without_followup_found = count($alerts_without_followup);

foreach ($alerts_without_followup as $key => $alert) {
    $applicant_id_sql = "SELECT applicant_fk FROM student WHERE id = :student_id";
    $applicant_id = $db->prepare($applicant_id_sql);
    $applicant_id->execute([
        'student_id' => $alert['student_fk']
    ]);
    $applicant_id = $applicant_id->fetchAll(PDO::FETCH_ASSOC);

    if (count($applicant_id) == 0) {
        echo "Applicant not found for alert: " . $alert['id'] . "\n";
        $applicant_not_found++;
        continue;
    }

    $alert_date = $alert['date'];
    $followup_sql = "SELECT id, creation_date, comment, applicant_fk, type FROM followup WHERE applicant_fk = :applicant_fk AND type = :type AND creation_date LIKE '$alert_date%'";
    $followup = $db->prepare($followup_sql);
    $followup->execute([
        'applicant_fk' => $applicant_id[0]['applicant_fk'],
        'type' => 'ALERTE',
    ]);
    $followup = $followup->fetchAll(PDO::FETCH_ASSOC);

    //Les lignes commentées ci-dessous servent à supprimer les éventuels doublons
    // if (count($followup) > 1) {
    //     foreach ($followup as $key => $f) {
    //         if ($key > 0) {
    //             $followup_id = $f['id'];
    //             $delete_followup_sql = "DELETE FROM followup WHERE id = :followup_id";
    //             $delete_followup = $db->prepare($delete_followup_sql);
    //             $delete_followup->execute([
    //                 'followup_id' => $followup_id
    //             ]);
    //         }
    //     }
    // }

    if (count($followup) == 0) {
        if ($applicant_id[0]['applicant_fk'] == 2562)
            echo "Followup not found for alert " . $alert['id'] . " at date " . $alert['date'] . " for applicant " . $applicant_id[0]['applicant_fk'] . "\n";
        $followups_not_found++;
        continue;
    } else {
        $followup_id = $followup[0]['id'];
        $update_alert_sql = "UPDATE alert SET followup_fk = :followup_id WHERE id = :alert_id";
        $update_alert = $db->prepare($update_alert_sql);
        $update_alert->execute([
            'followup_id' => $followup_id,
            'alert_id' => $alert['id']
        ]);
        $alerts_updated++;
    }
}

echo "Alerts without followup found: " . $alerts_without_followup_found . "\n";
echo "Alerts updated: " . $alerts_updated . "\n";
echo "Applicants not found: " . $applicant_not_found . "\n";
echo "Followups not found: " . $followups_not_found . "\n";
exit (0);
