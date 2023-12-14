<?php

namespace Group\ExampleGame\Game;

use Exception;
use Group\ExampleGame\Domain\Database;
use Group\ExampleGame\Models\User;

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

        $this->user = new User();
        $use_mysqli = true;
        $use_sqlite = false;

        if (file_exists('.use_sqlite')) {
            $use_mysqli = false;
            $use_sqlite = true;
        }

        if (!class_exists('mysqli') && $use_mysqli) {
            Helpers::console_log("mysqli не установлен");
            $use_mysqli = false;
            $use_sqlite = true;
            file_put_contents('.use_sqlite', '');
        }

        if (class_exists('mysqli') && $use_mysqli) {
            try {
                $this->db = new Database("mysqli", "localhost", $this->user->username, $this->user->password, 'game');
            } catch (Exception $e) {
                Helpers::console_log("Невозможно подключиться к БД mysql: " . $e->getMessage());
                Helpers::console_log("Будем использовать SQLite!");
                $use_mysqli = false;
                $use_sqlite = true;
                file_put_contents('.use_sqlite', '');
            }
        }

        if ($use_sqlite) {
            print "(c) SQLite3";
            if (!file_exists('mysqlitedb.db')) {
                $this->db = new Database('SQLite3', 'mysqlitedb.db');
                $sql = file_get_contents('create_db.sql');
                $this->db->query($sql);
            } else {
                $this->db = new Database('SQLite3', 'mysqlitedb.db');
            }
            $results = $this->db->query('SELECT 2+2');
            while ($row = $results->fetchArray()) {
                var_dump($row);
            }
        }


        if ($use_sqlite) {
            if (!file_exists('mysqlitedb.db')) {
                $sql = file_get_contents('create_db.sql');
                $this->db->query($sql);
            } else {
                $this->db = new Database('SQLite3', 'mysqlitedb.db');
            }
            $results = $this->db->query('SELECT 2+2');

            while ($row = $results->fetchArray()) {
                var_dump($row);
            }
        }

        $this->authorized = $_SESSION['user'] ?? false;

        if ($this->authorized) {

            $this->user = $_SESSION['user'];

            $query = $this->db->query("select * from users where name='" . $this->user->username . "'");
            if ($data = $query->fetch_assoc()) {
                $this->user->x = $data['x'];
                $this->user->y = $data['y'];
                $this->user->hp = $data['hp'];
                $this->user->setMaxHP($data['hp']);
                $this->user->exp = $data['xp'];
            } else {
                $x = rand(-10, 10);
                $y = rand(-10, 10);
                $hp = 10;
                $xp = 0;
                $name = $this->user->username;
                // userRepository.saveUser($user);
                $this->db->query("insert into users (name,x,y,hp,xp) values ('$name', $x, $y, $hp, $xp)");
                print "Пользователь $name успешно зарегистрирован";
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


    public function show(): void
    {
        require_once 'public/input_form.php';
    }
}
