<?php
require 'db.php';

class Game {
    protected $db;
    private $authorized = false;
    private $user;
    private $x, $y, $hp, $xp;
    private $eventManager;

    public function __construct() {
        session_start();
        if (isset($_REQUEST['logout'])) {
            $_SESSION['user'] = false;
        }
        if (isset($_REQUEST['user'])) {
            $_SESSION['user'] = $_REQUEST['user'];
        }
        $db_user = "game";
        $db_pass = "123Game!!!";
        $use_mysqli = true;
        $use_sqlite = false;

        if (file_exists('.use_sqlite')) {
            $use_mysqli = false;
            $use_sqlite = true;
        }

        if (!class_exists('mysqli') && $use_mysqli) {
            print "mysqli не установлен";
            $use_mysqli = false;
            $use_sqlite = true;
            file_put_contents('.use_sqlite', '');
        }

        if (class_exists('mysqli') && $use_mysqli) {
            try {
                $this->db = new DB("mysqli", "localhost", $db_user, $db_pass, 'game');
            } catch (Exception $e) {
                print "Невозвожно подключиться к БД mysql: ".$e->getMessage();
                print "Будем использовать SQLite!";
                $use_mysqli = false;
                $use_sqlite = true;
                file_put_contents('.use_sqlite', '');
            }
        }

        if ($use_sqlite) {
            print "(c) SQLite3";
            if (!file_exists('mysqlitedb.db')) {
                $this->db = new DB('SQLite3', 'mysqlitedb.db');
                $sql = file_get_contents('create_db.sql');
                $this->db->query($sql);
            } else {
                $this->db = new DB('SQLite3', 'mysqlitedb.db');
            }
            $results = $this->db->query('SELECT 2+2');
            while ($row = $results->fetchArray()) {
                var_dump($row);
            }
        }

        $this->authorized = $_SESSION['user'] ?? false;
        if ($this->authorized) {
            $this->user = $_SESSION['user'];
            $query = $this->db->query("select * from users 
        where name='".$this->user."'");
            if ($data = $query->fetch_assoc()) {
                $this->x = $data['x'];
                $this->y = $data['y'];
                $this->hp = $data['hp'];
                $this->xp = $data['xp'];
            } else {
                $x = rand(-10,10);
                $y = rand(-10,10);
                $hp = 10;
                $xp = 0;
                $name = $this->user;
                $this->db->query("insert into users (name,x,y,hp,xp) 
              values ('$name', $x, $y, $hp, $xp)");
                print "О, новый пользователь!";
                $this->x = $x;
                $this->y = $y;
                $this->hp = $hp;
                $this->xp = $xp;
            }
        }
        $this->eventManager = new EventManager($this->db);
    }
    public function authorize() {
        print '
      <form method="post">
      Представьтесь, пожалуйста
      <input type="text" name="user">
      <input type="submit" value="Войти">
      </form>
      ';
        die();
    }
    public function start() {
        if (!$this->authorized) $this->authorize();
        $this->process_actions();
        $this->show();
    }
    public function getClosestDistance() {
        $x = $this->x;
        $y = $this->y;
        $query = $this->db->query("select 
      min((x-$x)*(x-$x)+(y-$y)*(y-$y)) min 
      from users where name!='".$this->user."'");
        return sqrt(($query->fetch_assoc())['min']);
    }

    public function pointInfo($x, $y) {
        $query = $this->db->query("select count(*) n from users where x=$x and y=$y");
        $data = $query->fetch_assoc();
        print_r($data);
        return $data['n'];
    }
    public function process_actions() {
        $this->grantExperienceForTime(); // Начисление опыта за прожитое время

        if (isset($_REQUEST['direction'])) {
            $dir = $_REQUEST['direction'];
            $x = $this->x;
            $y = $this->y;
            switch ($dir) {
                case 'N': {
                    $y++;
                    break;
                }
                case 'S': {
                    $y--;
                    break;
                }
                case 'W': {
                    $x--;
                    break;
                }
                case 'E': {
                    $x++;
                    break;
                }
            }
            $info = $this->pointInfo($x,$y);
            if ($this->pointInfo($x,$y) == 0) {
                $this->db->query("update users set x=$x,y=$y
											                    where name='".$this->user."'");
                $this->x = $x;
                $this->y = $y;
            }

        } else {
            // Взаимодействие с ближайшим врагом при наличии
            if (isset($_REQUEST['attack'])) {
                $this->attackClosestEnemy();
            }
        }
    }
    public function show() {
        print '
            Вы в игре, '.$this->user.'!
            <script src="control.js"></script>
            <form method="post">
            <input type="hidden" name="logout">
            <input type="submit" value="Выйти">
            </form>
        ';
        print "Здоровье: ".$this->hp;
        print "<br>Опыт: ".$this->xp;
        print "<br>X: ".$this->x;
        print "<br>Y: ".$this->y;
        print "<br>Ближайший враг: ".$this->getClosestDistance();
        print '<form method="post">
            <input type="hidden" name="direction" value="N">
            <input type="submit" value="Идти на север">
            </form>
            ';
        print '<form method="post">
            <input type="hidden" name="direction" value="S">
            <input type="submit" value="Идти на юг">
            </form>
            ';
        print '<form method="post">
            <input type="hidden" name="direction" value="E">
            <input type="submit" value="Идти на восток">
            </form>
            ';
        print '<form method="post">
            <input type="hidden" name="direction" value="W">
            <input type="submit" value="Идти на запад">
            </form>
            ';
    }
    public function attackClosestEnemy() {
        $x = $this->x;
        $y = $this->y;

        $query = $this->db->query("SELECT * FROM users WHERE name != '".$this->user."'");
        $closestEnemy = null;
        $closestDistance = PHP_INT_MAX;

        while ($row = $query->fetch_assoc()) {
            $distance = sqrt(pow($x - $row['x'], 2) + pow($y - $row['y'], 2));
            if ($distance < $closestDistance) {
                $closestDistance = $distance;
                $closestEnemy = $row;
            }
        }

        if ($closestEnemy) {
            $enemyName = $closestEnemy['name'];
            $this->db->query("UPDATE users SET hp = hp - 1 WHERE name = '$enemyName'");

            if ($closestEnemy['hp'] <= 0) {
                // Враг убит
                $this->grantExperience(10); // Игрок получает 10 опыта за убийство врага
                print "Вы убили $enemyName!";
            } else {
                $this->grantExperience(1); // Игрок получает 1 опыт за успешную атаку
                print "Вы атаковали $enemyName! У него теперь ".$closestEnemy['hp']." HP.";
            }
        } else {
            print "Вокруг нет врагов.";
        }
    }

    public function grantExperience($amount) {
        $this->xp += $amount;
        $this->db->query("UPDATE users SET xp = $this->xp WHERE name = '$this->user'");
        print "Вы получили $amount опыта!";

        // Проверяем, достиг ли игрок 10 очков опыта
        if ($this->xp >= 10) {
            $this->levelUp();
        }
    }

    public function grantExperienceForTime() {
        $currentTime = time();

        // Получаем время последнего обновления опыта за время
        $lastUpdateTime = $_SESSION['lastUpdateTime'] ?? $currentTime;
        // Получаем время последнего события
        $lastEventTime = $_SESSION['lastEventTime'] ?? $currentTime;

        // Проверяем прошло ли 30 секунд с момента последнего обновления
        if ($currentTime - $lastUpdateTime >= 30) {
            // Обновляем время последнего обновления
            $_SESSION['lastUpdateTime'] = $currentTime;

            // Начисляем всем игрокам на поле единицу опыта
            $this->db->query("UPDATE users SET xp = xp + 1");
            print "Все игроки получили +1 опыта за прожитые 30 секунд!";

            // Проверяем, достиг ли игрок 10 очков опыта
            if ($this->xp >= 10) {
                $this->levelUp();
            }

            // Проверяем прошло ли 20 секунд с момента последнего вызова события
            if ($currentTime - $lastEventTime >= 20) {
                // Обновляем время последнего вызова события
                $_SESSION['lastEventTime'] = $currentTime;

                // Вызываем случайное событие
                $this->eventManager->triggerRandomEvent();
            }
        }
    }

    private function levelUp() {
        $this->xp -= 10; // Уменьшаем опыт на 10 (потому что каждые 10 опыта = +2 к здоровью)
        $this->hp += 2; // Увеличиваем здоровье на 2
        $this->db->query("UPDATE users SET hp = $this->hp WHERE name = '$this->user'");
        print "Вы получили +2 к здоровью за достижение 10 очков опыта!";
    }




}
