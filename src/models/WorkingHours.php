<?php

class WorkingHours extends Model { // Herda a tabela Model(Banco de dados)
    protected static $tableName = 'working_hours'; //tabela workinh_hours do banco
    protected static $columns = [ //colunas que serão utilizadas
        'id',
        'user_id',
        'work_date',
        'time1',
        'time2',
        'time3',
        'time4',
        'worked_time'
    ];

    public static function loadFromUserAndDate($userId, $workDate) {
        $registry = self::getOne(['user_id' => $userId, 'work_date' => $workDate]);

        if(!$registry) {
            $registry = new WorkingHours([
                'user_id' => $userId,
                'work_date' => $workDate,
                'worked_time' => 0
            ]);
        }

        return $registry;
    }

    public function getNextTime() { //Verifica se bateu os pontos
        if(!$this->time1) return 'time1';
        if(!$this->time2) return 'time2';
        if(!$this->time3) return 'time3';
        if(!$this->time4) return 'time4';
        return null;
    }

    public function getActiveClock() { //Verifica se bateu os pontos
        $nextTime = $this->getNextTime();
        if($nextTime === 'time1' || $nextTime === 'time3') { //SE entrada 1, e entrada 2 ok...
            return 'exitTime'; //...retorna exit time
        } elseif($nextTime === 'time2' || $nextTime === 'time4') { //SE NÃO SE intervalo ou saída ok ...
           return 'workedInterval'; //...retorna intervalo de trabalho
        } else {
            return null;
        }
    }

    public function innout($time) { //Batimento dos pontos
        $timeColumn = $this->getNextTime();
        if(!$timeColumn) { //Se os pontos estiverem preenchidos, informar que já bateu os pontos
            throw new AppException("Você já fez os 4 batimentos do dia!");
        }

        $this->$timeColumn = $time; //Da um SET no ponto correto
        $this->worked_time = getSecondsFromDateInterval($this->getWorkedInterval()); //Pega o intervalo e converte em segundos
        if($this->id) {  
            $this->update();
        } else {
            $this->insert();
        }
    }

    function getWorkedInterval(){ //Fazendo a contagem dos intervalos que o funcionário trabalhou
        [$t1, $t2, $t3, $t4] = $this->getTimes();

        $part1 = new DateInterval('PT0S'); //formatando a data Inicia com P = Período, T= Tempo,  0= = Uma string, S = Segundos
        $part2 = new DateInterval('PT0S');

        if($t1) $part1 = $t1->diff(new DateTime()); //iniciar o tempo
        if($t2) $part1 = $t1->diff($t2); //Pegar diferença entre ponto de entrada e intervalo
        if($t3) $part2 = $t3->diff(new DateTime()); //Pegar diferença entre ida intervalo e volta intervalo
        if($t4) $part2 = $t3->diff($t4); //Pegar diferença entre volta intervalo e saída

        return sumIntervals($part1, $part2); //Somar o valor de Entrada e saída
    }

    function getLunchInterval() { //Verificar horário do almoço
        [, $t2, $t3,] = $this->getTimes();
        $lunchInterval = new DateInterval('PT0S');

        if($t2) $lunchInterval = $t2->diff(new DateTime());
        if($t3) $lunchInterval = $t2->diff($t3);

        return $lunchInterval;
    }

    function getExitTime() { //Horário de saída
        [$t1,,, $t4] = $this->getTimes();
        $workday = DateInterval::createFromDateString('8 hours');

        if(!$t1) {
            return (new DateTimeImmutable())->add($workday);
        } elseif($t4) {
            return $t4;
        } else {
            $total = sumIntervals($workday, $this->getLunchInterval());
            return $t1->add($total);
        }
    }

    function getBalance() {
        if(!$this->time1 && !isPastWorkday($this->work_date)) return '';
        if($this->worked_time == DAILY_TIME) return '-';

        $balance = $this->worked_time - DAILY_TIME;
        $balanceString = getTimeStringFromSeconds(abs($balance));
        $sign = $this->worked_time >= DAILY_TIME ? '+' : '-';
        return "{$sign} {$balanceString}";
    }

    public static function getAbsentUsers() {
        $today = new DateTime(); 
        $result = Database::getResultFromQuery(" 
        SELECT name FROM users
        WHERE end_date is NULL
        AND id NOT IN (
            SELECT user_id FROM working_hours
            WHERE work_date = '{$today->format('Y-m-d')}'
            AND time1 IS NOT NULL
        )
        "); //Verifica todos os usuário que não bateram o ponto no dia atual

        $absentUsers = [];
        if($result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                array_push($absentUsers, $row['name']);
            }
        }

        return $absentUsers;
    }

    public static function getWorkedTimeInMonth($yearAndMonth) {
        $startDate = (new DateTime("{$yearAndMonth}-1"))->format('Y-m-d');
        $endDate = getLastDayOfMonth($yearAndMonth)->format('Y-m-d');
        $result = static::getResultSetFromSelect([
            'raw' => "work_date BETWEEN '{$startDate}' AND '{$endDate}'"
        ], "sum(worked_time) as sum");
        return $result->fetch_assoc()['sum'];
    }

    public static function getMonthlyReport($userId, $date) { //Pega o primeiro dia do mês, até o último dia do mês e cria o relatório
        $registries = [];
        $startDate = getFirstDayOfMonth($date)->format('Y-m-d');
        $endDate = getLastDayOfMonth($date)->format('Y-m-d');
        
        $result = static::getResultSetFromSelect([ //Consulta no banco
            'user_id' => $userId,
            'raw' => "work_date between '{$startDate}' AND '{$endDate}' "
        ]);

        if($result) { //Faço um laço, e o que achar, colocar no $registries
            while($row = $result->fetch_assoc()) {
                $registries[$row['work_date']] = new WorkingHours($row); //instancia de working hours
            }
        }

        return $registries;
    }

    private function getTimes() {
        $times = [];
    
        $this->time1 ? array_push($times, getDateFromString($this->time1)) : array_push($times, null); 
        $this->time2 ? array_push($times, getDateFromString($this->time2)) : array_push($times, null); 
        $this->time3 ? array_push($times, getDateFromString($this->time3)) : array_push($times, null); 
        $this->time4 ? array_push($times, getDateFromString($this->time4)) : array_push($times, null); 
    
        return $times;
    }
}
