<?php
session_start();
requireValidSession();

$currentDate = new DateTime();

$user = $_SESSION['user'];
$selectedPeriodId = $user->id;
$users = null;
if ($user) {
    $users = User::get();
    $selectedUserId = $_POST['user'] ? $_POST['user'] : $user->id;
}

$selectedPeriod = $_POST['period'] ? $_POST['period'] : $currentDate->format('Y-m'); //Filtro do período
$periods = [];
for ($yearDiff = 0; $yearDiff <= 2; $yearDiff++) { //Período de 2 anos antes do atual ou seja, haverá filtro de 2020 até 2022
    $year = date('Y') - $yearDiff;
    for ($month = 12; $month >= 1; $month--) {
        $date = new DateTime("{$year}-{$month}-1");
        $periods[$date->format('Y-m')] = strftime('%B de %Y', $date->getTimestamp());
    }
}

$registries = WorkingHours::getMonthlyReport($selectedUserId, $selectedPeriod);

$report = [];
$workDay = 0;
$sumOfWorkedTime = 0;
//$lastDay = getLastDayOfMonth($currentDate)->format('d');
$selectedDate = (new DateTime($selectedPeriod)); //Seleciona o período de busca
$lastDay = getLastDayOfMonth($selectedPeriod)->format('d');

for ($day = 1; $day <= $lastDay; $day++) { //para dia 1, até ultimo dia do mês
    //$date = $currentDate->format('Y-m') . '-' . sprintf('%02d', $day); 
    $date = $selectedPeriod . '-' . sprintf('%02d', $day); //coleta o período solicitado com a formatação inserindo um 0 no começo
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
    'balance' => "{$sign}{$balance}",
    'selectedPeriod' => $selectedPeriod,
    'periods' => $periods,
    'selectedUserId' => $selectedUserId,
    'users' => $users,
]);
