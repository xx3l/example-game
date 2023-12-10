<?php

namespace Group\ExampleGame\Game;

class User
{
    public function __construct(
        public Database $database,
        private int $xp,
        private int $hp,
        private int $x,
        private int $y,
        private string $username = "game",
        private string $password = "123Game!!!"
    )
    {
    }

    public function process_actions(int $difference_x, int $difference_y): void
    {
        if (!isset($_REQUEST['direction']))
        {
            return;
        }

        $dir = $_REQUEST['direction'];

        if (abs($difference_x > 1 || $difference_y > 1)) { return; }

        $temp_x = $this->x + $difference_x;
        $temp_y = $this->y + $difference_y;

        if ($this->isPositionFree($temp_x, $temp_y)) {
            $this->database->query("update users set x=$temp_x, y=$temp_y where name='" . $this->user . "'");
            $this->x = $temp_x;
            $this->y = $temp_y;
        }
    }

    /**
     * Get amount of users at the specified position
     * @param $x
     * @param $y
     * @return mixed
     */
    public function pointInfo($x, $y)
    {
        $query = $this->db->query("select count(*) n from users where x=$x and y=$y");
        $data = $query->fetch_assoc();
        print_r($data);
        return $data['n'];
    }

    public function isPositionFree() : bool
    {
        $query = $this->db->query("select count(*) n from users where x=$x and y=$y");
        $data = $query->fetch_assoc();
        return is_null($data);
    }
}