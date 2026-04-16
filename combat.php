<?php
 
// ── 1. Cargar las clases de personajes ──
require_once 'src/Characters/Character.php';
require_once 'src/Characters/Knight.php';
require_once 'src/Characters/FireWizard.php';
require_once 'src/Characters/LightningMage.php';
require_once 'src/Characters/WandererMagician.php';
 
// ── 2. Recibir los datos que mandó menu.html ──
// menu.html los guarda en sessionStorage, pero PHP no puede leer eso.
// Por eso menu.html los manda por GET en la URL al redirigir.
// Ejemplo: combat.php?p1_nick=Juan&p1_char=knight&p2_nick=Maria&p2_char=fire_wizard
$p1Nick = $_GET['p1_nick'] ?? 'Jugador 1';
$p1Char = $_GET['p1_char'] ?? 'knight';
$p2Nick = $_GET['p2_nick'] ?? 'Jugador 2';
$p2Char = $_GET['p2_char'] ?? 'fire_wizard';
 
// ── 3. Crear los personajes según lo que eligió cada jugador ──
function createCharacter(string $charKey, string $name): Character {
    switch ($charKey) {
        case 'knight':             return new Knight($name);
        case 'fire_wizard':        return new FireWizard($name);
        case 'lightning_mage':     return new LightningMage($name);
        case 'wanderer_magician':  return new WandererMagician($name);
        default:                   return new Knight($name);
    }
}
 
$p1 = createCharacter($p1Char, $p1Nick);
$p2 = createCharacter($p2Char, $p2Nick);
 
// ── 4. Simular el combate ronda por ronda ──
$combatLog = []; // aquí guardamos cada acción para mandársela al JS
$round     = 1;
$maxRounds = 20; // límite para evitar bucle infinito
 
while (!$p1->isDead() && !$p2->isDead() && $round <= $maxRounds) {
 
    // Costo de estamina por acción
    $normalCost  = 20;
    $specialCost = 35;
 
    // -- Turno del Jugador 1 --
    if (!$p1->isDead()) {
 
        // Decidir si usa habilidad especial (si tiene estamina suficiente, 40% de probabilidad)
        $useSpecial = $p1->getStamina() >= $specialCost && rand(1, 100) <= 40;
 
        if ($useSpecial) {
            $p1->useStamina($specialCost);
            $result = $p1->specialAbility($p2);
        } else {
            $p1->useStamina($normalCost);
            $damage = $p2->takeDamage($p1->getAttack());
            $result = [
                'type'    => 'attack',
                'damage'  => $damage,
                'message' => $p1->getName() . ' ataca a ' . $p2->getName() . ' por ' . $damage . ' de daño.',
            ];
        }
 
        // Guardar esta acción en el log
        $combatLog[] = [
            'round'      => $round,
            'attacker'   => 'p1',
            'isSpecial'  => $useSpecial,
            'isHeal'     => ($result['type'] === 'heal'),
            'p1Hp'       => $p1->getHp(),
            'p2Hp'       => $p2->getHp(),
            'p1Stamina'  => $p1->getStamina(),
            'p2Stamina'  => $p2->getStamina(),
            'message'    => $result['message'],
        ];
 
        if ($p2->isDead()) break; // si murió J2, terminar
    }
 
    // -- Turno del Jugador 2 --
    if (!$p2->isDead()) {
 
        $useSpecial = $p2->getStamina() >= $specialCost && rand(1, 100) <= 40;
 
        if ($useSpecial) {
            $p2->useStamina($specialCost);
            $result = $p2->specialAbility($p1);
        } else {
            $p2->useStamina($normalCost);
            $damage = $p1->takeDamage($p2->getAttack());
            $result = [
                'type'    => 'attack',
                'damage'  => $damage,
                'message' => $p2->getName() . ' ataca a ' . $p1->getName() . ' por ' . $damage . ' de daño.',
            ];
        }
 
        $combatLog[] = [
            'round'      => $round,
            'attacker'   => 'p2',
            'isSpecial'  => $useSpecial,
            'isHeal'     => ($result['type'] === 'heal'),
            'p1Hp'       => $p1->getHp(),
            'p2Hp'       => $p2->getHp(),
            'p1Stamina'  => $p1->getStamina(),
            'p2Stamina'  => $p2->getStamina(),
            'message'    => $result['message'],
        ];
 
        if ($p1->isDead()) break; // si murió J1, terminar
    }
 
    $round++;
}
 
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Almas Oscuras — Combate</title>
  <link rel="stylesheet" href="style.css" />
</head>

<body class="screen-combat">
 
  <div id="combat-bg"></div>
 
  <div id="combat-ui">
 
    <div id="hp-row">
      <div class="player-hud left">
        <div class="p-name"><?= htmlspecialchars($p1->getName()) ?></div>
        <div class="p-class"><?= htmlspecialchars($p1->getClassName()) ?></div>
        <div class="bar-label">Vida</div>
        <div class="bar-track"><div class="bar-fill hp-fill" id="p1-hp-bar" style="width:100%"></div></div>
        <div class="bar-numbers" id="p1-hp-text"><?= $p1->getMaxHp() ?> / <?= $p1->getMaxHp() ?></div>
        <div class="bar-label">Estamina</div>
        <div class="bar-track"><div class="bar-fill sta-fill" id="p1-sta-bar" style="width:100%"></div></div>
        <div class="bar-numbers" id="p1-sta-text">100 / 100</div>
      </div>
 
      <div id="vs-label">VS</div>
 
      <div class="player-hud right">
        <div class="p-name"><?= htmlspecialchars($p2->getName()) ?></div>
        <div class="p-class"><?= htmlspecialchars($p2->getClassName()) ?></div>
        <div class="bar-label">Vida</div>
        <div class="bar-track"><div class="bar-fill hp-fill" id="p2-hp-bar" style="width:100%"></div></div>
        <div class="bar-numbers" id="p2-hp-text"><?= $p2->getMaxHp() ?> / <?= $p2->getMaxHp() ?></div>
        <div class="bar-label">Estamina</div>
        <div class="bar-track"><div class="bar-fill sta-fill" id="p2-sta-bar" style="width:100%"></div></div>
        <div class="bar-numbers" id="p2-sta-text">100 / 100</div>
      </div>
    </div>
 
    <div id="arena">
      <div class="char-wrap">
        <div class="char-sprite" id="p1-sprite"
             data-idle="assets/sprites/<?= $p1Char ?>/Idle.png"
             data-attack="assets/sprites/<?= $p1Char ?>/Attack_1.png"
             data-hurt="assets/sprites/<?= $p1Char ?>/Hurt.png"
             data-dead="assets/sprites/<?= $p1Char ?>/Dead.png"
             data-char="<?= $p1Char ?>">
        </div>
        <div class="char-shadow"></div>
      </div>
 
      <div class="char-wrap">
        <div class="char-sprite" id="p2-sprite"
             data-idle="assets/sprites/<?= $p2Char ?>/Idle.png"
             data-attack="assets/sprites/<?= $p2Char ?>/Attack_1.png"
             data-hurt="assets/sprites/<?= $p2Char ?>/Hurt.png"
             data-dead="assets/sprites/<?= $p2Char ?>/Dead.png"
             data-char="<?= $p2Char ?>">
        </div>
        <div class="char-shadow"></div>
      </div>
    </div>
 
    <div id="bottom-row">
      <div id="combat-log">
        <div class="log-title">— Registro de Combate —</div>
      </div>
      <div id="controls">
        <div class="round-label">Ronda <span id="round-num">—</span></div>
        <button class="menu-btn" id="btn-start" onclick="startCombat()">⚔ Iniciar</button>
        <button class="menu-btn" onclick="goMenu()">← Menú</button>
      </div>
    </div>
 
  </div>
 
  <script>
    // PHP inyecta el log del combate ya calculado
    const COMBAT_DATA = <?= json_encode($combatLog) ?>;
 
    // HP máximos para calcular el porcentaje de las barras
    const P1_MAX_HP = <?= $p1->getMaxHp() ?>;
    const P2_MAX_HP = <?= $p2->getMaxHp() ?>;
 
    // Frames por animación y personaje
    const FRAMES = {
      knight:            { idle:4, attack:4, hurt:2, dead:6 },
      fire_wizard:       { idle:7, attack:4, hurt:3, dead:5 },
      lightning_mage:    { idle:6, attack:9, hurt:3, dead:5 },
      wanderer_magician: { idle:6, attack:5, hurt:4, dead:3 },
    };
 
    let actionIndex = 0;
    let lastRound   = 0;
    let timer       = null;
    const SPEED     = 2200;
 
    // Cambia el sprite de un personaje mostrando solo el primer frame
    function setSprite(spriteId, imageUrl, frameCount) {
      const el        = document.getElementById(spriteId);
      const frameWidth = 128; // ancho del div en px — cada frame ocupa 128px
      el.style.backgroundImage    = "url('" + imageUrl + "')";
      el.style.backgroundSize     = (frameCount * frameWidth) + 'px auto';
      el.style.backgroundPosition = '0px bottom';
    }
 
    // Inicializar sprites en idle al cargar
    window.addEventListener('DOMContentLoaded', () => {
      const p1El   = document.getElementById('p1-sprite');
      const p2El   = document.getElementById('p2-sprite');
      const p1Char = p1El.dataset.char;
      const p2Char = p2El.dataset.char;
 
      setSprite('p1-sprite', p1El.dataset.idle, FRAMES[p1Char].idle);
      setSprite('p2-sprite', p2El.dataset.idle, FRAMES[p2Char].idle);
    });
 
    function updateHp(player, current, max) {
      const pct = Math.max(0, (current / max) * 100);
      document.getElementById(player + '-hp-bar').style.width  = pct + '%';
      document.getElementById(player + '-hp-text').textContent = Math.max(0, current) + ' / ' + max;
    }
 
    function updateStamina(player, current) {
      document.getElementById(player + '-sta-bar').style.width  = Math.max(0, current) + '%';
      document.getElementById(player + '-sta-text').textContent = Math.max(0, current) + ' / 100';
    }
 
    function addLog(text, cssClass) {
      const log  = document.getElementById('combat-log');
      const line = document.createElement('div');
      line.className   = 'log-line ' + (cssClass || '');
      line.textContent = text;
      log.appendChild(line);
      log.scrollTop = log.scrollHeight;
    }
 
    function processAction(data) {
      if (data.round !== lastRound) {
        lastRound = data.round;
        addLog('── Ronda ' + data.round + ' ──', 'round');
        document.getElementById('round-num').textContent = data.round;
      }
 
      const attackerEl = document.getElementById(data.attacker + '-sprite');
      const targetId   = data.attacker === 'p1' ? 'p2' : 'p1';
      const targetEl   = document.getElementById(targetId + '-sprite');
      const aChar      = attackerEl.dataset.char;
      const tChar      = targetEl.dataset.char;
 
      // Mostrar sprite de ataque y volver a idle
      setSprite(data.attacker + '-sprite', attackerEl.dataset.attack, FRAMES[aChar].attack);
      setTimeout(() => {
        setSprite(data.attacker + '-sprite', attackerEl.dataset.idle, FRAMES[aChar].idle);
      }, 600);
 
      // Mostrar hurt o dead en el que recibe
      if (data.p1Hp <= 0) {
        const el = document.getElementById('p1-sprite');
        setSprite('p1-sprite', el.dataset.dead, FRAMES[el.dataset.char].dead);
      } else if (data.p2Hp <= 0) {
        const el = document.getElementById('p2-sprite');
        setSprite('p2-sprite', el.dataset.dead, FRAMES[el.dataset.char].dead);
      } else if (!data.isHeal) {
        setSprite(targetId + '-sprite', targetEl.dataset.hurt, FRAMES[tChar].hurt);
        setTimeout(() => {
          setSprite(targetId + '-sprite', targetEl.dataset.idle, FRAMES[tChar].idle);
        }, 500);
      }
 
      updateHp('p1', data.p1Hp, P1_MAX_HP);
      updateHp('p2', data.p2Hp, P2_MAX_HP);
      updateStamina('p1', data.p1Stamina);
      updateStamina('p2', data.p2Stamina);
 
      addLog(data.message, data.attacker);
    }
 
    function runNext() {
      if (actionIndex >= COMBAT_DATA.length) {
        const last   = COMBAT_DATA[COMBAT_DATA.length - 1];
        const winner = last.p1Hp <= 0
          ? '<?= htmlspecialchars($p2->getName()) ?>'
          : '<?= htmlspecialchars($p1->getName()) ?>';
        addLog('⚔ ' + winner + ' gana el combate!', 'end');
        document.getElementById('btn-start').disabled = true;
        return;
      }
      processAction(COMBAT_DATA[actionIndex]);
      actionIndex++;
      timer = setTimeout(runNext, SPEED);
    }
 
    function startCombat() {
      document.getElementById('btn-start').disabled = true;
      addLog('¡El combate comienza!', 'round');
      runNext();
    }
 
    function goMenu() {
      clearTimeout(timer);
      window.location.href = 'menu.html';
    }
  </script>
 
</body>
</html>