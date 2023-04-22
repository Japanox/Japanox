<?php

/**
 * Strings for translation in English
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
    'DARKFOX_TORRENTS_ENABLE'       => 'Enable Torrents',
    'DARKFOX_TORRENTS_DISABLE'      => 'Disable Torrents',
    'DARKFOX_TORRENTS_NEW'          => 'New Torrent added',
    'DARKFOX_TORRENTS_SUBJECT'      => 'New Torrent added',
    'DARKFOX_TORRENTS_MESSAGE'      => "Hello,\n\nA new Torrent has been added in the topic %s.\n\nTorrent name: %s\nTorrent size: %s\nTorrent type: %s\nSeeders: %d\nLeechers: %d\n\nBest regards,\n%s",
));

?>
