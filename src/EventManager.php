<?php declare(strict_types=1);

namespace App;

final class EventManager
{
    public function __construct(protected Database $db){}

    private array $events = ['meteorShower', 'blessing'];

    public function triggerEvent($eventName): void
    {
        // Реализация обработки событий
        switch ($eventName) {
            case 'meteorShower':
                $this->meteorShower();
                break;
            case 'blessing':
                $this->blessing();
                break;
        }
    }

    public function triggerRandomEvent(): void
    {
        $randomEvent = $this->events[array_rand($this->events)];
        $this->triggerEvent($randomEvent);
    }

    public function meteorShower(): void
    {
        // Уменьшение здоровья всех игроков на поле на 5 HP
        $this->db->query("UPDATE users SET hp = hp - 5");
        print "Метеоритный дождь! Все игроки потеряли 5 HP!";
    }

    public function blessing(): void
    {
        // Увеличение здоровья всех игроков на поле на 5 HP
        $this->db->query("UPDATE users SET hp = hp + 5");
        print "Солнце обратило на вас свой взор! Все игроки получили 5 HP!";
    }
}