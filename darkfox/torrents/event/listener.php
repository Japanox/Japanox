<?php

/**
* Adicionar o gerenciador de eventos para Torrents
*/

namespace darkfox\torrents\event;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class listener implements EventSubscriberInterface
{
    protected $db;
    protected $container;
    
    public function __construct(\phpbb\db\driver\driver_interface $db, \phpbb\user $user, \phpbb\config\config $config, \phpbb\template\template $template, \phpbb\controller\helper $controller_helper, \phpbb\request\request $request, \phpbb\language\language $language, \phpbb\notification\type\type_interface $notification_type, \phpbb\notification\manager $notification_manager, \phpbb\notification\method\method_interface $notification_method, \phpbb\notification\helper $notification_helper, \phpbb\auth\auth $auth, \phpbb\filesystem\filesystem $filesystem, \phpbb\log\log $log, \phpbb\cache\driver\driver_interface $cache)
    {
        $this->db = $db;
        $this->user = $user;
        $this->config = $config;
        $this->template = $template;
        $this->controller_helper = $controller_helper;
        $this->request = $request;
        $this->language = $language;
        $this->notification_type = $notification_type;
        $this->notification_manager = $notification_manager;
        $this->notification_method = $notification_method;
        $this->notification_helper = $notification_helper;
        $this->auth = $auth;
        $this->filesystem = $filesystem;
        $this->log = $log;
        $this->cache = $cache;
    }
    
    static public function getSubscribedEvents()
    {
        return array(
            'core.posting_modify_template_vars' => 'add_torrent_checkbox',
            'core.submit_post_end' => 'submit_torrent_data',
            'core.user_setup' => 'load_language_on_setup',
        );
    }
    
    /**
     * Adicionar a caixa de seleção para Torrents na página de postagem
     *
     * @param array $event
     * @return null
     */
    public function add_torrent_checkbox($event)
    {
        // Adicionar a caixa de seleção para habilitar Torrents
        $event['template_vars']['S_TORRENTS_ENABLED'] = $this->config['torrents_enabled'];
    }
    
    /**
     * Salvar dados adicionais para o Torrent ao enviar uma nova postagem
     *
     * @param array $event
     * @return null
     */
    public function submit_torrent_data($event)
    {
        // Verificar se a caixa de seleção de Torrents foi marcada
        if ($this->request->variable('enable_torrents', 0))
        {
            // Obter o nome do arquivo do torrent
            $torrent_name = $this->request->variable('torrent_name', '');
            
            // Obter o tamanho do arquivo do torrent
            $torrent_file_size = $this->request->variable('torrent_file_size', 0);
            
            // Obter o tipo de arquivo do torrent
            $torrent_file_type = $this->request->variable('torrent_file_type', '');
            
            // Obter o número de seeders do torrent
			$torrent_seeders = $this->request->variable('torrent_seeders', 0);

			// Obter o número de leechers do torrent
			$torrent_leechers = $this->request->variable('torrent_leechers', 0);

			// Salvar metadados adicionais para o torrent
			$torrent_data = array(
			'torrent_name' => $torrent_name,
			'torrent_file_size' => $torrent_file_size,
			'torrent_file_type' => $torrent_file_type,
			'torrent_seeders' => $torrent_seeders,
			'torrent_leechers' => $torrent_leechers,
);

$this->db->sql_query('INSERT INTO ' . TORRENT_TABLE . ' ' . $this->db->sql_build_array('INSERT', $torrent_data));

// Adicionar notificação para novos torrents
if ($this->config['darkfox_torrents_enable_notifications'])
{
    // Obter o gerenciador de configurações de notificação
    $notification_settings_manager = $this->container->get('notification.settings');

    // Obter as configurações de notificação para o usuário
    $notification_data = $notification_settings_manager->get_for_user('darkfox_torrents_new_torrent');

    // Verificar se a notificação está habilitada
    if ($notification_data['enabled'])
    {
        // Enviar notificação por e-mail
        $emailer = $this->container->get('emailer');
        $emailer->set_template('darkfox_torrents_notification');
        $emailer->set_subject($this->user->lang['DARKFOX_TORRENTS_SUBJECT']);
        $emailer->set_to_users($notification_data['users']);

        $message = $this->user->lang['DARKFOX_TORRENTS_MESSAGE'];
        $message = str_replace('{L_TOPIC_NAME}', $topic_data['topic_title'], $message);
        $message = str_replace('{L_DARKFOX_TORRENTS_NAME}', $torrent_name, $message);
        $message = str_replace('{L_DARKFOX_TORRENTS_SIZE}', $this->format_filesize($torrent_file_size), $message);

        $emailer->set_template_vars(array(
            'MESSAGE_TEXT' => $message,
            'SITENAME' => $this->config['sitename'],
        ));

        $emailer->send();
    }
}
