<?php
// Recebe o formulário de contato do site e envia para a caixa da NB Contabilidade.
header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

const DESTINO   = 'contato@nbcont.com.br';
const REMETENTE = 'contato@nbcont.com.br';

function responder($ok, $mensagem, $codigo = 200) {
    http_response_code($codigo);
    echo json_encode(['ok' => $ok, 'mensagem' => $mensagem], JSON_UNESCAPED_UNICODE);
    exit;
}

function campo($nome, $max) {
    $valor = isset($_POST[$nome]) ? trim((string) $_POST[$nome]) : '';
    $valor = str_replace(["\r\n", "\r"], "\n", $valor);
    return mb_substr($valor, 0, $max, 'UTF-8');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    responder(false, 'Método não permitido.', 405);
}

// Robôs preenchem o campo escondido ou enviam rápido demais.
if (campo('site', 200) !== '') {
    responder(true, 'Mensagem enviada.');
}
$inicio = (int) campo('inicio', 20);
if ($inicio > 0 && (time() * 1000 - $inicio) < 3000) {
    responder(false, 'Envio muito rápido. Aguarde alguns segundos e tente de novo.', 429);
}

// Limite simples: 5 envios por IP a cada hora.
$ip = $_SERVER['REMOTE_ADDR'] ?? 'desconhecido';
$controle = sys_get_temp_dir() . '/nbcont_form_' . md5($ip);
$envios = [];
if (is_file($controle)) {
    $envios = array_filter(
        (array) json_decode((string) file_get_contents($controle), true),
        function ($t) { return $t > time() - 3600; }
    );
}
if (count($envios) >= 5) {
    responder(false, 'Muitos envios em sequência. Tente novamente mais tarde ou fale pelo WhatsApp.', 429);
}

$nome     = campo('nome', 120);
$empresa  = campo('empresa', 160);
$email    = campo('email', 160);
$telefone = campo('telefone', 30);
$servico  = campo('servico', 80);
$texto    = campo('mensagem', 3000);

if ($nome === '' || $email === '' || $telefone === '' || $servico === '' || $texto === '') {
    responder(false, 'Preencha todos os campos obrigatórios.', 422);
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || preg_match('/[\r\n]/', $email)) {
    responder(false, 'Informe um e-mail válido.', 422);
}
if (strlen(preg_replace('/\D/', '', $telefone)) < 10) {
    responder(false, 'Informe o WhatsApp com DDD.', 422);
}

$linhaUnica = function ($v) { return preg_replace('/[\r\n]+/', ' ', $v); };

$assunto = 'Contato pelo site: ' . $linhaUnica($nome) . ' — ' . $linhaUnica($servico);
$corpo = "Nova solicitação recebida pelo site nbcont.com.br\n\n"
       . "Nome: {$nome}\n"
       . "Empresa: " . ($empresa !== '' ? $empresa : '-') . "\n"
       . "E-mail: {$email}\n"
       . "WhatsApp: {$telefone}\n"
       . "Serviço: {$servico}\n\n"
       . "Mensagem:\n{$texto}\n\n"
       . "--\nEnviado em " . date('d/m/Y H:i') . " · IP {$ip}\n";

$cabecalhos = [
    'From: NB Contabilidade (site) <' . REMETENTE . '>',
    'Reply-To: ' . $linhaUnica($nome) . ' <' . $email . '>',
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
];

$enviado = mail(
    DESTINO,
    '=?UTF-8?B?' . base64_encode($assunto) . '?=',
    $corpo,
    implode("\r\n", $cabecalhos),
    '-f' . REMETENTE
);

if (!$enviado) {
    responder(false, 'Não foi possível enviar agora. Tente novamente em instantes.', 500);
}

$envios[] = time();
@file_put_contents($controle, json_encode(array_values($envios)));

responder(true, 'Recebemos sua mensagem! Em breve a NB Contabilidade entrará em contato.');
