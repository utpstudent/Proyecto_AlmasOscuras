<?php

require_once 'Character.php';

class FireWizard extends Character {

    public function __construct(string $name) {
        parent::__construct($name);

        $this->className = 'Fire Wizard';
        $this->maxHp     = 100;
        $this->hp        = 100;
        $this->attack    = 50;
        $this->defense   = 5;
    }

    // Habilidad especial: quema al enemigo
    // Causa daño extra además del ataque normal
    public function specialAbility(Character $target): array {
        $burnDamage = 15; // daño extra por quemadura
        $realDamage = $target->takeDamage($burnDamage);

        return [
            'type'    => 'burn',
            'damage'  => $realDamage,
            'message' => $this->name . ' quema a ' . $target->getName()
                         . ' causando ' . $realDamage . ' de daño extra!',
        ];
    }

}
