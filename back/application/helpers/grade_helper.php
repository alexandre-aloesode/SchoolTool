<?php

class Grade_Helper
{
    public $grades = [
        'En cours' => 0,
        'Echec' => 0,
        'Amateur' => 0.5,
        'Apprenti' => 0.75,
        'Pro' => 1,
        'Expert' => 1.25
    ];
    public $coef = [
        'En cours'=> -1,
        'Echec' => 0,
        'Amateur' => 1,
        'Apprenti' => 2,
        'Pro' => 3,
        'Expert' => 4,
       ];

    public function GetEarned($points, $status)
    {
        $earned = $points * $this->grades[$status];
        return ($earned);
    }

    public function GetCoefValue($points, $status)
    {
        $CoefValue = $points * $this->coef[$status];
        return ($CoefValue);
    }
    public function ExistingGrade($grade)
    {
        if (array_key_exists($grade, $this->grades))
        {
            return (true);
        }
        return (false);
    }

    public function ValueToStatus($value)
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
}

?>