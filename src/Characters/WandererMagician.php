<?php

require_once 'Character.php';

class WandererMagician extends Character {

    public function __construct(string $name) {
        parent::__construct($name);

        $this->className = 'Wanderer Magician';
        $this->maxHp     = 150;
        $this->hp        = 150;
        $this->attack    = 35;
        $this->defense   = 12;
    }

    // Habilidad especial: curación
    // Se cura a sí mismo 20 HP sin pasar del máximo
    public function specialAbility(Character $target): array {
        $healAmount  = 20;
        $hpBefore    = $this->hp;

        // min() asegura que no se cure por encima del HP máximo
        $this->hp = min($this->maxHp, $this->hp + $healAmount);

        $realHeal = $this->hp - $hpBefore; // cuánto se curó realmente

        return [
            'type'    => 'heal',
            'heal'    => $realHeal,
            'message' => $this->name . ' se cura ' . $realHeal . ' puntos de vida!',
        ];
    }

}
