//Script pour venir MAJ la table students avec le nouveau champ calendar_fk
<?php

//////VARIABLES A REMPLACER ////////

$db_host = 'localhost:3308';
$db_name = 'laplateforme';
$db_user = 'root';
$db_password = 'root';

//////VARIABLES A REMPLACER ////////

$db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);

$students_found = 0;
$students_updated = 0;
$update_errors = 0;

$student_list_sql = "SELECT student.id, applicant.promotion_fk FROM student
INNER JOIN applicant ON student.applicant_fk = applicant.id";
$student_list = $db->prepare($student_list_sql);
$student_list->execute();
$student_list = $student_list->fetchAll(PDO::FETCH_ASSOC);

foreach($student_list as $student) {
    $calendar_fk_sql = "SELECT id FROM calendar WHERE promotion_fk = :promotion_fk";
    $calendar_fk = $db->prepare($calendar_fk_sql);
    $calendar_fk->execute([
        'promotion_fk' => $student['promotion_fk']
    ]);
    $calendar_fk = $calendar_fk->fetchAll(PDO::FETCH_ASSOC);

    if (count($calendar_fk) == 0) {
        echo "Calendar not found for student: " . $student['id'] . "\n";
        $update_errors++;
        continue;
    }

    $update_student_sql = "UPDATE student SET calendar_fk = :calendar_fk WHERE id = :id";
    $update_student = $db->prepare($update_student_sql);
    $update_student->execute([
        'calendar_fk' => $calendar_fk[0]['id'],
        'id' => $student['id']
    ]);

    if ($update_student) {
        $students_updated++;
    } else {
        echo "failed to update student: " . $student['id'] . "\n";
        $update_errors++;
    }
}

echo "\n ------------------------------------ \n";
echo "Students found: " . count($student_list) . "\n";
echo "Students updated: " . $students_updated . "\n";
echo "Errors: " . $update_errors . "\n";
