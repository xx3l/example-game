<?php

namespace Group\ExampleGame\Game;

use Exception;
use Group\ExampleGame\Game\Database;
use Group\ExampleGame\Game\User;

class Game
{
    protected Database $db;
    private mixed $authorized = false;
    private User $user;

    public function __construct()
    {
        session_start();

        if (isset($_REQUEST['logout'])) {
            $_SESSION['user'] = false;
        }
        if (isset($_REQUEST['user'])) {
            $_SESSION['user'] = $_REQUEST['user'];
        }

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
                $this->db = new Database("mysqli", "localhost", $db_user, $db_pass, 'game');
            } catch (Exception $e) {
                print "Невозможно подключиться к БД mysql: " . $e->getMessage();
                print "Будем использовать SQLite!";
                $use_mysqli = false;
                $use_sqlite = true;
                file_put_contents('.use_sqlite', '');
            }
        }

        if ($use_sqlite) {
            print "(c) SQLite3";
            $this->db = new Database('SQLite3', 'mysqlitedb.db');

            if (!file_exists('mysqlitedb.db')) {
                $sql = file_get_contents('create_db.sql');
                $this->db->query($sql);
            }

            $results = $this->db->query('SELECT 2+2');

            while ($row = $results->fetchArray()) {
                var_dump($row);
            }
        }

        $this->authorized = $_SESSION['user'] ?? false;

        if ($this->authorized) {

            $this->user = $_SESSION['user'];

            $query = $this->db->query("select * from users where name='" . $this->user . "'");
            if ($data = $query->fetch_assoc()) {
                $this->x = $data['x'];
                $this->y = $data['y'];
                $this->hp = $data['hp'];
                $this->xp = $data['xp'];
            } else {
                $x = rand(-10, 10);
                $y = rand(-10, 10);
                $hp = 10;
                $xp = 0;
                $name = $this->user;
                $this->db->query("insert into users (name,x,y,hp,xp) values ('$name', $x, $y, $hp, $xp)");
                print "Пользователь $name успешно зарегистрирован";
                $this->x = $x;
                $this->y = $y;
                $this->hp = $hp;
                $this->xp = $xp;
            }
        }
    }

    public function authorize(): void
    {
        print '
          <form method="post">
          Представьтесь, пожалуйста
          <input type="text" name="user">
          <input type="submit" value="Войти">
          </form>
      ';
        die();
    }

    public function start(): void
    {
        if (!$this->authorized) $this->authorize();
        $this->process_actions();
        $this->show();
    }

    public function getClosestDistance(): float
    {
        $x = $this->x;
        $y = $this->y;
        $query = $this->db->query("select 
      min((x-$x)*(x-$x)+(y-$y)*(y-$y)) min 
      from users where name!='" . $this->user . "'");
        return sqrt(($query->fetch_assoc())['min']);
    }


    public function show(): void
    {
        // note lol
        require_once 'public/input_form.php';
    }
}
