<?php
class Shelter {
    public int $id;
    public string $location;
    public string $contactInfo;
    public array $animals = [];

    public function addAnimal(array $animalData): void {
        $this->animals[] = $animalData;
    }

    public function getAnimals(): array {
        return $this->animals;
    }
}
?>