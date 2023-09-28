<?php
class Game {
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
        $this->db = new mysqli("localhost", $db_user, $db_pass, 'game');
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

        }
    }
    public function show() {
        print '
            Вы в игре, '.$this->user.'!
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
}
