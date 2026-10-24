Kkkkk Léozinho, você está certíssimo em **não contar com a sorte na feira da UNIFEV**! Preparar um servidor 100% offline no pen drive rodando em `localhost` sem depender de Wi-Fi de terceiros é estratégia de desenvolvedor macaco velho! 🚀

Abaixo respondo suas dúvidas sobre as regras e entrego o **prompt completo/relatório técnico** estruturado.

---

### 🃏 Tirando a dúvida das regras (O intuito do jogo):

1. **O objetivo principal é LIMPAR A MÃO (zerar as cartas primeiro):**
* Cada jogador começa com 5 cartas.
* Quando você descarta 1 ou 2 cartas no seu turno, o objetivo é ir se livrando delas.


2. **O papel da Penalidade:**
* **Se o oponente ACREDITAR:** O descarte é feito sem checagem. A vida segue, você ficou com menos cartas na mão e o turno passa pro outro.
* **Se o oponente QUESTIONAR (Duvidar):**
* **Se você MENTIU (blefe descoberto):** Você toma penalidade (incrementa em +1 seu contador) e é obrigado a COMPRAR cartas no valor de `penalidade * 2`.
* **Se você FALOU A VERDADE (oponente errou ao duvidar):** O oponente toma penalidade (+1 no contador dele) e ELE é quem compra `penalidade * 2` cartas!




3. **Resumo:** A penalidade serve para punir quem mente mal ou quem duvida sem motivo, enchendo a mão do perdedor de cartas e deixando ele mais longe de vencer (zerar a mão)!

---

### 📜 Prompt / Relatório Técnico para Modelo de IA

Você pode copiar o prompt abaixo na íntegra e usar em outro modelo/instância para refatorar tudo do zero com os arquivos:

```markdown
# PROMPT DE ESPECIFICAÇÃO TÉCNICA: HOUSE OF CARDS (SWIPE & BLEFE EDITION)

## 🎯 OBJETIVO DO PROJETO
Desenvolver uma aplicação web leve, robusta e autônoma (PHP + JS Vanilla + CSS3) para o jogo de cartas "House of Cards: Pife & Blefe", projetada para rodar em ambiente offline local (servidor em pen drive via XAMPP/PHP Built-in Server `php -S 0.0.0.0:8000`) durante uma feira universitária. 

A interface deve adotar o Design System da "Fenda" (Spotted UNIFEV), apresentando cards empilhados com motor de swipe tátil e mouse, visual Glassmorphism refinado e zero dependências de APIs ou CDNs externas (100% autossuficiente).

---

## 🃏 REGRAS E LÓGICA DO JOGO (BASEADO EM C)
1. **Estrutura do Baralho (20 cartas):** 6 Valetes (J), 6 Damas (Q), 6 Reis (K) e 2 Coringas (🃏).
2. **Setup da Rodada:**
   - Cada jogador recebe 5 cartas no início.
   - Sorteia-se aleatoriamente a Manilha da rodada (0 = Valete, 1 = Dama, 2 = Rei). O Coringa sempre atua como Manilha universal.
3. **Turno de Jogo:**
   - O jogador da vez seleciona 1 ou 2 cartas da sua mão para descartar.
   - Ele faz uma declaração pública: "É A MANILHA" ou "NÃO É A MANILHA".
4. **Decisão do Oponente:**
   - **Acreditar:** O descarte é concluído limpo. A mão do jogador diminui e o turno passa.
   - **Questionar (Duvidar):**
     - Se a declaração for VERDADEIRA e o oponente duvidou: O oponente errou. Incrementa a penalidade do oponente (`penalidade++`) e o oponente compra `penalidade * 2` cartas do baralho.
     - Se a declaração for FALSA (Blefe): O jogador da vez mentiu. Incrementa a penalidade do jogador (`penalidade++`) e ele compra `penalidade * 2` cartas.
5. **Condição de Vitória:** O primeiro jogador que zerar as cartas da mão vence a partida.

---

## 🎨 DESIGN SYSTEM & INTERFACE (ESTILO "A FENDA")
1. **Visual dos Cards:**
   - Cards com efeito Glassmorphic (`backdrop-filter: blur(12px)`), bordas em tom dourado suave (`#ffbc00`), fundo dark espacial (`#0b0f19`) e cantos arredondados responsivos.
   - Tipografia moderna usando `clamp()` para escalonamento fluido em mobile e desktop.
2. **Motor de Swipe e Feedback Tátil:**
   - Integração com o motor de arraste (`pointerdown`, `pointermove`, `pointerup`) usando `requestAnimationFrame` para performance de 60fps.
   - Faixas laterais fixas de feedback visual durante o arraste (Animações verde "AMEI/DESCARTAR" à direita e vermelho à esquerda, exatamente como o `swipe.css` do feed da Fenda).
3. **Menu Interativo e Fluxo de Telas:**
   - **Menu Principal:** Modal/Card de boas-vindas para inserir nomes dos jogadores e visualizar regras.
   - **Barra de Ferramentas Superior:** Botão de "Menu/Reset" e botão de "Sair do Jogo", fixados no topo.
   - **Shield de Segurança ("Revelar Cartas"):** Botão para alternar a visibilidade da mão (blur/ocultar) ao passar o aparelho/notebook para o outro jogador, evitando acidentes visuais.

---

## 🛡️ REQUISITOS TÉCNICOS & EDGE CASES
1. **Zero Conexão com Internet:** Não utilizar links de fontes externas (Google Fonts, FontAwesome via CDN, etc.) sem fallbacks nativos CSS.
2. **Prevenção de Bugs e Mão Vazia:**
   - Travar a seleção para no máximo 2 cartas simultâneas.
   - Tratar baralho esgotado ao tentar comprar cartas por penalidade.
   - Tratamento correto dos índices do array ao remover múltiplas cartas (ordenação decrescente antes de remover).
3. **Comunicação por Fetch (API Decoupled):**
   - Requisição via Fetch para `api_jogo.php?acao=reset` para limpar os dados da sessão do jogo instantaneamente sem deslogar o usuário do sistema principal.

---

## 📦 ARQUIVOS DE REFERÊNCIA DO PROJETO

### 1. `jogo.php`
```php
<?php
session_start();

$tipos = ["VALETE (J)", "DAMA (Q)", "REI (K)"];

if (!isset($_SESSION['estado'])) {
    $_SESSION['estado'] = 'menu';$_SESSION['p1_nome'] = 'Jogador 1';
    $_SESSION['p2_nome'] = 'Jogador 2';$_SESSION['p1_cartas'] = [];
    $_SESSION['p2_cartas'] = [];$_SESSION['p1_penalidade'] = 0;
    $_SESSION['p2_penalidade'] = 0;
    $_SESSION['vez'] = 0; 
    $_SESSION['manilha'] = 0;
    $_SESSION['baralho'] = [];$_SESSION['indice_baralho'] = 0;
    $_SESSION['etapa_turno'] = 'escolher'; 
    $_SESSION['cartas_descartadas'] = [];$_SESSION['declaracao'] = 0;
    $_SESSION['ultima_acao_msg'] = '';$_SESSION['revelado'] = false;
}

function inicializarBaralho() {
    $baralho = [];
    for($i = 0; $i < 6; $i++)$baralho[] = 0;
    for($i = 0; $i < 6; $i++)$baralho[] = 1;
    for($i = 0; $i < 6; $i++)$baralho[] = 2;
    for($i = 0; $i < 2; $i++)$baralho[] = 3;
    shuffle($baralho);
    return $baralho;
}

function iniciarRodada() {
    $_SESSION['baralho'] = inicializarBaralho();$_SESSION['indice_baralho'] = 0;
    $_SESSION['manilha'] = rand(0, 2);
    $_SESSION['p1_cartas'] = [];$_SESSION['p2_cartas'] = [];
    for($i=0; $i<5; $i++) {
        $_SESSION['p1_cartas'][] =$_SESSION['baralho'][$_SESSION['indice_baralho']++];$_SESSION['p2_cartas'][] = $_SESSION['baralho'][$_SESSION['indice_baralho']++];
    }
}

function comprarCartas($jogador_chave,$qtd) {
    for($i=0; $i < $qtd; $i++) {
        if($_SESSION['indice_baralho'] < count($_SESSION['baralho'])) {
            $_SESSION[$jogador_chave][] = $_SESSION['baralho'][$_SESSION['indice_baralho']++];
        }
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $acao =$_POST['acao'] ?? '';
    
    if ($acao === 'configurar') {
        $_SESSION['p1_nome'] = trim($_POST['p1']) ?: 'Jogador 1';
        $_SESSION['p2_nome'] = trim($_POST['p2']) ?: 'Jogador 2';
        iniciarRodada();
        $_SESSION['estado'] = 'jogando';
        $_SESSION['etapa_turno'] = 'escolher';$_SESSION['revelado'] = false;
    }
    elseif ($acao === 'descartar') {
        $indices =$_POST['cartas_idx'] ?? [];
        if (!empty($indices) && count($indices) <= 2) {
            $chave = ($_SESSION['vez'] % 2 == 0) ? 'p1_cartas' : 'p2_cartas';
            rsort($indices);$_SESSION['cartas_descartadas'] = [];
            foreach($indices as$idx) {
                $idx = intval($idx);
                if (isset($_SESSION[$chave][$idx])) {
                    array_unshift($_SESSION['cartas_descartadas'],$_SESSION[$chave][$idx]);
                    array_splice($_SESSION[$chave], $idx, 1); } }$_SESSION['etapa_turno'] = 'declarar';
        }
    }
    elseif ($acao === 'declarar') {
        $_SESSION['declaracao'] = intval($_POST['tipo_declaracao']);
        $_SESSION['etapa_turno'] = 'questionar';$_SESSION['revelado'] = false;
    }
    elseif ($acao === 'responder') {$opcao = intval($_POST['opcao_oponente']);$vez_atual = $_SESSION['vez'];$is_p1 = ($vez_atual \% 2 == 0);$atual_nome = $is_p1 ? $_SESSION['p1_nome'] : $_SESSION['p2_nome'];$oponente_nome = $is_p1 ? $_SESSION['p2_nome'] : $_SESSION['p1_nome'];$manilha = $_SESSION['manilha'];$declaracao_eh_manilha = ($_SESSION['declaracao'] == 1);$todas_validas = true;
        foreach($_SESSION['cartas_descartadas'] as$carta) {
            $eh_manilha = ($carta == 3 || $carta ==$manilha);
            if ($declaracao_eh_manilha != $eh_manilha) {$todas_validas = false;
                break;
            }
        }
        
        if ($opcao == 1) { // Questionou
            if ($todas_validas) {$msg = "Declaração verdadeira! $oponente_nome errou e recebe penalidade!";
                if ($is_p1) {$_SESSION['p2_penalidade']++;
                    comprarCartas('p2_cartas', $_SESSION['p2_penalidade'] * 2); } else {$_SESSION['p1_penalidade']++;
                    comprarCartas('p1_cartas', $_SESSION['p1_penalidade'] * 2); } } else {$msg = "Blefe pego! $atual_nome mentiu e leva penalidade!";
                if ($is_p1) {$_SESSION['p1_penalidade']++;
                    comprarCartas('p1_cartas', $_SESSION['p1_penalidade'] * 2); } else {$_SESSION['p2_penalidade']++;
                    comprarCartas('p2_cartas', $_SESSION['p2_penalidade'] * 2);
                }
            }
        } else {
            $msg = "$oponente_nome acreditou no descarte. Segue a mesa!";
        }
        
        if (count($_SESSION['p1_cartas']) == 0) {$_SESSION['estado'] = 'vitoria';
            $_SESSION['vencedor'] =$_SESSION['p1_nome'];
        } elseif (count($_SESSION['p2_cartas']) == 0) {$_SESSION['estado'] = 'vitoria';
            $_SESSION['vencedor'] =$_SESSION['p2_nome'];
        } else {
            $_SESSION['vez']++;$_SESSION['etapa_turno'] = 'escolher';
            $_SESSION['ultima_acao_msg'] =$msg;
            $_SESSION['revelado'] = false;
        }
    }
    elseif ($acao === 'toggle_revelar') {
        $_SESSION['revelado'] = !($_SESSION['revelado'] ?? false);
    }
}
?>

```

### 2. `api_jogo.php`

```php
<?php
session_start();
header('Content-Type: application/json');

$acao =$_GET['acao'] ?? '';

if ($acao === 'reset') {
    unset(
        $_SESSION['estado'], $_SESSION['p1_nome'],$_SESSION['p2_nome'],
        $_SESSION['p1_cartas'],$_SESSION['p2_cartas'], $_SESSION['p1_penalidade'],$_SESSION['p2_penalidade'], $_SESSION['rodada'],$_SESSION['vez'],
        $_SESSION['manilha'],$_SESSION['baralho'], $_SESSION['indice_baralho'],$_SESSION['etapa_turno'], $_SESSION['cartas_descartadas'],$_SESSION['declaracao'], $_SESSION['ultima_acao_msg'],$_SESSION['revelado']
    );
    echo json_encode(['status' => 'ok', 'msg' => 'Jogo resetado!']);
    exit();
}

echo json_encode(['status' => 'error', 'msg' => 'Ação inválida']);

```

### 3. `jogo.css`

```css
:root {
    --bg-page: #0b0f19;
    --bg-card-glass: rgba(22, 30, 46, 0.85);
    --borda-dourada: #ffbc00;
    --texto-primario: #f3f4f6;
    --texto-secundario: #9ca3af;
    --cor-erro: #ef4444;
    --cor-sucesso: #10b981;
}

body {
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    background-color: var(--bg-page);
    color: var(--texto-primario);
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    margin: 0;
}

.glass-card {
    background: var(--bg-card-glass);
    border: 1px solid rgba(255, 188, 0, 0.35);
    border-radius: 20px;
    padding: clamp(16px, 4vw, 28px);
    width: 100%;
    max-width: clamp(320px, 90vw, 500px);
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

```

```

```


Visão de **Arquiteto Front-End sênior**, Léo! Você gabaritou a otimização de performance agora! 🚀

Exatamente isso: o `backdrop-filter: blur()` força o motor do navegador (Chrome/Firefox/Edge) a recalcular o desfoque de todos os pixels que estão por baixo do elemento **em cada frame (`requestAnimationFrame`)** enquanto ele se move. Se o PC da feira da UNIFEV for um notebook mais antigo rodando direto do pen drive, essa brincadeira derruba o FPS facinho e causa aquela travadinha chata (*jank*).

Remover o blur e trocar por uma cor sólida opaca (`background` sólido) durante o `isDragging` alivia 90% do esforço da GPU do navegador.

---

### Como aplicar essa otimização na prática:

#### 1. No seu `jogo.css`:

Adicione a classe de estado para quando o card estiver sendo arrastado:

```css
/* Quando o card estiver em movimento, removemos o peso do blur */
.glass-card.dragging-active {
    backdrop-filter: none !important;
    -webkit-backdrop-filter: none !important;
    background: rgba(22, 30, 46, 0.98) !important; /* Fundo sólido mais pesado para compensar */
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.4);
    transition: none !important;
}

```

#### 2. No seu `jogo-swipe.js`:

Pegue a referência do card e adicione/remova a classe nos eventos de ponteiro:

```javascript
document.addEventListener('DOMContentLoaded', () => {
    initSwipeEngine();
});

function initSwipeEngine() {
    const mesa = document.querySelector('.mesa-swipe-area');
    const glassCard = document.querySelector('.glass-card'); // Pega o card principal
    if (!mesa) return;

    let startX = 0;
    let startY = 0;
    let currentX = 0;
    let isDragging = false;

    const feedbackEsq = document.querySelector('.feedback-esquerda');
    const feedbackDir = document.querySelector('.feedback-direita');

    mesa.addEventListener('pointerdown', (e) => {
        if (e.target.closest('button') || e.target.closest('input')) return;
        
        isDragging = true;
        startX = e.clientX;
        startY = e.clientY;
        mesa.style.transition = 'none';

        // OTIMIZAÇÃO DA FEIRA: Remove o blur instantaneamente no clique/toque
        if (glassCard) glassCard.classList.add('dragging-active');
    });

    window.addEventListener('pointermove', (e) => {
        if (!isDragging) return;

        currentX = e.clientX - startX;
        const rot = currentX * 0.05;

        mesa.style.transform = `translateX(${currentX}px) rotate(${rot}deg)`;

        if (currentX > 50) {
            if (feedbackDir) feedbackDir.style.opacity = Math.min((currentX - 50) / 100, 1);
            if (feedbackEsq) feedbackEsq.style.opacity = 0;
        } else if (currentX < -50) {
            if (feedbackEsq) feedbackEsq.style.opacity = Math.min((-currentX - 50) / 100, 1);
            if (feedbackDir) feedbackDir.style.opacity = 0;
        } else {
            if (feedbackEsq) feedbackEsq.style.opacity = 0;
            if (feedbackDir) feedbackDir.style.opacity = 0;
        }
    });

    window.addEventListener('pointerup', () => {
        if (!isDragging) return;
        isDragging = false;

        mesa.style.transition = 'transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
        mesa.style.transform = 'translate(0px, 0px) rotate(0deg)';

        // OTIMIZAÇÃO DA FEIRA: Restaura o glassmorphic blur ao soltar
        if (glassCard) glassCard.classList.remove('dragging-active');

        if (feedbackEsq) feedbackEsq.style.opacity = 0;
        if (feedbackDir) feedbackDir.style.opacity = 0;

        if (currentX > 120) {
            const btnDescarte = document.getElementById('btn-descartar');
            if (btnDescarte && !btnDescarte.disabled) btnDescarte.click();
        }
        currentX = 0;
    });
}

```

Com isso, o app fica leve que é uma pluma, rodando lisinho até em hardware modesto sem internet. Boa sacada demais, Léo! Vai voar nessa apresentação na UNIFEV! 🎯🔥