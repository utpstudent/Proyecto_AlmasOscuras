<?php

require_once 'Character.php';

class Knight extends Character {

    public function __construct(string $name) {
        parent::__construct($name); // llama al constructor de Character

        $this->className = 'Knight';
        $this->maxHp     = 200;
        $this->hp        = 200;
        $this->attack    = 35;
        $this->defense   = 20;
    }

    // Habilidad especial: bloquear daño
    // El Knight tiene 30% de probabilidad de bloquear el próximo ataque
    public function specialAbility(Character $target): array {
        // rand(1, 100) genera un número aleatorio entre 1 y 100
        // Si cae en 1-30, bloquea (30% de probabilidad)
        $blocked = rand(1, 100) <= 30;

        return [
            'type'    => 'block',
            'blocked' => $blocked,
            'message' => $blocked
                ? $this->name . ' activa su escudo y bloquea el próximo ataque!'
                : $this->name . ' intenta bloquear pero falla.',
        ];
    }

}
