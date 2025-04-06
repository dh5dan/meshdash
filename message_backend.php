<?php

require_once 'dbinc/param.php';
require_once 'include/func_php_core.php';
require_once 'include/func_php_message_backend.php';

header('Content-Type: application/json');

$group = $_POST['group'] ?? 0;
$htmlBlocks = getLatestMessages($group);

$htmlContent = '';
foreach ($htmlBlocks as $htmlBlock) {
    $htmlContent .= $htmlBlock;
}

echo json_encode([
    "html" => $htmlContent,
    "messages" => array_map(fn($html) => ["msg" => $html], $htmlBlocks),
    "playSound" => false,
    "scrollToBottom" => false
]);
