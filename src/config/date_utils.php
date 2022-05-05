<?php

function getDateAsDateTime($date) {//Passa um Date, e ele vira um DateTime
    return is_string($date) ? new DateTime($date) : $date;
}

function isWeekend($date) { //Se for fim de semana, o formato é 'N'
    $inputDate = getDateAsDateTime($date);
    return $inputDate->format('N') >= 6;
}

function isBefore($date1, $date2) { //Dia anterior
    $inputDate1 = getDateAsDateTime($date1);
    $inputDate2 = getDateAsDateTime($date2);
    return $inputDate1 <= $inputDate2;
}

function getNextDay($date) { //Próximo dia
    $inputDate = getDateAsDateTime($date);
    $inputDate->modify('+1 day');
    return $inputDate;
}

function sumIntervals($interval1, $interval2) { //Intervalo de tempo entre o começo e o fim SE MAIOR
    $date = new DateTime('00:00:00');
    $date->add($interval1);
    $date->add($interval2);
    return (new DateTime('00:00:00'))->diff($date);
}

function subtractIntervals($interval1, $interval2) { //Intervalo de tempo entre o começo e o fim SE MENOR
    $date = new DateTime('00:00:00');
    $date->add($interval1);
    $date->sub($interval2);
    return (new DateTime('00:00:00'))->diff($date);  
}

function getDateFromInterval($interval) {//Formato da data
    return new DateTimeImmutable($interval->format('%H:%i:%s'));
}

function getDateFromString($str) { //FOrmato da data como string
    return DateTimeImmutable::createFromFormat('H:i:s', $str);
}

function getFirstDayOfMonth($date) { //Primeiro dia do mês que foi passado como parametro
    $time = getDateAsDateTime($date)->getTimestamp();
    return new DateTime(date('Y-m-1', $time));
}

function getLastDayOfMonth($date) { //Ultimo dia do mês que foi passado como parametro
    $time = getDateAsDateTime($date)->getTimestamp();//quantidade de horas
    return new DateTime(date('Y-m-t', $time)); //retornado o ultimo dia do mês
}

function getSecondsFromDateInterval($interval) { //Tempo do Intervalo em Segundos
    $d1 = new DateTimeImmutable;
    $d2 = $d1->add($interval); //data1 será data2 + intervalo que será mandado como parametro
    return $d2->getTimestamp() - $d1->getTimestamp();
}

function isPastWorkday($date) { //Dia de trabalho no passado
    return !isWeekend($date) && isBefore($date, new DateTime());
}

function getTimeStringFromSeconds($seconds) { //FOrmata os segundos em Horas:Minutos:Segundos
    $h = intdiv($seconds, 3600);
    $m = intdiv($seconds % 3600, 60);
    $s = $seconds - ($h * 3600) - ($m * 60);
    return sprintf('%02d:%02d:%02d', $h, $m, $s);
}

function formatDateWithLocale($date, $pattern) { //
    $time = getDateAsDateTime($date)->getTimestamp();
    return strftime($pattern, $time);
}