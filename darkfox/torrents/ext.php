<?php
/**
*
* @package darkfox/torrents
* @copyright Copyright (c) DarkFox <https://github.com/DarkFox>
* @license https://opensource.org/licenses/MIT MIT License
*
*/

namespace darkfox\torrents;

class ext extends \phpbb\extension\base
{
    /**
     * @var \phpbb\db\driver\driver_interface
     */
    protected $db;

    /**
     * Constructor
     *
     * @param \phpbb\di\container_builder $container
     * @param \phpbb\db\driver\driver_interface $db
     */
    public function __construct(\phpbb\di\container_builder $container, \phpbb\db\driver\driver_interface $db)
    {
        $this->db = $db;
        parent::__construct($container);
    }

    /**
     * Install the extension
     *
     * @return null
     */
    public function install()
    {
        $this->add_torrent_table();
        $this->add_torrent_notification_type();
        $this->add_acl_option();
    }

    /**
     * Uninstall the extension
     *
     * @return null
     */
    public function uninstall()
    {
        $this->remove_torrent_table();
        $this->remove_torrent_notification_type();
        $this->remove_acl_option();
    }

    /**
     * Enable the extension
     *
     * @return null
     */
    public function enable_step($old_state)
    {
        // Add cron job to update seeders and leechers
        $this->add_cron_job();

        // Add event listener to send notification when a new torrent is added
        $this->add_event_listener();

        // Add permission to manage torrents
        $this->add_acl_role();
    }

    /**
     * Disable the extension
     *
     * @return null
     */
    public function disable_step($old_state)
    {
        // Remove cron job
        $this->remove_cron_job();

        // Remove event listener
        $this->remove_event_listener();

        // Remove permission
        $this->remove_acl_role();
    }

    /**
     * Add a new table to store torrent data
     *
     * @return null
     */
    protected function add_torrent_table()
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . TORRENT_TABLE . ' (
            torrent_id INT UNSIGNED AUTO_INCREMENT,
            topic_id INT UNSIGNED NOT NULL,
            user_id INT UNSIGNED NOT NULL,
            torrent_hash CHAR(40) NOT NULL,
            torrent_name VARCHAR(255) NOT NULL,
            torrent_file_size BIGINT UNSIGNED NOT NULL,
            torrent_file_type VARCHAR(10) NOT NULL,
            torrent_seeders INT UNSIGNED NOT NULL,
            torrent_leechers INT UNSIGNED NOT NULL,
            PRIMARY KEY (torrent_id)
        ) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';

        $this->db->sql_query($sql);
    }

    /**
     * Remove the table that stores torrent data
     *
     * @return null
     */
    protected function remove_torrent_table()
    {
        $sql = 'DROP TABLE IF EXISTS ' . TORRENT_TABLE;

        $this->db->sql_query($sql);
    }

    /**
     * Add a new notification type for new torrents
     *
     * @return null
     */
    protected function add_torrent_notification_type()
   
    /**
     * Add a new notification type for new torrents
     *
     * @return null
     */
    protected function add_torrent_notification_type()
    {
        // Get the notification manager service
        $notification_manager = $this->container->get('notification_manager');

        // Define the notification type key
        $notification_type_key = 'new_torrent';

        // Define the notification type options
        $notification_type_options = array(
            'lang' => array(
                'title' => 'NOTIFICATION_TYPE_NEW_TORRENT',
                'description' => 'NOTIFICATION_TYPE_NEW_TORRENT_EXPLAIN',
            ),
            'category' => 'post',
            'enable' => true,
            'enable_board_default' => true,
            'permission_display' => 'u_viewonline',
        );

        // Add the notification type
        $notification_manager->add_notification_type($notification_type_key, $notification_type_options);
    }

    /**
     * Send a notification to subscribed users when a new torrent is added to a topic
     *
     * @param array $event An array containing the notification event data
     * @return null
     */
    public function send_torrent_notification($event)
    {
        // Get the necessary variables from the event data
        $topic_id = $event['topic_id'];
        $poster_id = $event['poster_id'];
        $torrent_name = $event['torrent_name'];

        // Get the subscribed users for the topic
        $subscribed_users = $this->get_subscribed_users($topic_id);

        // Loop through the subscribed users and send a notification to each one
        foreach ($subscribed_users as $user_id => $username) {
            // Skip the user who posted the torrent
            if ($user_id == $poster_id) {
                continue;
            }

            // Get the user's notification settings
            $notification_settings = $this->get_notification_settings($user_id);

            // Check if the user has enabled notifications for new torrents
            if (isset($notification_settings['new_torrent']) && $notification_settings['new_torrent'] == true) {
                // Send the notification to the user
                $this->send_notification($user_id, 'new_torrent', array('torrent_name' => $torrent_name));
            }
        }
    }

    /**
     * Get the subscribed users for a topic
     *
     * @param int $topic_id The ID of the topic
     * @return array An array containing the user IDs and usernames of the subscribed users
     */
    protected function get_subscribed_users($topic_id)
    {
        // Get the topic subscription manager service
        $topic_subscription_manager = $this->container->get('topic_subscription.manager');

        // Get the topic subscription list for the topic
        $topic_subscription_list = $topic_subscription_manager->get_subscribed_users($topic_id);

        // Extract the user IDs and usernames from the topic subscription list
        $subscribed_users = array();
        foreach ($topic_subscription_list as $subscription) {
            $subscribed_users[$subscription['user_id']] = $subscription['username'];
        }

        return $subscribed_users;
    }

    /**
     * Get a user's notification settings
     *
     * @param int $user_id The ID of the user
     * @return array An array containing the user's notification settings
     */
    protected function get_notification_settings($user_id)
    {
        // Get the notification settings manager service
        $notification_settings_manager = $this->container->get('notification.settings');

        // Get the notification settings for the user
        $user_notifications = $notification_settings_manager->createBuilder($this->name)
            ->enable(true)
            ->option('post', 'notify')
            ->get();

        // Register the new notification type
        $notification_manager->registerNotificationType($this->name, $user_notifications);
    }

    /**
     * Send a notification to subscribed users when a new torrent is added
     *
     * @param \phpbb\event\data $event The event data
     * @return null
     */
    public function send_torrent_notification($event)
    {
        // Get the topic and torrent data
        $topic_id = $event['topic_id'];
        $torrent_data = $event['torrent_data'];

        // Get the subscribed user list
        $subscribed_users = $this->get_subscribed_users($topic_id);

        // Send the notification to each subscribed user
        foreach ($subscribed_users as $user_id => $user_language)
        {
            // Get the user's notification settings
            $notification_settings_manager = $this->container->get('notification.settings');
            $user_notifications = $notification_settings_manager->loadNotificationOptions($user_id);

            // Check if the user is subscribed to this notification type
            if ($user_notifications[$this->name]['post'] == 'notify_with_post')
            {
                // Send the notification
                $notification_builder = $this->notification_manager->createBuilder($this->name, array(
                    'post_subject'      => $this->language->lang('NEW_TORRENT_ADDED_NOTIFICATION_SUBJECT'),
                    'post_text'         => $this->get_notification_text($torrent_data),
                    'from_user_id'      => ANONYMOUS,
                    'from_user_type'    => NOTIFICATION_TYPE_POST,
                    'from_user_ip'      => '',
                    'force'             => true,
                ))
                ->setUsers(array($user_id))
                ->setLanguage($user_language);

                $notification_builder->send();
            }
        }
    }
