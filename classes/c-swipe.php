<?php
class Swipe {
    public int $id;
    public int $user_id;
    public int $animal_id;
    public string $direction;
}

enum SwipeDirection: string {
    case LEFT = 'left';
    case RIGHT = 'right';
}
?>