<?php

/*
  Plugin Name: Spamclean
  Description: All websites and blog owners face the problem of unwanted malicious spam. Spamclean WordPress plugin protects your websites/blogs from those constant malicious spam attacks. SpamClean uses real-time technology to protect your website from know spam authors, emails, domains, IPs and words.
  Version: 1.2.3
  Author: ItsGuru, uhpatel, urvihpatel
  Author URI: https://www.itsguru.com
  License: GPL2
  Text Domain: spamclean
 */

if (!defined('ABSPATH'))
    exit;

if (!defined('SPAMCLEAN_URL')) {
    define('SPAMCLEAN_URL', plugin_dir_url(__FILE__));
}

include 'sc_assets/classes/geoiploc.php';

include 'sc_assets/classes/dashboardstates.php';

class Spam_clean {

    public static $spamclean_defaults;
    private static $spamclean_base;
    private static $spamclean_salt;
    private static $spamclean_reason;

    public static function spamclean_init() {

        add_action('unspam_comment', array(__CLASS__, 'spamclean_delete_reason_to_comment'));

        if ((defined('SPAMCLEAN_AJAX') && SPAMCLEAN_AJAX) or ( defined('SPAMCLEAN_AUTOSAVE') && SPAMCLEAN_AUTOSAVE)) {
            return;
        }

        self::spamclean_internal_vars();

        if (defined('SPAMCLEAN_CRON')) {
            add_action('spam_clean_daily_cronjob', array(__CLASS__, 'spamclean_daily_cronjob'));
        } elseif (is_admin()) {

            add_action('admin_menu', array(__CLASS__, 'spamclean_sidebar_menu'));
            if (self::spamclean_current_page('dashboard')) {
                add_action('init', array(__CLASS__, 'spamclean_plugin_lang'));
            } else if (self::spamclean_current_page('plugins')) {
                add_action('init', array(__CLASS__, 'spamclean_plugin_lang'));
            } else if (self::spamclean_current_page('options')) {
                add_action('admin_init', array(__CLASS__, 'spamclean_plugin_lang'));
                add_action('admin_init', array(__CLASS__, 'spamclean_plugin_sources'));
            } else if (self::spamclean_current_page('admin-post')) {
                require_once( dirname(__FILE__) . '/sc_assets/classes/spamclean.class.php' );

                add_action('admin_post_ias_save_changes', array('Spam_clean_GUI', 'save_changes'));
            } else if (self::spamclean_current_page('edit-comments')) {
                if (!empty($_GET['comment_status']) && $_GET['comment_status'] === 'spam' && !self::get_option('no_notice')) {
                    require_once( dirname(__FILE__) . '/sc_assets/classes/spamclean.columns.php' );
                    self::spamclean_plugin_lang();
                    add_filter('manage_edit-comments_columns', array('Spam_clean_Columns', 'register_plugin_columns'));
                    add_filter('manage_comments_custom_column', array('Spam_clean_Columns', 'print_plugin_column'), 10, 2);
                    add_filter('admin_print_styles-edit-comments.php', array('Spam_clean_Columns', 'print_column_styles'));
                    add_filter('manage_edit-comments_sortable_columns', array('Spam_clean_Columns', 'register_sortable_columns'));
                    add_action('pre_get_posts', array('Spam_clean_Columns', 'set_orderby_query'));
                }
            }
        } else {
            add_action('template_redirect', array(__CLASS__, 'spamclean_precomment_field'));
            add_action('init', array(__CLASS__, 'spamclean_incoming_request'));
            add_action('preprocess_comment', array(__CLASS__, 'spamclean_hnd_inc_request'), 1);
            add_action('sc_count', array(__CLASS__, 'spamclean_spam_main_count'));
        }
    }

    public static function spamclean_activate() {
        add_option('spam_clean', array(), '', 'no');
        if (self::get_option('cronjob_enable')) {
            self::spamclean_scheduled_hook();
        }
    }

    public static function spamclean_deactivate() {
        self::spamclean_clear_scheduled_hook();
    }

    public static function spamclean_uninstall() {
        global $wpdb;
        delete_option('spam_clean');
        $wpdb->query("OPTIMIZE TABLE `" . $wpdb->options . "`");
    }

    private static function spamclean_internal_vars() {
        self::$spamclean_base = plugin_basename(__FILE__);

        $spam_nonce = defined('NONCE_SPAMCLEAN') ? NONCE_SPAMCLEAN : ABSPATH;
        self::$spamclean_salt = substr(sha1($spam_nonce), 0, 10);

        self::$spamclean_defaults = array('options' => array('advanced_check' => 1, 'regexp_check' => 1, 'spam_ip' => 1, 'already_commented' => 1, 'gravatar_check' => 0, 'ignore_pings' => 0, 'always_allowed' => 0, 'country_code' => 0, 'country_black' => '', 'country_white' => '', 'translate_api' => 0, 'translate_lang' => '', 'dnsbl_check' => 0, 'bbcode_check' => 1, 'flag_spam' => 1, 'email_notify' => 1, 'no_notice' => 0, 'cronjob_enable' => 0, 'cronjob_interval' => 0, 'ignore_filter' => 0, 'ignore_type' => 0, 'reasons_enable' => 0, 'ignore_reasons' => array(),), 'reasons' => array('css' => esc_attr__('CSS Hack', 'spamclean'), 'time' => esc_attr__('Comment time', 'spamclean'), 'empty' => esc_attr__('Empty Data', 'spamclean'), 'server' => esc_attr__('Fake IP', 'spamclean'), 'localdb' => esc_attr__('Local DB Spam', 'spamclean'), 'country' => esc_attr__('Country Check', 'spamclean'), 'dnsbl' => esc_attr__('Public Spam Database', 'spamclean'), 'bbcode' => esc_attr__('BBCode', 'spamclean'), 'lang' => esc_attr__('Comment Language', 'spamclean'), 'regexp' => esc_attr__('Regular Expression', 'spamclean')));
    }

    public static function spamclean_get_key($array, $key) {
        if (empty($array) or empty($key) or empty($array[$key])) {
            return null;
        }

        return $array[$key];
    }

    private static function spamclean_current_page($spam_page) {
        switch ($spam_page) {
            case 'dashboard': return ( empty($GLOBALS['pagenow']) or ( !empty($GLOBALS['pagenow']) && $GLOBALS['pagenow'] == 'index.php' ) );

            case 'options': return (!empty($_GET['page']) && $_GET['page'] == 'spam_clean' );

            case 'plugins': return (!empty($GLOBALS['pagenow']) && $GLOBALS['pagenow'] == 'plugins.php' );

            case 'admin-post': return (!empty($GLOBALS['pagenow']) && $GLOBALS['pagenow'] == 'admin-post.php' );

            case 'edit-comments': return (!empty($GLOBALS['pagenow']) && $GLOBALS['pagenow'] == 'edit-comments.php' );

            default: return false;
        }
    }

    public static function spamclean_plugin_lang() {
        load_plugin_textdomain('spamclean', false, 'spamclean/lang');
    }

    public static function spamclean_action_links($spamclean_data) {
        if (!current_user_can('manage_options')) {
            return $spamclean_data;
        }

        return array_merge($spamclean_data, array(sprintf('<a href="%s">%s</a>', add_query_arg(array('page' => 'spam_clean'), admin_url('options-general.php')), esc_attr__('Settings', 'spamclean'))));
    }

    public static function spamclean_plugin_sources() {
        $spam_plugin = get_plugin_data(__FILE__);
        wp_register_script('ias_script', SPAMCLEAN_URL . 'sc_assets/js/spamscripts.js', __FILE__, array('jquery'), $spam_plugin['Version']);
        wp_enqueue_style('ias_style', SPAMCLEAN_URL . 'sc_assets/css/spamcleanstyles.css', __FILE__);
    }

    public static function spamclean_sidebar_menu() {
        $spam_icon = SPAMCLEAN_URL . "sc_assets/img/spamcleanicon.png";
        $spam_page = add_menu_page('Spamclean', 'Spamclean', 'manage_options', 'spam_clean', array('Spam_clean_GUI', 'options_page'), $spam_icon);
        add_action('admin_print_scripts-' . $spam_page, array(__CLASS__, 'spamclean_options_script'));
        add_action('admin_print_styles-' . $spam_page, array(__CLASS__, 'spamclean_options_style'));
        add_action('load-' . $spam_page, array(__CLASS__, 'spamclean_options_page'));
    }

    public static function spamclean_options_script() {
        wp_enqueue_script('ias_script');
    }

    public static function spamclean_options_style() {
        wp_enqueue_style('ias_style');
    }

    public static function spamclean_options_page() {
        require_once( dirname(__FILE__) . '/sc_assets/classes/spamclean.class.php' );
    }

    public static function spamclean_get_options() {
        if (!$options = wp_cache_get('spam_clean')) {
            wp_cache_set('spam_clean', $options = get_option('spam_clean'));
        }

        return wp_parse_args($options, self::$spamclean_defaults['options']);
    }

    public static function get_option($spam_field) {
        $options = self::spamclean_get_options();

        return self::spamclean_get_key($options, $spam_field);
    }

    private static function spamclean_update_option($spam_field, $value) {
        self::spamclean_update_options(array($spam_field => $value));
    }

    public static function spamclean_update_options($spamclean_data) {
        $options = get_option('spam_clean');
        if (is_array($options)) {
            $options = array_merge($options, $spamclean_data);
        } else {
            $options = $spamclean_data;
        } update_option('spam_clean', $options);
        wp_cache_set('spam_clean', $options);
    }

    public static function spamclean_daily_cronjob() {
        if (!self::get_option('cronjob_enable')) {
            return;
        } self::spamclean_update_option('cronjob_timestamp', time());
        self::spamclean_delete_old_spam();
    }

    private static function spamclean_delete_old_spam() {
        $days = (int) self::get_option('cronjob_interval');
        if (empty($days)) {
            return false;
        } global $wpdb;
        $wpdb->query($wpdb->prepare("DELETE FROM `$wpdb->comments` WHERE `comment_approved` = 'spam' AND SUBDATE(NOW(), %d) > comment_date_gmt", $days));
        $wpdb->query("OPTIMIZE TABLE `$wpdb->comments`");
    }

    public static function spamclean_scheduled_hook() {
        if (!wp_next_scheduled('spam_clean_daily_cronjob')) {
            wp_schedule_event(time(), 'daily', 'spam_clean_daily_cronjob');
        }
    }

    public static function spamclean_clear_scheduled_hook() {
        if (wp_next_scheduled('spam_clean_daily_cronjob')) {
            wp_clear_scheduled_hook('spam_clean_daily_cronjob');
        }
    }

    public static function spamclean_incoming_request() {
        if (is_feed() OR is_trackback() OR empty($_POST) OR self::spamclean_mobile()) {
            return;
        } $request_uri = self::spamclean_get_key($_SERVER, 'REQUEST_URI');
        $request_path = parse_url($request_uri, PHP_URL_PATH);
        if (strpos($request_path, 'wp-comments-post.php') === false) {
            return;
        }

        $post_id = (int) self::spamclean_get_key($_POST, 'comment_post_ID');
        $hidden_field = self::spamclean_get_key($_POST, 'comment');
        $plugin_field = self::spamclean_get_key($_POST, self::spamclean_secret_name($post_id));
        if (empty($hidden_field) && !empty($plugin_field)) {
            $_POST['comment'] = $plugin_field;
            unset($_POST[self::spamclean_secret_name($post_id)]);
        } else {
            $_POST['spamclean_spam_hidden_field'] = 1;
        }
    }

    public static function spamclean_hnd_inc_request($comment) {
        $comment['comment_author_IP'] = self::spamclean_get_client_ip();
        add_filter('pre_comment_user_ip', array(__CLASS__, 'spamclean_get_client_ip'), 1);
        $request_uri = self::spamclean_get_key($_SERVER, 'REQUEST_URI');
        $request_path = parse_url($request_uri, PHP_URL_PATH);
        if (empty($request_path)) {
            return self::spamclean_spam_request($comment, 'empty');
        } $ping = array('types' => array('pingback', 'trackback', 'pings'), 'allowed' => !self::get_option('ignore_pings'));
        if (strpos($request_path, 'wp-comments-post.php') !== false && !empty($_POST)) {
            $status = self::spamclean_verify_comment_request($comment);
            if (!empty($status['reason'])) {
                return self::spamclean_spam_request($comment, $status['reason']);
            }
        } else if (in_array(self::spamclean_get_key($comment, 'comment_type'), $ping['types']) && $ping['allowed']) {
            $status = self::spamclean_verify_trackback_request($comment);
            if (!empty($status['reason'])) {
                return self::spamclean_spam_request($comment, $status['reason'], true);
            }
        }

        return $comment;
    }

    public static function spamclean_precomment_field() {
        if (is_feed() or is_trackback() or is_robots() or self::spamclean_mobile()) {
            return;
        } if (!is_singular() && !self::get_option('always_allowed')) {
            return;
        } ob_start(array('Spam_clean', 'spamclean_replace_comment_field'));
    }

    public static function spamclean_replace_comment_field($spamclean_data) {
        if (empty($spamclean_data)) {
            return;
        } if (!preg_match('#<textarea.+?name=["\']comment["\']#s', $spamclean_data)) {
            return $spamclean_data;
        } return preg_replace_callback('/(?P<all> (?# match the whole textarea tag ) <textarea (?# the opening of the textarea and some optional attributes ) ( (?# match a id attribute followed by some optional ones and the name attribute ) (?P<before1>[^>]*) (?P<id1>id=["\'](?P<id_value1>[^>"\']*)["\']) (?P<between1>[^>]*) name=["\']comment["\'] | (?# match same as before, but with the name attribute before the id attribute ) (?P<before2>[^>]*) name=["\']comment["\'] (?P<between2>[^>]*) (?P<id2>id=["\'](?P<id_value2>[^>"\']*)["\']) | (?# match same as before, but with no id attribute ) (?P<before3>[^>]*) name=["\']comment["\'] (?P<between3>[^>]*) ) (?P<after>[^>]*) (?# match any additional optional attributes ) ><\/textarea> (?# the closing of the textarea ) )/x', array('Spam_clean', 'spamclean_replace_comment_field_callback'), $spamclean_data, 1);
    }

    public static function spamclean_replace_comment_field_callback($spamclean_matches) {
        if (self::get_option('time_check')) {
            $init_time_field = sprintf('<input type="hidden" name="ias_init_time" value="%d" />', time());
        } else {
            $init_time_field = '';
        }

        $output = '<textarea ' . $spamclean_matches['before1'] . $spamclean_matches['before2'] . $spamclean_matches['before3'];

        $id_script = '';
        if (!empty($spamclean_matches['id1']) || !empty($spamclean_matches['id2'])) {
            $output .= 'id="' . self::spamclean_get_sec_id(get_the_ID()) . '" ';
            $id_script = '<script type="text/javascript">document.getElementById("comment").setAttribute( "id", "' . esc_js(md5(time())) . '" );document.getElementById("' . esc_js(self::spamclean_get_sec_id(get_the_ID())) . '").setAttribute( "id", "comment" );</script>';
        }

        $output .= ' name="' . esc_attr(self::spamclean_secret_name(get_the_ID())) . '" ';
        $output .= $spamclean_matches['between1'] . $spamclean_matches['between2'] . $spamclean_matches['between3'];
        $output .= $spamclean_matches['after'] . '>';
        $output .= '</textarea><textarea id="comment" aria-hidden="true" name="comment" style="width:10px !important;position:absolute !important;left:-10000000px !important"></textarea>';
        $output .= $id_script;
        $output .= $init_time_field;

        return $output;
    }

    private static function spamclean_verify_trackback_request($comment) {
        $ip = self::spamclean_get_key($comment, 'comment_author_IP');
        $url = self::spamclean_get_key($comment, 'comment_author_url');
        $body = self::spamclean_get_key($comment, 'comment_content');
        if (empty($url) OR empty($body)) {
            return array('reason' => 'empty');
        } if (empty($ip)) {
            return array('reason' => 'empty');
        } $options = self::spamclean_get_options();
        if ($options['bbcode_check'] && self::spamclean_bbcode_spam($body)) {
            return array('reason' => 'bbcode');
        } if ($options['advanced_check'] && self::spamclean_fake_ip($ip, parse_url($url, PHP_URL_HOST))) {
            return array('reason' => 'server');
        } if ($options['spam_ip'] && self::spamclean_db_spam($ip, $url)) {
            return array('reason' => 'localdb');
        } if ($options['dnsbl_check'] && self::spamclean_dnsbl_spam($ip)) {
            return array('reason' => 'dnsbl');
        } if ($options['country_code'] && self::spamclean_country_spam($ip)) {
            return array('reason' => 'country');
        } if ($options['translate_api'] && self::spamclean_lang_spam($body)) {
            return array('reason' => 'lang');
        }
    }

    private static function spamclean_verify_comment_request($comment) {
        $ip = self::spamclean_get_key($comment, 'comment_author_IP');
        $url = self::spamclean_get_key($comment, 'comment_author_url');
        $body = self::spamclean_get_key($comment, 'comment_content');
        $email = self::spamclean_get_key($comment, 'comment_author_email');
        $author = self::spamclean_get_key($comment, 'comment_author');
        if (empty($body)) {
            return array('reason' => 'empty');
        } if (empty($ip)) {
            return array('reason' => 'empty');
        } if (get_option('require_name_email') && ( empty($email) OR empty($author) )) {
            return array('reason' => 'empty');
        } $options = self::spamclean_get_options();
        if ($options['already_commented'] && !empty($email) && self::spamclean_approved_email($email)) {
            return;
        } if ($options['gravatar_check'] && !empty($email) && self::spamclean_valid_gravatar($email)) {
            return;
        } if (!empty($_POST['spamclean_spam_hidden_field'])) {
            return array('reason' => 'css');
        } if ($options['time_check'] && self::spamclean_shortest_time()) {
            return array('reason' => 'time');
        } if ($options['bbcode_check'] && self::spamclean_bbcode_spam($body)) {
            return array('reason' => 'bbcode');
        } if ($options['advanced_check'] && self::spamclean_fake_ip($ip)) {
            return array('reason' => 'server');
        } if ($options['regexp_check'] && self::spamclean_regexp_spam(array('ip' => $ip, 'rawurl' => $url, 'host' => parse_url($url, PHP_URL_HOST), 'body' => $body, 'email' => $email, 'author' => $author))) {
            return array('reason' => 'regexp');
        } if ($options['spam_ip'] && self::spamclean_db_spam($ip, $url, $email)) {
            return array('reason' => 'localdb');
        } if ($options['dnsbl_check'] && self::spamclean_dnsbl_spam($ip)) {
            return array('reason' => 'dnsbl');
        } if ($options['country_code'] && self::spamclean_country_spam($ip)) {
            return array('reason' => 'country');
        } if ($options['translate_api'] && self::spamclean_lang_spam($body)) {
            return array('reason' => 'lang');
        }
    }

    private static function spamclean_valid_gravatar($email) {
        $response = wp_safe_remote_get(sprintf('https://www.gravatar.com/avatar/%s?d=404', md5(strtolower(trim($email)))));

        if (is_wp_error($response)) {
            return null;
        }

        if (wp_remote_retrieve_response_code($response) === 200) {
            return true;
        }

        return false;
    }

    private static function spamclean_shortest_time() {
        if (!$init_time = (int) self::spamclean_get_key($_POST, 'ias_init_time')) {
            return false;
        } if (time() - $init_time < apply_filters('ias_action_time_limit', 5)) {
            return true;
        }

        return false;
    }

    private static function spamclean_regexp_spam($comment) {
        $spam_fields = array(
            'ip',
            'host',
            'body',
            'email',
            'author',
        );
        $spamclean_patterns = array(
            array
                (
                'host' => '^(www\.)?\d+\w+\.com$',
                'body' => '^\w+\s\d+$',
                'email' => '@gmail.com$',
            ),
            array(
                'body' => '\<\!.+?mfunc.+?\>',),
            array(
                'author' => 'moncler|north face|vuitton|handbag|burberry|outlet|prada|cialis|viagra|maillot|oakley|ralph lauren|ray ban|iphone|プラダ',),
            array(
                'host' => '^(www\.)?fkbook\.co\.uk$|^(www\.)?nsru\.net$|^(www\.)?goo\.gl$|^(www\.)?bit\.ly$',),
            array(
                'body' => 'target[t]?ed (visitors|traffic)|viagra|cialis',),
            array(
                'body' => 'dating|sex|lotto|pharmacy',
                'email' => '@mail\.ru|@yandex\.',
            ),
        );
        if ($quoted_author = preg_quote($comment['author'], '/')) {
            $spamclean_patterns[] = array(
                'body' => sprintf('<a.+?>%s<\/a>$', $quoted_author),
            );
            $spamclean_patterns[] = array(
                'body' => sprintf('%s https?:.+?$', $quoted_author),
            );
            $spamclean_patterns[] = array(
                'email' => '@gmail.com$',
                'author' => '^[a-z0-9-\.]+\.[a-z]{2,6}$',
                'host' => sprintf('^%s$', $quoted_author),
            );
        } $spamclean_patterns = apply_filters(
                'spam_clean_patterns', $spamclean_patterns
        );
        if (!$spamclean_patterns) {
            return false;
        } foreach ($spamclean_patterns as $spamclean_pattern) {
            $spamclean_hits = array();
            foreach ($spamclean_pattern as $spam_field => $spam_regexp) {
                if (empty($spam_field) OR ! in_array($spam_field, $spam_fields) OR empty($spam_regexp)) {
                    continue;
                } $comment[$spam_field] = ( function_exists('iconv') ? iconv('utf-8', 'utf-8//TRANSLIT', $comment[$spam_field]) : $comment[$spam_field] );
                if (empty($comment[$spam_field])) {
                    continue;
                } if (@preg_match('/' . $spam_regexp . '/isu', $comment[$spam_field])) {
                    $spamclean_hits[$spam_field] = true;
                }
            }

            if (count($spamclean_hits) === count($spamclean_pattern)) {
                return true;
            }
        }

        return false;
    }

    private static function spamclean_db_spam($ip, $url = '', $email = '') {
        global $wpdb;
        $spam_filter = array('`comment_author_IP` = %s');
        $spam_params = array(wp_unslash($ip));
        if (!empty($url)) {
            $spam_filter[] = '`comment_author_url` = %s';
            $spam_params[] = wp_unslash($url);
        } if (!empty($email)) {
            $spam_filter[] = '`comment_author_email` = %s';
            $spam_params[] = wp_unslash($email);
        } $result = $wpdb->get_var($wpdb->prepare(sprintf("SELECT `comment_ID` FROM `$wpdb->comments` WHERE `comment_approved` = 'spam' AND (%s) LIMIT 1", implode(' OR ', $spam_filter)), $spam_params));

        return !empty($result);
    }

    private static function spamclean_country_spam($ip) {
        $options = self::spamclean_get_options();
        $white = preg_split('/[\s,;]+/', $options['country_white'], -1, PREG_SPLIT_NO_EMPTY);
        $black = preg_split('/[\s,;]+/', $options['country_black'], -1, PREG_SPLIT_NO_EMPTY);
        if (empty($white) && empty($black)) {
            return false;
        } $response = wp_safe_remote_head(esc_url_raw(sprintf('https://api.ip2country.info/ip?%s', self::spamclean_anonymize_ip($ip)), 'https'));
        if (is_wp_error($response)) {
            return false;
        } if (wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        } $country = (string) wp_remote_retrieve_header($response, 'x-country-code');
        if (empty($country) OR strlen($country) !== 2) {
            return false;
        } if (!empty($black)) {
            return ( in_array($country, $black) );
        } return (!in_array($country, $white) );
    }

    private static function spamclean_dnsbl_spam($ip) {
        $response = wp_safe_remote_request(esc_url_raw(sprintf('http://www.stopforumspam.com/api?ip=%s&f=json', $ip), 'http'));
        if (is_wp_error($response)) {
            return false;
        } $json = wp_remote_retrieve_body($response);
        $result = json_decode($json);
        if (empty($result->success)) {
            return false;
        } return (bool) $result->ip->appears;
    }

    private static function spamclean_bbcode_spam($body) {
        return (bool) preg_match('/\[url[=\]].*\[\/url\]/is', $body);
    }

    private static function spamclean_approved_email($email) {
        global $wpdb;
        $result = $wpdb->get_var($wpdb->prepare("SELECT `comment_ID` FROM `$wpdb->comments` WHERE `comment_approved` = '1' AND `comment_author_email` = %s LIMIT 1", wp_unslash($email)));
        if ($result) {
            return true;
        }

        return false;
    }

    private static function spamclean_fake_ip($client_ip, $client_host = false) {
        $host_by_ip = gethostbyaddr($client_ip);
        if (self::spamclean_ipv6($client_ip)) {
            return $client_ip != $host_by_ip;
        } if (empty($client_host)) {
            $ip_by_host = gethostbyname($host_by_ip);

            if ($ip_by_host === $host_by_ip) {
                return false;
            }
        } else {
            if ($host_by_ip === $client_ip) {
                return true;
            }

            $ip_by_host = gethostbyname($client_host);
        }

        if (strpos($client_ip, self::spamclean_cut_ip($ip_by_host)) === false) {
            return true;
        }

        return false;
    }

    private static function spamclean_lang_spam($comment_content) {
        $spam_allowed_lang = self::get_option('translate_lang');
        $comment_text = wp_strip_all_tags($comment_content);
        if (empty($spam_allowed_lang) || empty($comment_text)) {
            return false;
        } if (!$query_text = wp_trim_words($comment_text, 10, '')) {
            return false;
        } $key = apply_filters('ias_google_translate_api_key', base64_decode(strrev('B9GcXFjbjdULkdDUfh1SOlzZ2FzMhF1Mt1kRWVTWoVHR5NVY6lUQ')));
        $response = wp_safe_remote_request(add_query_arg(array('q' => rawurlencode($query_text), 'key' => $key,), 'https://www.googleapis.com/language/translate/v2/detect'));
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        } if (!$json = wp_remote_retrieve_body($response)) {
            return false;
        } if (!$data_array = json_decode($json, true)) {
            return false;
        } if (!$spam_detected_lang = @$data_array['data']['detections'][0][0]['language']) {
            return false;
        }

        return ( $spam_detected_lang != $spam_allowed_lang );
    }

    private static function spamclean_cut_ip($ip, $cut_end = true) {
        $separator = ( self::spamclean_ipv4($ip) ? '.' : ':' );

        return str_replace(( $cut_end ? strrchr($ip, $separator) : strstr($ip, $separator)), '', $ip);
    }

    private static function spamclean_anonymize_ip($ip) {
        if (self::spamclean_ipv4($ip)) {
            return self::spamclean_cut_ip($ip) . '.0';
        }

        return self::spamclean_cut_ip($ip, false) . ':0:0:0:0:0:0:0';
    }

    private static function spamclean_ipv4($ip) {
        if (function_exists('filter_var')) {
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false;
        } else {
            return preg_match('/^\d{1,3}(\.\d{1,3}){3,3}$/', $ip);
        }
    }

    private static function spamclean_ipv6($ip) {
        if (function_exists('filter_var')) {
            return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false;
        } else {
            return !self::spamclean_ipv4($ip);
        }
    }

    private static function spamclean_mobile() {
        return strpos(TEMPLATEPATH, 'wptouch');
    }

    private static function spamclean_spam_request($comment, $reason, $is_ping = false) {
        $options = self::spamclean_get_options();
        $spam_remove = !$options['flag_spam'];
        $spam_notice = !$options['no_notice'];
        $ignore_filter = $options['ignore_filter'];
        $ignore_type = $options['ignore_type'];
        $ignore_reason = in_array($reason, (array) $options['ignore_reasons']);
        self::spamclean_update_spam_log($comment);
        self::spamclean_update_spam_count();
        self::spamclean_update_daily_stats();
        if ($spam_remove) {
            self::spamclean_go_in_peace();
        } if ($ignore_filter && (( $ignore_type == 1 && $is_ping ) or ( $ignore_type == 2 && !$is_ping ))) {
            self::spamclean_go_in_peace();
        } if ($ignore_reason) {
            self::spamclean_go_in_peace();
        } self::$spamclean_reason = $reason;
        add_filter('pre_comment_approved', create_function('', 'return "spam";'));
        add_filter('trackback_post', array(__CLASS__, 'spamclean_mail_notification'));
        add_filter('comment_post', array(__CLASS__, 'spamclean_mail_notification'));
        if ($spam_notice) {
            add_filter('comment_post', array(__CLASS__, 'spamclean_reason_to_comment'));
        }

        return $comment;
    }

    private static function spamclean_update_spam_log($comment) {
        if (!defined('SPAMCLEAN_LOG_FILE') OR ! SPAMCLEAN_LOG_FILE OR ! is_writable(SPAMCLEAN_LOG_FILE) OR validate_file(SPAMCLEAN_LOG_FILE) === 1) {
            return false;
        } $entry = sprintf('%s comment for post=%d from host=%s marked as spam%s', current_time('mysql'), $comment['comment_post_ID'], $comment['comment_author_IP'], PHP_EOL);
        file_put_contents(SPAMCLEAN_LOG_FILE, $entry, FILE_APPEND | LOCK_EX);
    }

    private static function spamclean_go_in_peace() {
        status_header(403);
        //die('Spam deleted.');
        wp_redirect(home_url());
    }

    public static function spamclean_get_client_ip() {
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ip = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        } else {
            return '';
        }

        if (strpos($ip, ',') !== false) {
            $ips = explode(',', $ip);
            $ip = trim(@$ips[0]);
        }

        if (function_exists('filter_var')) {
            return filter_var($ip, FILTER_VALIDATE_IP);
        }

        return preg_replace('/[^0-9a-f:\., ]/si', '', $ip);
    }

    public static function spamclean_reason_to_comment($comment_id) {
        add_comment_meta($comment_id, 'spam_clean_reason', self::$spamclean_reason);
    }

    public static function spamclean_delete_reason_to_comment($comment_id) {
        delete_comment_meta($comment_id, 'spam_clean_reason');
    }

    public static function spamclean_mail_notification($id) {
        $options = self::spamclean_get_options();
        if (!$options['email_notify']) {
            return $id;
        } $comment = get_comment($id, ARRAY_A);
        if (empty($comment)) {
            return $id;
        } if (!$post = get_post($comment['comment_post_ID'])) {
            return $id;
        } self::spamclean_plugin_lang();
        $subject = sprintf('[%s] %s', stripslashes_deep(html_entity_decode(get_bloginfo('name'), ENT_QUOTES)), esc_html__('Comment marked as spam', 'spamclean'));
        if (!$content = strip_tags(stripslashes($comment['comment_content']))) {
            $content = sprintf('-- %s --', esc_html__('Content removed by Spamclean', 'spamclean'));
        } $body = sprintf("%s \"%s\"\r\n\r\n", esc_html__('New spam comment on your post', 'spamclean'), strip_tags($post->post_title)) . sprintf("%s: %s\r\n", esc_html__('Author', 'spamclean'), ( empty($comment['comment_author']) ? '' : strip_tags($comment['comment_author']))) . sprintf("URL: %s\r\n", esc_url($comment['comment_author_url'])) . sprintf("%s: %s\r\n", esc_html__('Type', 'spamclean'), esc_html__(( empty($comment['comment_type']) ? 'Comment' : 'Trackback'), 'spamclean')) . sprintf("Whois: http://whois.arin.net/rest/ip/%s\r\n", $comment['comment_author_IP']) . sprintf("%s: %s\r\n\r\n", esc_html__('Spam Reason', 'spamclean'), esc_html__(self::$spamclean_defaults['reasons'][self::$spamclean_reason], 'spamclean')) . sprintf("%s\r\n\r\n\r\n", $content) . ( EMPTY_TRASH_DAYS ? ( sprintf("%s: %s\r\n", esc_html__('Trash it', 'spamclean'), admin_url('comment.php?action=trash&c=' . $id)) ) : ( sprintf("%s: %s\r\n", esc_html__('Delete it', 'spamclean'), admin_url('comment.php?action=delete&c=' . $id)) ) ) . sprintf("%s: %s\r\n", esc_html__('Approve it', 'spamclean'), admin_url('comment.php?action=approve&c=' . $id)) . sprintf("%s: %s\r\n\r\n", esc_html__('Spam list', 'spamclean'), admin_url('edit-comments.php?comment_status=spam')) . sprintf("%s\r\n%s\r\n", esc_html__('Notification from SpamClean - By Itsguru', 'spamclean'), esc_html__('http://itsguru.com', 'spamclean'));
        wp_mail(get_bloginfo('admin_email'), apply_filters('spam_clean_notification_subject', $subject), $body);

        return $id;
    }

    public static function spamclean_spam_count() {
        echo esc_html(self::spamclean_spam_count());
    }

    private static function spamclean_update_spam_count() {
        if (!self::get_option('dashboard_count')) {
            return;
        }

        self::spamclean_update_option('spam_count', intval(self::get_option('spam_count') + 1));
    }

    private static function spamclean_update_daily_stats() {
        if (!self::get_option('dashboard_chart')) {
            return;
        } $spam_stats = (array) self::get_option('daily_stats');
        $spam_today = (int) strtotime('today');
        if (array_key_exists($spam_today, $spam_stats)) {
            $spam_stats[$spam_today] ++;
        } else {
            $spam_stats[$spam_today] = 1;
        } krsort($spam_stats, SORT_NUMERIC);
        self::spamclean_update_option('daily_stats', array_slice($spam_stats, 0, 31, true));
    }

    public static function spamclean_secret_name($post_id) {

        $spam_secret = substr(sha1(md5(self::$spamclean_salt . get_the_title((int) $post_id))), 0, 10);
        return apply_filters('ias_get_sc_secret_name_for_post', $spam_secret, (int) $post_id);
    }

    public static function spamclean_get_sec_id($post_id) {

        $spam_secret = substr(sha1(md5('comment-id' . self::$spamclean_salt . get_the_title((int) $post_id))), 0, 10);
        return apply_filters('ias_get_sc_secret_id_for_post', $spam_secret, (int) $post_id);
    }

}

add_action('plugins_loaded', array('Spam_clean', 'spamclean_init'));
register_activation_hook(__FILE__, array('Spam_clean', 'spamclean_activate'));
register_deactivation_hook(__FILE__, array('Spam_clean', 'spamclean_deactivate'));
register_uninstall_hook(__FILE__, array('Spam_clean', 'spamclean_uninstall'));
