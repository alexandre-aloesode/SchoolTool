<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


//////VARIABLES A REMPLACER ////////

$db_host = 'localhost:3308';
$db_name = 'laplateforme';
$db_user = 'root';
$db_password = 'root';

$mail_host = 'ssl0.ovh.net';
$mail_username = 'development@atelier.ovh';
$mail_password = 'W!jWI&G5U12E';

//Pour faire des tests, commenter les lignes 42 à 49 qui 
//empêchent le script de se relancer dans la même journée.

//Puis ne pas oublier de changer le destinataire du mail ligne 310

//////VARIABLES A REMPLACER ////////


$db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
if ($db === false) {
    echo ("Erreur lors de la connexion à la base de données");
    exit(1);
}

$mails_sent = 0;
$mails_failed = 0;
$alerts_created = 0;
$alerts_updated = 0;

//Script qui tous les lundi à 2h se lance via cron et check les temps de logs de la semaine pour les 
// promos qui étaient en semaine de cours.
$last_monday = date('Y-m-d', strtotime('monday this week - 7 days'));
$last_sunday = date('Y-m-d', strtotime('last sunday'));
$students_to_alert = [];
$students_with_previous_unsolved_alert = [];

// Par sécurité on commence en checkant s'il y a des entrées d'alerte à la même date à laquelle nous sommmes 
// pour éviter que le script se répète par accident
$alert_exists_sql = "SELECT id FROM alert WHERE date BETWEEN :date1 AND :date2";
$alert_exists = $db->prepare($alert_exists_sql);
$alert_exists->execute([
    'date1' => date('Y-m-d', strtotime('monday this week')),
    'date2' => date('Y-m-d', strtotime('sunday this week')),
]);
$alert_exists = $alert_exists->fetchAll(PDO::FETCH_ASSOC);
if (count($alert_exists) > 0) {
    echo "Script already ran this week, exiting";
    exit(1);
}

$promotion_sql = "SELECT promotion.id, promotion.name FROM promotion WHERE promotion.is_active = 1";
$promotions = $db->query($promotion_sql)->fetchAll(PDO::FETCH_ASSOC);

foreach ($promotions as $key => $promotion) {
    if ($promotion['name'] == "STAFF" || $promotion['name'] == "Alumni 2019-2021") {
        unset($promotions[$key]);
        continue;
    }

    $promotion_calendar_id_sql = "SELECT id FROM calendar WHERE promotion_fk = :promotion_fk";
    $promotion_calendar_id = $db->prepare($promotion_calendar_id_sql);
    $promotion_calendar_id->execute(['promotion_fk' => $promotion['id']]);
    $promotion_calendar_id = $promotion_calendar_id->fetch(PDO::FETCH_ASSOC);
    if ($promotion_calendar_id !== false) {
        $promotion['calendar_id'] = $promotion_calendar_id['id'];
    } else {
        unset($promotions[$key]);
        continue;
    }

    // if ($promotion['calendar_id'] !== "" || $promotion['calendar_id'] !== null || $promotion['calendar_id'] !== false) {
    if (isset($promotion['calendar_id'])) {
        $promotion_calendar_days_sql = "SELECT day, type FROM calendar_day WHERE calendar_fk = :calendar_fk AND day BETWEEN :date_after AND :date_before";
        // $promotion_calendar_days_sql = "SELECT day, type, FROM calendar_day WHERE calendar_fk = :calendar_fk ";
        $promotion_calendar_days = $db->prepare($promotion_calendar_days_sql);
        $promotion_calendar_days->execute([
            'calendar_fk' => $promotion['calendar_id'],
            'date_after' => $last_monday,
            'date_before' => $last_sunday,
        ]);
        $promotion_calendar_days = $promotion_calendar_days->fetchAll(PDO::FETCH_ASSOC);
    }

    $promotions[$key]['hours_to_do'] = 0;
    $promotions[$key]['hours_minimum_to_do'] = 0;

    if (isset($promotion_calendar_days) && count($promotion_calendar_days) > 0) {
        foreach ($promotion_calendar_days as $day) {
            if ($day['type'] == 1) { //le day_type 1 correspond aux jours de cours
                $promotions[$key]['hours_to_do'] += 6;
                $promotions[$key]['hours_minimum_to_do'] += 2;
            }
        }
    }
    //Si la promotion a 0 "hours_to_do" c'est que les étudiants étaient en entreprise ou en congés
    if ($promotions[$key]['hours_to_do'] == 0) {
        unset($promotions[$key]);
        continue;
    }

    $promotion_students_sql = "SELECT student.id AS student_id, student.email, applicant.id AS applicant_id 
    FROM student INNER JOIN applicant ON student.applicant_fk = applicant.id
    WHERE applicant.promotion_fk = :promotion_fk";
    $promotion_students = $db->prepare($promotion_students_sql);
    $promotion_students->execute(['promotion_fk' => $promotion['id']]);
    $promotion_students = $promotion_students->fetchAll(PDO::FETCH_ASSOC);

    if (count($promotion_students) == 0) {
        unset($promotions[$key]);
        continue;
    }
    $promotions[$key]['students'] = $promotion_students;

    foreach ($promotions[$key]['students'] as $student_key => $student) {

        $student_logtimes_sql = "SELECT algo2, day AS logtime_day FROM logtime WHERE student_fk = :student_fk AND day BETWEEN :date_after AND :date_before";
        $student_logtimes = $db->prepare($student_logtimes_sql);
        $student_logtimes->execute([
            'student_fk' => $student['student_id'],
            'date_after' => $last_monday,
            'date_before' => $last_sunday,
        ]);
        $student_logtimes = $student_logtimes->fetchAll(PDO::FETCH_ASSOC);
        // if (count($student_logtimes) == 0) {
        //     unset($promotions[$key]['students'][$student_key]);
        //     continue;
        // }

        $student_logtimes_events_sql = "SELECT duration FROM logtime_event WHERE student_fk = :student_fk AND logtime_date BETWEEN :date_after AND :date_before";
        $student_logtimes_events = $db->prepare($student_logtimes_events_sql);
        $student_logtimes_events->execute([
            'student_fk' => $student['student_id'],
            'date_after' => $last_monday,
            'date_before' => $last_sunday,
        ]);
        $student_logtimes_events = $student_logtimes_events->fetchAll(PDO::FETCH_ASSOC);

        $promotions[$key]['students'][$student_key]['hours_done_algo_2'] = 0;
        if (count($student_logtimes) > 0) {
            foreach ($student_logtimes as $logtime) {
                $promotions[$key]['students'][$student_key]['hours_done_algo_2'] += $logtime['algo2'] / 60;
            }
        }
        if (count($student_logtimes_events) > 0) {
            foreach ($student_logtimes_events as $logtime_event) {
                $promotions[$key]['students'][$student_key]['hours_done_algo_2'] += $logtime_event['duration'] / 60;
            }
        }

        $student_has_alert_sql = "SELECT id AS alert_id, level AS alert_level, status AS alert_status, date AS alert_date 
        FROM alert WHERE student_fk = :student_fk AND date < :date_before
        ORDER BY date DESC LIMIT 1";
        $student_has_alert = $db->prepare($student_has_alert_sql);
        $student_has_alert->execute([
            'student_fk' => $student['student_id'],
            // 'date_after' => date('Y-m-d', strtotime($last_monday . ' - 1 days')),
            'date_before' => $last_monday,
        ]);
        $student_has_alert = $student_has_alert->fetchAll(PDO::FETCH_ASSOC);

        if (count($student_has_alert) > 0) {
            $promotions[$key]['students'][$student_key]['previous_alert_id'] = $student_has_alert[0]['alert_id'];
            $promotions[$key]['students'][$student_key]['previous_alert'] = $student_has_alert[0]['alert_level'];
            $promotions[$key]['students'][$student_key]['previous_alert_date'] = $student_has_alert[0]['alert_date'];
            $promotions[$key]['students'][$student_key]['previous_alert_status'] = $student_has_alert[0]['alert_status'];
            if ($student_has_alert[0]['alert_status'] == 2) {
                //Les alertes de status 2 sont les alertes non résolues, et doivent $être repassées en cours la semaine d'après
                array_push($students_with_previous_unsolved_alert, $promotions[$key]['students'][$student_key]);
            }
        }

        if (($promotions[$key]['students'][$student_key]['hours_done_algo_2']) < $promotions[$key]['hours_to_do']) {
            $promotions[$key]['students'][$student_key]['hours_to_do'] = $promotions[$key]['hours_to_do'];
            $promotions[$key]['students'][$student_key]['hours_minimum_to_do'] = $promotions[$key]['hours_minimum_to_do'];

            //Si l'étudiant a moins d'heures que le minimum à faire, on check si il a eu des absences validées
            $student_absences_sql = "SELECT duration FROM absence WHERE student_fk = :student_fk AND status = 1 
            AND start_date <= :date_before AND end_date >= :date_after";
            $student_absences = $db->prepare($student_absences_sql);
            $student_absences->execute([
                'student_fk' => $student['student_id'],
                'date_after' => $last_monday,
                'date_before' => $last_sunday,
            ]);
            $student_absences = $student_absences->fetchAll(PDO::FETCH_ASSOC);

            if (count($student_absences) > 0) {
                foreach ($student_absences as $absence) {
                    $promotions[$key]['students'][$student_key]['hours_to_do'] -= ($absence['duration'] * 6);
                    $promotions[$key]['students'][$student_key]['hours_minimum_to_do'] -= ($absence['duration'] * 2);
                }
            }

            if (isset($student['previous_alert'])) {
                //Si une alerte est trouvée on doit vérifier si celle-ci correspond bien à la semaine précédente de cours
                $verify_days_type_sql = "SELECT day FROM calendar_day WHERE calendar_fk = :calendar_fk AND type = 1 AND day BETWEEN :date_after AND :date_before";
                $verify_days_type = $db->prepare($verify_days_type_sql);
                $verify_days_type->execute([
                    'calendar_fk' => $promotion['calendar_id'],
                    'date_after' => $student['previous_alert_date'],
                    'date_before' => date('Y-m-d', strtotime($last_monday . ' - 1 days')),
                ]);
                $verify_days_type = $verify_days_type->fetchAll(PDO::FETCH_ASSOC);

                if (count($verify_days_type) == 0) {
                    // il n'y a pas de jours de cours entre la date de la dernière alerte trouvée et la date actuelle, on est donc bien dans le cas
                    // où l'étudiant n'a pas assez d'heures de logtime pour la semaine précédente de cours
                    $promotions[$key]['students'][$student_key]['previous_alert'] = $student_was_on_alert[0]['alert_level'];
                    $promotions[$key]['students'][$student_key]['previous_alert_status'] = $student_was_on_alert[0]['alert_status'];
                }
            }

            //après avoir vérifié le nombre de jours de cours, les temps de log et les absences validées, 
            //si l'étudiant n'a pas un temps de présence suffisant, on l'ajoute au tableau des étudiants à alerter
            if ($promotions[$key]['students'][$student_key]['hours_done_algo_2'] < $promotions[$key]['students'][$student_key]['hours_to_do']) {
                array_push($students_to_alert, $promotions[$key]['students'][$student_key]);
                //Les étudiants en temps de log insuffisant seront traités séparément plus bas, on le supprime donc du tableau des étudiants dont il faudra vérifier l'alerte
                foreach ($students_with_previous_unsolved_alert as $key2 => $student2) {
                    if ($student2['student_id'] == $promotions[$key]['students'][$student_key]['student_id']) {
                        unset($students_with_previous_unsolved_alert[$key2]);
                    }
                }
            }
        }
    }
}

foreach ($students_with_previous_unsolved_alert as $student) {
    //Le but ici est de vérifier si l'étudiant avait une alerte de niveau 2 en statut non résolu. Si c'est le cas, il faut la repasser en statut en cours
    if ($student['previous_alert'] == 2 && $student['previous_alert_status'] == 2) {
        $alert_status_sql = "UPDATE alert SET status = 0 WHERE id = :id";
        $alert_status = $db->prepare($alert_status_sql);
        $alert_status->execute(['id' => $student['previous_alert_id']]);
    }
    $alerts_updated++;
}

foreach ($students_to_alert as $student) {
    $second_warning_in_a_row = false;
    $create_new_alert = true;
    $level = $student['hours_done_algo_2'] >= $student['hours_minimum_to_do'] ? 1 : 2;

    if (isset($student['previous_alert'])) {
        if ($student['previous_alert'] == 1) {
            //Si l'étudiant a déjà reçu un avertissement la semaine dernière et qu'il en reçoit un autre, l'alerte passe en RDV Péda
            if ($level == 1) $second_warning_in_a_row = true;
            $level = 2;
        }
        if ($student['previous_alert'] == 2 && $student['previous_alert_status'] == 0) {
            //Si l'étudiant a déjà une alerte de niveau 2 en cours on ne fait rien
            $create_new_alert = false;
        }
        if ($student['previous_alert'] == 2 && $student['previous_alert_status'] == 2) {
            //Si l'étudiant a déjà une alerte de niveau 2 non résolue on crée une alerte niveau 3 en cours
            $level = 3;
        }
        if ($student['previous_alert'] == 3) {
            //Si l'étudiant a déjà une alerte de niveau 3 on ne fait rien
            $create_new_alert = false;
        }
    }

    if ($create_new_alert == true) {
        $level_to_string = $level == 3 ? 'RAR' : ($level == 2 ? 'RDV Péda' : "avertissement");
        $status = $level == 1 ? 1 : 0;
        $comment =
            $second_warning_in_a_row == false ?
            'A reçu un ' . $level_to_string . ' car il a réalisé ' . ($student['hours_done_algo_2']) . 'h sur ' . ($student['hours_to_do'])
            :
            'A reçu un ' . $level_to_string . ' car il a réalisé ' . ($student['hours_done_algo_2']) . 'h sur ' . ($student['hours_to_do']) . 'h et qu\'il avait déjà reçu un avertissement la semaine dernière';

        $followup_sql = "INSERT INTO followup (creation_date, applicant_fk, author, comment, type) VALUES (:creation_date, :applicant_fk, :author, :comment, :type)";
        $followup = $db->prepare($followup_sql);
        $followup->execute([
            'creation_date' => date('Y-m-d H:i:s'),
            'applicant_fk' => $student['applicant_id'],
            'author' => 'deepthought@laplateforme.io',
            'comment' => $comment,
            'type' => '4',
        ]);

        $followup_id = $db->lastInsertId();

        $alert_sql = "INSERT INTO alert (student_fk, date, level, status, followup_fk) VALUES (:student_fk, :date, :level, :status, :followup_fk)";
        $alert = $db->prepare($alert_sql);
        $alert->execute([
            'student_fk' => $student['student_id'],
            'date' => date('Y-m-d'),
            'level' => $level,
            'status' => $status,
            'followup_fk' => $followup_id,
        ]);

        $alerts_created++;
    }

    // $path = '/home/alex/Github/plateforme/stack-plateforme-interne/API-LaPlateforme/application/third_party/phpMailer';
    // require_once $path . '/PHPMailer.php';
    // require_once $path . '/SMTP.php';
    // require_once $path . '/Exception.php';

    // $subject = 'Alerte temps de log';
    // $message = 'Bonjour,<br><br>
    //         Par ce mail, nous vous informons que votre temps de connexion de la semaine dernière est insuffisant.<br>               
    //         Nous vous rappelons que vous avez une obligation de présence de 35 heures par semaine en formation.<br>               
    //         Cette notification est transmise à votre responsable pédagogique, qui prendra contact avec vous.<br><br>
    //         Bien à vous,<br><br>
    //         <b>Equipe Administrative</b><br>
    //         @<a href="https://www.laplateforme.io/">LaPlateforme.io</a><br>
    //         04.84.89.43.69<br>
    //         <img src="cid:logo" alt="logo" style="width:200px"; height:100px">
    //         <p style="color:#263F92"><b>Le Campus Méditerranéen du Numérique</b><p><br>
    //         ';

    // $mail = new PHPMailer(true);

    // try {
    //     //Server settings
    //     $mail->SMTPDebug = SMTP::DEBUG_SERVER;                      //Enable verbose debug output
    //     $mail->isSMTP();                                            //Send using SMTP
    //     $mail->Host       = $mail_host;                           //Set the SMTP server to send through
    //     $mail->SMTPAuth   = true;                                   //Enable SMTP authentication
    //     $mail->Username   = $mail_username;                             //SMTP username
    //     $mail->Password   = $mail_password;                               //SMTP password                            //SMTP password
    //     $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         //Enable implicit TLS encryption
    //     $mail->Port       = 587;                                    //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`

    //     //Recipients
    //     $mail->setFrom($mail_username, 'Service Administration');
    //     $mail->addAddress(
    //         $student['email'],
    //         // 'alexandre.aloesode@laplateforme.io',
    //         //  'Joe User'
    //     );

    //     $mail->AddEmbeddedImage($path . 'logo.png', 'logo');

    //     //Content
    //     $mail->isHTML(true);                                  //Set email format to HTML
    //     $mail->Subject = $subject;
    //     $mail->Body = $message;

    //     $mail->send();
    //     echo ("Mail has been sent to " . $student['email'] . "\n");
    //     $mails_sent++;
    // } catch (Exception $e) {
    //     echo ("Mail could not be sent to " . $student['email'] . "Mailer Error: {$mail->ErrorInfo} \n");
    //     $mails_failed++;
    // }
}

echo (" \n Script ran successfully, " . $alerts_created . " new alerts, " . $alerts_updated . " alerts updated, " . $mails_sent . " mails sent, and " . $mails_failed . " mails failed \n");
exit(0);
