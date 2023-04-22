<?php

/**
* Strings para tradução em inglês
*/

if (!defined('IN_PHPBB'))
{
    exit;
}

if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

$lang = array_merge($lang, array(
    'DARKFOX_TORRENTS'              => 'Torrents',
    'DARKFOX_TORRENTS_ENABLE'       => 'Habilitar Torrents',
    'DARKFOX_TORRENTS_DISABLE'      => 'Desabilitar Torrents',
    'DARKFOX_TORRENTS_NEW'          => 'Novo Torrent adicionado',
    'DARKFOX_TORRENTS_SUBJECT'      => 'Novo Torrent adicionado',
    'DARKFOX_TORRENTS_MESSAGE'      => "Olá,\n\nUm novo Torrent foi adicionado no tópico %s.\n\nNome do Torrent: %s\nTamanho do Torrent: %s\nTipo do Torrent: %s\nSeeders: %d\nLeechers: %d\n\nAtenciosamente,\n%s",
));

?>
