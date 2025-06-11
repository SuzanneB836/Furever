<?php

class Swipe {
    public int $id;
    public string $user;
    public string $animal;
    public SwipeDirection $direction;
}

enum SwipeDirection: string {
    case LEFT = 'left';
    case RIGHT = 'right';
}


/*

$swipe = new Swipe();
$swipe->direction = SwipeDirection::RIGHT; // or LEFT

*/

?>