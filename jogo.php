<?php
session_start();

$nomes_tipos = ["VALETE (J)", "DAMA (Q)", "REI (K)"];
$nomes_cartas = ["VALETE (J)", "DAMA (Q)", "REI (K)", "CORINGA 🃏"];

if (!isset($_SESSION['estado'])) {
    $_SESSION['estado'] = 'menu';
    $_SESSION['p1_nome'] = 'Jogador 1';
    $_SESSION['p2_nome'] = 'Jogador 2';
    $_SESSION['p1_cartas'] = [];
    $_SESSION['p2_cartas'] = [];
    $_SESSION['p1_penalidade'] = 0;
    $_SESSION['p2_penalidade'] = 0;
    $_SESSION['rodada'] = 1;
    $_SESSION['vez'] = 0;
    $_SESSION['manilha'] = 0;
    $_SESSION['baralho'] = [];
    $_SESSION['indice_baralho'] = 0;
    $_SESSION['etapa_turno'] = 'escolher';
    $_SESSION['cartas_descartadas'] = [];
    $_SESSION['declaracao'] = 0;
    $_SESSION['ultima_acao_msg'] = '';
    $_SESSION['revelado'] = false;
}

function inicializarBaralho() {
    $baralho = [];
    for ($i = 0; $i < 6; $i++) $baralho[] = 0; // J
    for ($i = 0; $i < 6; $i++) $baralho[] = 1; // Q
    for ($i = 0; $i < 6; $i++) $baralho[] = 2; // K
    for ($i = 0; $i < 2; $i++) $baralho[] = 3; // Coringa
    shuffle($baralho);
    return $baralho;
}

function iniciarRodada() {
    $_SESSION['baralho'] = inicializarBaralho();
    $_SESSION['indice_baralho'] = 0;
    $_SESSION['manilha'] = rand(0, 2);
    $_SESSION['p1_cartas'] = [];
    $_SESSION['p2_cartas'] = [];
    for ($i = 0; $i < 5; $i++) {
        $_SESSION['p1_cartas'][] = $_SESSION['baralho'][$_SESSION['indice_baralho']++];
        $_SESSION['p2_cartas'][] = $_SESSION['baralho'][$_SESSION['indice_baralho']++];
    }
}

function comprarCartas($jogador_chave, $qtd) {
    for ($i = 0; $i < $qtd; $i++) {
        if ($_SESSION['indice_baralho'] < count($_SESSION['baralho'])) {
            $_SESSION[$jogador_chave][] = $_SESSION['baralho'][$_SESSION['indice_baralho']++];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao = $_POST['acao'] ?? '';

    if ($acao === 'reset') {
        unset(
            $_SESSION['estado'], $_SESSION['p1_nome'], $_SESSION['p2_nome'],
            $_SESSION['p1_cartas'], $_SESSION['p2_cartas'], $_SESSION['p1_penalidade'],
            $_SESSION['p2_penalidade'], $_SESSION['rodada'], $_SESSION['vez'],
            $_SESSION['manilha'], $_SESSION['baralho'], $_SESSION['indice_baralho'],
            $_SESSION['etapa_turno'], $_SESSION['cartas_descartadas'], $_SESSION['declaracao'],
            $_SESSION['ultima_acao_msg'], $_SESSION['revelado'], $_SESSION['anim_evento']
        );
        header("Location: jogo.php");
        exit;
    }

    if ($acao === 'configurar') {
        $_SESSION['p1_nome'] = trim($_POST['p1']) ?: 'Jogador 1';
        $_SESSION['p2_nome'] = trim($_POST['p2']) ?: 'Jogador 2';
        iniciarRodada();
        $_SESSION['estado'] = 'jogando';
        $_SESSION['etapa_turno'] = 'escolher';
        $_SESSION['revelado'] = false;
    }
    elseif ($acao === 'descartar') {
        $indices = $_POST['cartas_idx'] ?? [];
        if (!empty($indices) && count($indices) <= 2) {
            $chave = ($_SESSION['vez'] % 2 == 0) ? 'p1_cartas' : 'p2_cartas';
            rsort($indices);
            $_SESSION['cartas_descartadas'] = [];
            foreach ($indices as $idx) {
                $idx = intval($idx);
                if (isset($_SESSION[$chave][$idx])) {
                    array_unshift($_SESSION['cartas_descartadas'], $_SESSION[$chave][$idx]);
                    array_splice($_SESSION[$chave], $idx, 1);
                }
            }
            $_SESSION['etapa_turno'] = 'declarar';
            $_SESSION['revelado'] = false;
        }
    }
    elseif ($acao === 'declarar') {
        $_SESSION['declaracao'] = intval($_POST['tipo_declaracao']);
        $_SESSION['etapa_turno'] = 'questionar';
        $_SESSION['revelado'] = false;
    }
    elseif ($acao === 'responder') {
        $opcao = intval($_POST['opcao_oponente']);
        $vez_atual = $_SESSION['vez'];
        $is_p1 = ($vez_atual % 2 == 0);

        $atual_nome    = $is_p1 ? $_SESSION['p1_nome'] : $_SESSION['p2_nome'];
        $oponente_nome = $is_p1 ? $_SESSION['p2_nome'] : $_SESSION['p1_nome'];

        $manilha = $_SESSION['manilha'];
        $declaracao_eh_manilha = ($_SESSION['declaracao'] == 1);

        $todas_validas = true;
        $carta_delatora = "";
        $cartas_str = implode(' e ', array_map(function($c) use ($nomes_cartas) { return $nomes_cartas[$c]; }, $_SESSION['cartas_descartadas']));

        // Aqui está a matemática do grupo: Se uma falhar, todas falham.
        foreach ($_SESSION['cartas_descartadas'] as $carta) {
            $eh_manilha = ($carta == 3 || $carta == $manilha);
            if ($declaracao_eh_manilha != $eh_manilha) {
                $todas_validas = false;
                $carta_delatora = $nomes_cartas[$carta]; // Guarda o nome da carta que provou a mentira
                break;
            }
        }

        $p1_foi_penalizado = false;
        $p2_foi_penalizado = false;

        if ($opcao == 1) { // Questionou
            if ($todas_validas) {
                $_SESSION['anim_evento'] = 'verdade';
                $msg = "✅ Verdade! $oponente_nome duvidou, mas $atual_nome não mentiu. A dupla era: $cartas_str.";
                if ($is_p1) {
                    $_SESSION['p2_penalidade']++;
                    $p2_foi_penalizado = true;
                    comprarCartas('p2_cartas', $_SESSION['p2_penalidade'] * 2);
                } else {
                    $_SESSION['p1_penalidade']++;
                    $p1_foi_penalizado = true;
                    comprarCartas('p1_cartas', $_SESSION['p1_penalidade'] * 2);
                }
            } else {
                $_SESSION['anim_evento'] = 'blefe';
                // A MÁGICA ACONTECE AQUI: A mensagem explica o motivo da penalidade!
                $motivo = ($declaracao_eh_manilha) 
                    ? "Você declarou que AMBAS eram Manilha, mas a carta $carta_delatora não era!" 
                    : "Você declarou que AMBAS NÃO eram Manilha, mas a carta $carta_delatora era!";
                
                $msg = "❌ Blefe pego! $atual_nome mentiu. $motivo (Cartas: $cartas_str).";
                
                if ($is_p1) {
                    $_SESSION['p1_penalidade']++;
                    $p1_foi_penalizado = true;
                    comprarCartas('p1_cartas', $_SESSION['p1_penalidade'] * 2);
                } else {
                    $_SESSION['p2_penalidade']++;
                    $p2_foi_penalizado = true;
                    comprarCartas('p2_cartas', $_SESSION['p2_penalidade'] * 2);
                }
            }
        } else {
            $msg = "🤝 $oponente_nome acreditou no descarte. Segue a mesa!";
        }

        if (count($_SESSION['p1_cartas']) == 0 && !$p1_foi_penalizado) {
            $_SESSION['estado'] = 'vitoria';
            $_SESSION['vencedor'] = $_SESSION['p1_nome'];
        } elseif (count($_SESSION['p2_cartas']) == 0 && !$p2_foi_penalizado) {
            $_SESSION['estado'] = 'vitoria';
            $_SESSION['vencedor'] = $_SESSION['p2_nome'];
        } else {
            $_SESSION['vez']++;
            $_SESSION['etapa_turno'] = 'escolher';
            $_SESSION['ultima_acao_msg'] = $msg;
            $_SESSION['revelado'] = false;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>House of Cards: Swipe Edition</title>
    
    <!-- Configurações do PWA / Standalone -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0b0f19">
    <link rel="apple-touch-icon" href="icone-192.png">
    
    <link rel="stylesheet" href="jogo.css">
</head>
<body>

<?php if (isset($_SESSION['anim_evento'])): ?>
    <div class="popup-evento <?php echo $_SESSION['anim_evento']; ?>">
        <?php echo ($_SESSION['anim_evento'] == 'blefe') ? '😱 BLEFE PEGO!' : '🛡️ VERDADE!'; ?>
    </div>
    <?php unset($_SESSION['anim_evento']); ?>
<?php endif; ?>

<div class="topo-acoes">
    <form method="POST" style="margin: 0;">
        <input type="hidden" name="acao" value="reset">
        <button type="submit" class="btn-top">🔄 Menu / Reset</button>
    </form>
    <button type="button" class="btn-top danger" onclick="sairDoJogo()">🚪 Sair</button>
</div>

<div class="modal-overlay" id="modal-regras">
    <div class="modal-card">
        <h2>Como Jogar 🃏</h2>
        <div class="regras-texto">
            <p><b>Objetivo:</b> Ser o primeiro a ficar sem cartas na mão!</p>
            <ul>
                <li>Cada jogador começa com <b>5 cartas</b>.</li>
                <li>A <b>Manilha</b> da rodada é sorteada. O Coringa é sempre Manilha.</li>
                <li>No seu turno, <b>arraste 1 ou 2 cartas</b> para a gaveta.</li>
                <li>Em seguida, <b>declare</b>: "É a Manilha" ou "Não é a Manilha".</li>
                <li>O oponente pode <b>Acreditar</b> ou <b>Questionar</b> (Duvidar).</li>
                <li><b>Se você mentiu:</b> Toma penalidade e compra cartas extras.</li>
                <li><b>Se você falou a verdade:</b> O oponente toma penalidade e compra!</li>
            </ul>
            <p class="regra-dica" style="background: rgba(239, 68, 68, 0.15); border: 1px solid rgba(239, 68, 68, 0.3); color: #fca5a5;">
                ⚠️ <b>Atenção à regra de grupo:</b> Sua declaração vale para o <b>grupo TODO</b>. 
                Se você descarta 2 cartas e diz "É a Manilha", as DUAS precisam ser manilha. 
                Se disser "NÃO é a Manilha", NENHUMA das duas pode ser manilha. <b>Meias-verdades contam como blefe!</b>
            </p>
        </div>
        <button type="button" class="btn-fenda" onclick="fecharRegras()">Entendi, vamos jogar! 🚀</button>
    </div>
</div>

<div class="game-viewport">
    <div class="glass-card">

        <?php if ($_SESSION['estado'] === 'menu'): ?>
            <h1>House of Cards</h1>
            <h2>Edição Pife, Blefe & Swipe 🃏</h2>
            
            <form method="POST" style="margin-top: 20px;">
                <input type="hidden" name="acao" value="configurar">
                <label class="label-jogador">Jogador 1:</label>
                <input type="text" name="p1" value="Jogador 1" required maxlength="20">
                <label class="label-jogador">Jogador 2:</label>
                <input type="text" name="p2" value="Jogador 2" required maxlength="20">
                <button type="submit" class="btn-fenda">COMEÇAR PARTIDA 🚀</button>
                <button type="button" class="btn-fenda btn-secundario" onclick="abrirRegras()">Como Jogar 📖</button>
            </form>

        <?php elseif ($_SESSION['estado'] === 'jogando'):
            $vez_atual     = $_SESSION['vez'];
            $is_p1         = ($vez_atual % 2 == 0);
            $atual_nome    = $is_p1 ? $_SESSION['p1_nome'] : $_SESSION['p2_nome'];
            $oponente_nome = $is_p1 ? $_SESSION['p2_nome'] : $_SESSION['p1_nome'];
            $atual_cartas  = $is_p1 ? $_SESSION['p1_cartas'] : $_SESSION['p2_cartas'];
            $penalidade_atual = $is_p1 ? $_SESSION['p1_penalidade'] : $_SESSION['p2_penalidade'];

            $jogador_da_vez = ($_SESSION['etapa_turno'] === 'questionar') ? $oponente_nome : $atual_nome;
            $esta_revelado  = $_SESSION['revelado'] ?? false;
        ?>

            <h2>Vez de: <span class="nome-destaque"><?php echo htmlspecialchars($jogador_da_vez); ?></span></h2>

            <div class="info-mesa">
                <div class="info-manilha">
                    <b>Manilha:</b> <?php echo $nomes_tipos[$_SESSION['manilha']]; ?>
                </div>
                <div class="info-jogadores">
                    <div class="info-jogador <?php echo $is_p1 ? 'destaque' : ''; ?>">
                        <span class="nome"><?php echo htmlspecialchars($_SESSION['p1_nome']); ?></span>
                        <span class="stats">🎴 <?php echo count($_SESSION['p1_cartas']); ?> · ⚠️ <?php echo $_SESSION['p1_penalidade']; ?></span>
                    </div>
                    <div class="info-jogador <?php echo !$is_p1 ? 'destaque' : ''; ?>">
                        <span class="nome"><?php echo htmlspecialchars($_SESSION['p2_nome']); ?></span>
                        <span class="stats">🎴 <?php echo count($_SESSION['p2_cartas']); ?> · ⚠️ <?php echo $_SESSION['p2_penalidade']; ?></span>
                    </div>
                </div>
            </div>

            <?php if (!empty($_SESSION['ultima_acao_msg'])): ?>
                <div class="msg-resultado">
                    🔔 <?php echo htmlspecialchars($_SESSION['ultima_acao_msg']); $_SESSION['ultima_acao_msg'] = ''; ?>
                </div>
            <?php endif; ?>

            <?php if ($_SESSION['etapa_turno'] === 'escolher'): ?>

                <button type="button" id="btn-revelar" class="btn-revelar" onclick="toggleRevelar()">
                    <?php echo $esta_revelado ? '🙈 Ocultar Minhas Cartas' : '👁️ Revelar Cartas de ' . htmlspecialchars($atual_nome); ?>
                </button>

                <form method="POST" id="form-descarte">
                    <input type="hidden" name="acao" value="descartar">

                    <?php if (!$esta_revelado): ?>
                        <div class="dica-flutuante" id="dica-flutuante">
                            Passe o celular e clique em Revelar! 👆
                        </div>
                    <?php else: ?>
                        <div class="dica-flutuante" id="dica-flutuante">
                            Arraste as cartas para cima 👆
                        </div>
                    <?php endif; ?>

                    <div class="mesa <?php echo !$esta_revelado ? 'mesa-oculta' : ''; ?>" id="mesa">
                        
                        <div class="zona-jogo" id="zona-jogo">
                            <div class="zona-hint">Arraste cartas pra cá</div>
                        </div>

                        <div class="leque-container">
                            <?php foreach ($atual_cartas as $idx => $carta_val): ?>
                                <div class="carta-card" data-location="hand" data-index="<?php echo $idx; ?>">
                                    <input type="checkbox" name="cartas_idx[]" value="<?php echo $idx; ?>" style="display:none;">
                                    <span class="carta-canto"><?php echo $carta_val == 3 ? '🃏' : ['J','Q','K'][$carta_val]; ?></span>
                                    <span class="emoji-carta"><?php echo ($carta_val == 3) ? '🃏' : '🎴'; ?></span>
                                    <span class="nome-carta"><?php echo $nomes_cartas[$carta_val]; ?></span>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="painel-oculto-alerta" id="painel-oculto" style="<?php echo $esta_revelado ? 'display: none;' : 'display: flex;'; ?>">
                            <span class="lock-icon">🔒</span>
                            <p class="lock-titulo">Cartas Escondidas</p>
                            <p class="lock-subtext">Para proteger sua mão, as cartas estão ocultas.</p>
                        </div>
                    </div>

                    <button type="submit" class="btn-fenda" id="btn-descartar" disabled>
                        Arraste cartas para a mesa
                    </button>
                </form>

            <?php elseif ($_SESSION['etapa_turno'] === 'declarar'): ?>
                
                <div class="info-mesa" style="margin-bottom: 20px; border-left-color: var(--borda-dourada);">
                    <div class="info-manilha" style="border: none; margin: 0; padding: 0;">
                        Manilha da rodada: <br>
                        <span style="font-size: 1.5em; color: var(--borda-dourada);"><?php echo $nomes_tipos[$_SESSION['manilha']]; ?></span>
                    </div>
                </div>

                <p class="subtext">Você descartou:</p>
                <div class="cartas-descartadas-display">
                    <?php foreach ($_SESSION['cartas_descartadas'] as $c): ?>
                        <div class="carta-mini">
                            <span class="emoji"><?php echo ($c == 3) ? '🃏' : '🎴'; ?></span>
                            <span><?php echo $nomes_cartas[$c]; ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
                <p class="subtext">O que você declara sobre a(s) carta(s)?</p>
                <form method="POST">
                    <input type="hidden" name="acao" value="declarar">
                    <button type="submit" name="tipo_declaracao" value="1" class="btn-fenda btn-verdade">👉 "É a Manilha"</button>
                    <button type="submit" name="tipo_declaracao" value="2" class="btn-fenda btn-mentira">👉 "NÃO é a Manilha"</button>
                </form>

            <?php elseif ($_SESSION['etapa_turno'] === 'questionar'): ?>
                <div class="questionar-box">
                    <p class="subtext"><b><?php echo htmlspecialchars($atual_nome); ?></b> declarou:</p>
                    <h3 class="declaracao-texto">"<?php echo ($_SESSION['declaracao'] == 1) ? 'É A MANILHA' : 'NÃO É A MANILHA'; ?>"</h3>
                    <p class="subtext">Vez de <b><?php echo htmlspecialchars($oponente_nome); ?></b> decidir:</p>
                    <form method="POST">
                        <input type="hidden" name="acao" value="responder">
                        <button type="submit" name="opcao_oponente" value="1" class="btn-fenda btn-questionar">❌ QUESTIONAR (Duvidar)</button>
                        <button type="submit" name="opcao_oponente" value="2" class="btn-fenda btn-acreditar">✅ ACREDITAR (Passar a Vez)</button>
                    </form>
                </div>
            <?php endif; ?>

        <?php elseif ($_SESSION['estado'] === 'vitoria'): ?>
            <h1 class="vitoria-titulo">FIM DE JOGO! 🏆</h1>
            <h2 class="vitoria-nome"><?php echo htmlspecialchars($_SESSION['vencedor']); ?> VENCEU!</h2>
            
            <form method="POST" style="margin: 0;">
                <input type="hidden" name="acao" value="reset">
                <button type="submit" class="btn-fenda">Jogar Novamente 🔄</button>
            </form>
        <?php endif; ?>

    </div>
</div>

<script src="jogo-swipe.js"></script>
<script>
    // Registra o PWA para funcionar como Standalone
    if ('serviceWorker' in navigator) {
        navigator.serviceWorker.register('sw.js').then(function(reg) {
            console.log('Service Worker registrado com sucesso!');
        }).catch(function(err) {
            console.log('Falha ao registrar o Service Worker:', err);
        });
    }
</script>
</body>
</html>