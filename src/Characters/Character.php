<?php

abstract class Character {

    // Atributos de cada personaje
    protected string $name;       // nickname del jugador
    protected string $className;  // "Knight", "Fire Wizard", etc.
    protected int $hp;            // vida actual
    protected int $maxHp;         // vida máxima
    protected int $attack;        // daño base
    protected int $defense;       // reducción de daño
    protected int $stamina;       // estamina actual
    protected int $maxStamina;    // estamina máxima (siempre 100)

    // Constructor: se llama al crear cualquier personaje
    public function __construct(string $name) {
        $this->name       = $name;
        $this->stamina    = 100;
        $this->maxStamina = 100;
        // hp, maxHp, attack, defense los define cada subclase
    }

    // Recibir daño — descuenta defensa antes de restar HP
    public function takeDamage(int $damage): int {
        $realDamage = max(0, $damage - $this->defense);
        $this->hp   = max(0, $this->hp - $realDamage);
        return $realDamage; // devuelve el daño real para mostrarlo en el log
    }

    // Gastar estamina — devuelve false si no hay suficiente
    public function useStamina(int $amount): bool {
        if ($this->stamina < $amount) {
            return false; // no tiene estamina suficiente
        }
        $this->stamina -= $amount;
        return true;
    }

    // Está muerto?
    public function isDead(): bool {
        return $this->hp <= 0;
    }

    // Getters — para leer los valores desde fuera
    public function getName(): string     { return $this->name; }
    public function getClassName(): string { return $this->className; }
    public function getHp(): int          { return $this->hp; }
    public function getMaxHp(): int       { return $this->maxHp; }
    public function getStamina(): int     { return $this->stamina; }
    public function getMaxStamina(): int  { return $this->maxStamina; }
    public function getAttack(): int      { return $this->attack; }
    public function getDefense(): int     { return $this->defense; }

    // Método abstracto: cada personaje define su habilidad especial
    // Lo implementa cada subclase (Knight, FireWizard, etc.)
    abstract public function specialAbility(Character $target): array;

}
