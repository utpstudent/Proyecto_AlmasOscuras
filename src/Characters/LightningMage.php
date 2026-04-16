<?php

require_once 'Character.php';

class LightningMage extends Character {

    public function __construct(string $name) {
        parent::__construct($name);

        $this->className = 'Lightning Mage';
        $this->maxHp     = 150;
        $this->hp        = 150;
        $this->attack    = 50;
        $this->defense   = 5;
    }

    // Habilidad especial: ataque doble
    // Ataca dos veces seguidas al enemigo
    public function specialAbility(Character $target): array {
        $damage1 = $target->takeDamage($this->attack);
        $damage2 = $target->takeDamage($this->attack);
        $total   = $damage1 + $damage2;

        return [
            'type'    => 'double',
            'damage'  => $total,
            'message' => $this->name . ' ataca dos veces a ' . $target->getName()
                         . ' causando ' . $total . ' de daño en total!',
        ];
    }

}
