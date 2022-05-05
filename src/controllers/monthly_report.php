<?php
session_start();
requireValidSession();

$currentDate = new DateTime();

$user = $_SESSION['user'];

$registries = WorkingHours::getMonthlyReport($user->id, $currentDate);

$report = [];
$workDay = 0;
$sumOfWorkedTime = 0;
$lastDay = getLastDayOfMonth($currentDate)->format('d');

for ($day = 1; $day <= $lastDay; $day++) { //para dia 1, até ultimo dia do mês
    $date = $currentDate->format('Y-m') . '-' . sprintf('%02d', $day); //formatando o dia, com um 0, exemplo: 1/05/2022 > 01/05/2022
    $registry = $registries[$date];

    if (isPastWorkday($date)) $workDay++; //contabilizando as horas do passado

    if ($registry) {
        $sumOfWorkedTime += $registry->worked_time;
        array_push($report, $registry);
    } else {
        array_push($report, new WorkingHours([
            'work_date' => $date,
            'worked_time' => 0
        ]));
    }
}

$expectedTime = $workDay * DAILY_TIME; //Quanto tempo o usuário deve trabalhar
$balance = getTimeStringFromSeconds(abs($sumOfWorkedTime - $expectedTime));
$sign = ($sumOfWorkedTime >= $expectedTime) ? '+' : '-';

loadTemplateView('monthly_report', [
    'report' => $report,
    'sumOfWorkedTime' => getTimeStringFromSeconds($sumOfWorkedTime),
    'balance' => "{$sign}{$balance}"
]);
