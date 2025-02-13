<?php

//////VARIABLES A REMPLACER ////////

$db_host = 'localhost:3308';
$db_name = 'laplateforme';
$db_user = 'root';
$db_password = 'root';

//////VARIABLES A REMPLACER ////////


$db = new PDO("mysql:host=$db_host;dbname=$db_name", $db_user, $db_password);
if ($db === false) {
    echo ("Erreur lors de la connexion à la base de données");
    exit (1);
}
$jobs_reviewed = 0;
$jobs_without_correction = 0;
$jobs_succeeded = 0;


function GetEarned($points, $status)
{
    $grades = [
        'En cours' => 0,
        'Echec' => 0,
        'Amateur' => 0.5,
        'Apprenti' => 0.75,
        'Pro' => 1,
        'Expert' => 1.25
    ];
    $earned = $points * $grades[$status];
    return ($earned);
}

function GetCoefValue($points, $status)
{
    $coef = [
        'En cours' => -1,
        'Echec' => 0,
        'Amateur' => 1,
        'Apprenti' => 2,
        'Pro' => 3,
        'Expert' => 4,
    ];
    $CoefValue = $points * $coef[$status];
    return ($CoefValue);
}

function ValueToStatus($value)
{
    if ($value >= 3.3)
        return ('Expert');
    else if ($value >= 2.5)
        return ('Pro');
    else if ($value >= 1.5)
        return ('Apprenti');
    else if ($value >= 0.5)
        return ('Amateur');
    return ('Echec');
}

$registrations_sql = "SELECT * FROM registration WHERE is_complete = :is_complete AND is_success = :is_success";
$registrations = $db->prepare($registrations_sql);
$registrations->execute([
    'is_complete' => '1',
    'is_success' => '0'
]);
$registrations_array = $registrations->fetchAll(PDO::FETCH_ASSOC);

$registrations_succeeded = [];

foreach ($registrations_array as $reg_key => $registration) {
    $registration['total_points'] = 0;
    $registration['total_points_earned'] = 0;
    $registration['jobs'] = [];

    $job_skills_sql = "SELECT * FROM job_skill WHERE job_fk = :job_fk";
    $job_skills = $db->prepare($job_skills_sql);
    $job_skills->execute([
        'job_fk' => $registration['job_fk']
    ]);
    $job_skills = $job_skills->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($job_skills as $job_key => $job) {
        $acquired_skills_sql = "SELECT * FROM acquiered_skill WHERE registration_fk = :registration_fk AND job_skill_fk = :job_skill_fk";
        $acquired_skills = $db->prepare($acquired_skills_sql);
        $acquired_skills->execute([
            'registration_fk' => $registration['id'],
            'job_skill_fk' => $job['id']
        ]);
        $acquired_skills = $acquired_skills->fetchAll(PDO::FETCH_ASSOC);
        if (count($acquired_skills) == 0) {
            $jobs_without_correction++;
            continue;
        }
        $status = $acquired_skills[0]['status'];
        $points = $job['earned'];
        $registration['total_points_earned'] += GetEarned($points, $status);
        $registration['total_points'] += $points;
    }

    if($registration['total_points_earned'] > 0 && $registration['total_points_earned'] / $registration['total_points'] >= 0.5) {
        array_push($registrations_succeeded, $registration);
    }
    $jobs_reviewed++;
}

$result = [];
foreach ($registrations_succeeded as $succeeded_reg) {
    // $registration = new Registration_Model();
    // $trans = $registration->putRegistration(['registration_id' => $group['registration_id'], 'job_is_success' => '1']);
    // array_push($result, $trans);
    $registration_sql = "UPDATE registration SET is_success = :is_success WHERE id = :id";
    $registration = $db->prepare($registration_sql);
    $registration->execute([
        'is_success' => '1',
        'id' => $succeeded_reg['id']
    ]);
    $jobs_succeeded++;
}

echo $jobs_reviewed . " jobs reviewed\n";
echo $jobs_without_correction . " jobs without correction\n";
echo $jobs_succeeded . " jobs updated to success\n";
exit (0);
