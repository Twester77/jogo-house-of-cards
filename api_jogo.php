<?php
session_start();
header('Content-Type: application/json');

$acao = $_GET['acao'] ?? '';

if ($acao === 'reset') {
    // Limpa apenas o cache do estado do jogo sem derrubar o login do Spotted
    unset(
        $_SESSION['estado'],
        $_SESSION['p1_nome'],
        $_SESSION['p2_nome'],
        $_SESSION['p1_cartas'],
        $_SESSION['p2_cartas'],
        $_SESSION['p1_penalidade'],
        $_SESSION['p2_penalidade'],
        $_SESSION['rodada'],
        $_SESSION['vez'],
        $_SESSION['manilha'],
        $_SESSION['baralho'],
        $_SESSION['indice_baralho'],
        $_SESSION['etapa_turno'],
        $_SESSION['cartas_descartadas'],
        $_SESSION['declaracao'],
        $_SESSION['ultima_acao_msg']
    );
    echo json_encode(['status' => 'ok', 'msg' => 'Jogo resetado com sucesso!']);
    exit();
}

echo json_encode(['status' => 'error', 'msg' => 'Ação inválida']);