<?php declare(strict_types=1);

namespace App;

use App\Models\User;
use Exception;
use Twig\Environment;

final class Game
{
    protected Database $db;
    private bool $authorized = false;
    private User $user;
    private EventManager $eventManager;
	private Environment $twig;

    /**
     * @throws Exception
     */
    public function __construct(Environment $template)
    {
		$this->twig = $template;
        session_start();
        if (isset($_REQUEST['logout'])) {
            $_SESSION['user'] = null;
            $_SESSION['password'] = null;
        }

        if (isset($_REQUEST['user'])) {
            $_SESSION['user'] = $_REQUEST['user'];
        }
        if (isset($_REQUEST['password'])) {
            $_SESSION['password'] = $_REQUEST['password'];
        }

        $dotManager = new DotEnvManager("../.env");
        $dotManager->parse()->load();

        if ($_ENV['GAME_DATABASE'] == DbDriver::MySQL->value) {
            if (class_exists(DbDriver::MySQL->value)) {
                try {
                    $this->db = new Database($_ENV['GAME_DATABASE'], $_ENV['DB_CONNECTION'], $_ENV['DB_USERNAME'], $_ENV['DB_PASSWORD'], $_ENV['DB_TABLE']);
                } catch (Exception $e) {
                    Utils::console_log("Невозможно подключиться к БД " . DbDriver::MySQL->value . ": " . $e->getMessage());
                    $_ENV['GAME_DATABASE'] = DbDriver::SQLite3->value;
                }
            } else {
                Utils::console_log(DbDriver::MySQL->value . " не установлен");
                $_ENV['GAME_DATABASE'] = DbDriver::SQLite3->value;
            }
        }

        if ($_ENV['GAME_DATABASE'] == DbDriver::SQLite3->value) {
            if (class_exists(DbDriver::SQLite3->value)) {
                Utils::console_log("Будем использовать " . DbDriver::SQLite3->name . "!");

                if (!file_exists(SQLITE3_DB)) {
                    $this->db = new Database($_ENV['GAME_DATABASE'], SQLITE3_DB);
                    $sql = file_get_contents(SQLITE3_SCRIPT);
                    $this->db->query($sql);
                } else {
	                $this->db = new Database($_ENV['GAME_DATABASE'], SQLITE3_DB);
                }
            } else {
                Utils::console_log("SQLite3 не установлен");
                print "no database engine provided. Game не будет";
	            Utils::console_log("Не получилось использовать следующие драйверы БД для установки игровой сессии: "
		            . DbDriver::SQLite3->value . ", " . DbDriver::MySQL->value);
	            Utils::console_log("Проверьте php.ini на предмет включенных расширений, наличие соответствующих программных инструментов на сервере или в контейнере");
                die();
            }
        }

        $this->authorized = false;
        if (isset($_SESSION['user']) && isset($_SESSION['password'])) {
            if (is_string($_SESSION['user']) && is_string($_SESSION['password'])) {
                $this->authorized = true;
            }
        }
        if ($this->authorized) {
            $query = $this->db->query("select * from users where name='{$_SESSION['user']}' AND password='{$_SESSION['password']}'");

            if ($data = $query->fetch_assoc()) {
                $this->user = User::create(
                    $data['x'],
                    $data['y'],
                    $data['hp'],
                    $data['xp'],
                    $data['maxHp'],
                    $data['name'],
                    $data['password'],
                    $data['powerAttack'],
                    $data['inventorySize']
                );
            } else {
                $this->user = new User($_SESSION['user'], $_SESSION['password']);
                $this->db->query("insert into users (name, password, xp, hp, maxHp, x, y, powerAttack, inventorySize) values ('{$this->user->name}', '{$this->user->password}', '{$this->user->xp}', '{$this->user->hp}', '{$this->user->maxHp}', '{$this->user->x}', '{$this->user->y}', '{$this->user->powerAttack}', '{$this->user->inventorySize}')");
                print "О, новый пользователь!";
            }
        }
        $this->eventManager = new EventManager($this->db);
    }

    public function authorize()
    {
        print '
      <form method="post">
      Представьтесь, пожалуйста
      <input type="text" name="user">
      <input type="text" name="password">
      <input type="submit" value="Войти">
      </form>
      ';
        die();
    }

    public function start(): void
    {
        if (!$this->authorized) {
            $this->authorize();
        }
        $this->process_actions();
        $this->show();
    }

    public function getClosestDistance(): float|null
    {
        $signX = "-";
        $signY = "-";

        if ($this->user->x < 0) {
            $signX = "+";
        }
        if ($this->user->y < 0) {
            $signY = "+";
        }

        $x = abs($this->user->x);
        $y = abs($this->user->y);

        $query = $this->db->query("select min((x$signX$x)*(x$signX$x)+(y$signY$y)*(y$signY$y)) min from users where name!='{$this->user->name}'");

        $distance = ($query->fetch_assoc())['min'];

        if (is_null($distance)) {
            return null;
        }

        return sqrt($distance);
    }

    public function pointInfo($x, $y)
    {
        $query = $this->db->query("select count(*) n from users where x=$x and y=$y");
        $data = $query->fetch_assoc();
        return $data['n'];
    }

    public function process_actions(): void
    {
        $this->grantExperienceForTime(); // Начисление опыта за прожитое время

        if (isset($_REQUEST['direction'])) {
            $dir = $_REQUEST['direction'];
            $x = $this->user->x;
            $y = $this->user->y;
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
            if ($this->pointInfo($x, $y) == 0) {
                $this->db->query("update users set x=$x,y=$y where name='{$this->user->name}'");
                $this->user->x = $x;
                $this->user->y = $y;
            }

        } else {
            // Взаимодействие с ближайшим врагом при наличии
            if (isset($_REQUEST['attack'])) {
                $this->attackClosestEnemy();
            }
        }
    }

	/**
	 * @throws \Twig\Error\SyntaxError
	 * @throws \Twig\Error\RuntimeError
	 * @throws \Twig\Error\LoaderError
	 */
	public function show(): void
    {
		echo $this->twig->render("user-info.twig", array(
			"user"              => $this->user,
			"closestDistance"   => $this->getClosestDistance(),
		));
        echo $this->twig->render("user-controls.twig");
    }

    public function attackClosestEnemy(): void
    {
        $x = $this->user->x;
        $y = $this->user->y;

        $query = $this->db->query("SELECT * FROM users WHERE name != '" . $this->user->name . "'");
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
                print "Вы атаковали $enemyName! У него теперь " . $closestEnemy['hp'] . " HP.";
            }
        } else {
            print "Вокруг нет врагов.";
        }
    }

    public function grantExperience(int $amount): void
    {
        $this->user->xp += $amount;
        $this->db->query("UPDATE users SET xp = {$this->user->xp} WHERE name = '{$this->user->name}'");
        print "Вы получили $amount опыта!";

        // Проверяем, достиг ли игрок 10 очков опыта
        if ($this->user->xp >= 10) {
            $this->levelUp();
        }
    }

    public function grantExperienceForTime(): void
    {
        $currentTime = time();

        // Получаем время последнего обновления опыта за время
        if (!isset($_SESSION['lastUpdateTime'])) {
            $_SESSION['lastUpdateTime'] = $currentTime;
            $lastUpdateTime = $currentTime;
        } else {
            $lastUpdateTime = $_SESSION['lastUpdateTime'];
        }
        // Получаем время последнего события
        if (!isset($_SESSION['lastEventTime'])) {
            $_SESSION['lastEventTime'] = $currentTime;
            $lastEventTime = $currentTime;
        } else {
            $lastEventTime = $_SESSION['lastEventTime'];
        }



        // Проверяем прошло ли 30 секунд с момента последнего обновления
        if ($currentTime - $lastUpdateTime >= 30) {
            // Обновляем время последнего обновления
            $_SESSION['lastUpdateTime'] = $currentTime;

            // Начисляем всем игрокам на поле единицу опыта
            $this->db->query("UPDATE users SET xp = xp + 1");
            print "Все игроки получили +1 опыта за прожитые 30 секунд!";

            // Проверяем, достиг ли игрок 10 очков опыта
            if ($this->user->xp >= 10) {
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

    private function levelUp(): void
    {
        $this->user->xp -= 10; // Уменьшаем опыт на 10 (потому что каждые 10 опыта = +2 к здоровью)
        $this->user->hp += 2; // Увеличиваем здоровье на 2
        $this->user->maxHp += 2;
        $this->db->query("UPDATE users SET hp={$this->user->hp}, maxHP={$this->user->maxHp} WHERE name = '{$this->user->name}'");
        print "Вы получили +2 к здоровью за достижение 10 очков опыта!";
    }
}
