<?php
if (!defined('ABSPATH'))
    exit;

class Spam_clean_GUI extends Spam_clean {

    public static function save_changes() {

        if (empty($_POST)) {
            wp_die(esc_html__('Cheatin&#8217; uh?', 'spamclean'));
        }

        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('Cheatin&#8217; uh?', 'spamclean'));
        }

        check_admin_referer('_spamclean__settings_nonce');

        $options = array(
            'flag_spam' => (int) (!empty($_POST['ias_flag_spam'])),
            'email_notify' => (int) (!empty($_POST['ias_email_notify'])),
            'cronjob_enable' => (int) (!empty($_POST['ias_cronjob_enable'])),
            'cronjob_interval' => (int) self::spamclean_get_key($_POST, 'ias_cronjob_interval'),
            'no_notice' => (int) (!empty($_POST['ias_no_notice'])),
            'dashboard_count' => (int) (!empty($_POST['ias_dashboard_count'])),
            'dashboard_chart' => (int) (!empty($_POST['ias_dashboard_chart'])),
            'advanced_check' => (int) (!empty($_POST['ias_advanced_check'])),
            'regexp_check' => (int) (!empty($_POST['ias_regexp_check'])),
            'spam_ip' => (int) (!empty($_POST['ias_spam_ip'])),
            'already_commented' => (int) (!empty($_POST['ias_already_commented'])),
            'time_check' => (int) (!empty($_POST['ias_time_check'])),
            'always_allowed' => (int) (!empty($_POST['ias_always_allowed'])),
            'ignore_pings' => (int) (!empty($_POST['ias_ignore_pings'])),
            'ignore_filter' => (int) (!empty($_POST['ias_ignore_filter'])),
            'ignore_type' => (int) self::spamclean_get_key($_POST, 'ias_ignore_type'),
            'reasons_enable' => (int) (!empty($_POST['ias_reasons_enable'])),
            'ignore_reasons' => (array) self::spamclean_get_key($_POST, 'ias_ignore_reasons'),
            'bbcode_check' => (int) (!empty($_POST['ias_bbcode_check'])),
            'gravatar_check' => (int) (!empty($_POST['ias_gravatar_check'])),
            'dnsbl_check' => (int) (!empty($_POST['ias_dnsbl_check'])),
            'country_code' => (int) (!empty($_POST['ias_country_code'])),
            'country_black' => sanitize_text_field(wp_unslash(self::spamclean_get_key($_POST, 'ias_country_black'))),
            'country_white' => sanitize_text_field(wp_unslash(self::spamclean_get_key($_POST, 'ias_country_white'))),
            'translate_api' => (int) (!empty($_POST['ias_translate_api'])),
            'translate_lang' => sanitize_text_field(wp_unslash(self::spamclean_get_key($_POST, 'ias_translate_lang'))),
        );

        foreach ($options['ignore_reasons'] as $key => $val) {
            if (!isset(self::$spamclean_defaults['reasons'][$val])) {
                unset($options['ignore_reasons'][$key]);
            }
        }


        if (empty($options['cronjob_interval'])) {
            $options['cronjob_enable'] = 0;
        }


        if (!empty($options['translate_lang'])) {
            if (!preg_match('/^(de|en|fr|it|es)$/', $options['translate_lang'])) {
                $options['translate_lang'] = '';
            }
        }
        if (empty($options['translate_lang'])) {
            $options['translate_api'] = 0;
        }


        if (empty($options['reasons_enable'])) {
            $options['ignore_reasons'] = array();
        }


        if (!empty($options['country_black'])) {
            $options['country_black'] = preg_replace(
                    '/[^A-Z ,;]/', '', strtoupper($options['country_black'])
            );
        }

        if (!empty($options['country_white'])) {
            $options['country_white'] = preg_replace(
                    '/[^A-Z ,;]/', '', strtoupper($options['country_white'])
            );
        }
        if (empty($options['country_black']) && empty($options['country_white'])) {
            $options['country_code'] = 0;
        }
        if ($options['cronjob_enable'] && !self::get_option('cronjob_enable')) {
            self::spamclean_scheduled_hook();
        } else if (!$options['cronjob_enable'] && self::get_option('cronjob_enable')) {
            self::spamclean_clear_scheduled_hook();
        }
        self::spamclean_update_options($options);
        wp_safe_redirect(
                add_query_arg(
                        array(
            'updated' => 'true'
                        ), wp_get_referer()
                )
        );
        die();
    }

    private static function spam_clean_build_select($name, $spamclean_data, $selected) {
        $html = '<select name="' . $name . '">';
        foreach ($spamclean_data as $k => $v) {
            $html .= '<option value="' . esc_attr($k) . '" ' . selected($selected, $k, false) . '>' . esc_html($v) . '</option>';
        }
        $html .= '</select>';

        return $html;
    }

    public static function options_page() {
        wp_enqueue_script('jquery');
        wp_enqueue_style('spamcleancss', SPAMCLEAN_URL . 'sc_assets/css/spamcleantables.css', __FILE__);
        ?>
        <div class="wrap" id="ias_main">

            <h1 class="spamfilter_title">Spam Clean</h1>
            <hr>
            <?php
            //  print_r($_GET);
            if (isset($_GET) && isset($_GET['token']) && !isset($_GET['err']) && sanitize_text_field($_GET['token']) || sanitize_text_field($_GET['err'])) {
                $token = get_option('spam-clean-token');
                $ex = explode("-", sanitize_text_field($_GET['token']));
                //  print_r($ex);
                if (count($token) == 1) {
                    // print $_GET['token'];
                    update_option('spam-clean-token', sanitize_text_field($_GET['token']));
                    update_option('spam-clean-twitter-username', $ex[0]);
                } else {
                    // print $_GET['token'];
                    add_option('spam-clean-token', sanitize_text_field($_GET['token']));
                    add_option('spam-clean-twitter-username', $ex[0]);
                }
            }
            $token = (isset($_GET['token'])) ? $_GET['token'] : get_option('spam-clean-token');
			
			$token = "123";
			
            if ($token) {
                $spamstyle = "display:none";
                $btn_style = "display:none";
            } else {
                $spamstyle = "display:block";
                $btn_style = "";
            }
            ?>
            <!--Overlay Class-->
            <div class="spam_clean_overlay" style="<?php echo $spamstyle; ?>">
                <?php $current_url = "http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; ?>
                <div class="spam_clean_overlay-content">
                    <div><img src="<?php echo SPAMCLEAN_URL ?>sc_assets/img/padlock.png"></div>
                    <div class="spam_clean_overlay-inst"><h1>Please Login With Twitter</h1></div>
                    <div>
                        <a class="button button-primary spam_clean_twitter_login_link" href="http://www.itsguru.com/spamclean/twitter_oauth/twtconnect.php?source=wp&ref=<?php echo $current_url; ?>" >
                            Login With Twitter
                        </a>
                    </div>
                    <?php
                    if (isset($_GET['err'])) {
                        ?>
                        <div class="spam_clean_error_overlay">
                            This Twitter account is already linked with another website. Please login with another account.
                        </div>
                        <?php
                    }
                    ?>
                </div>
            </div>
            <div class="spamclean_overlay">
                <div class="scspamnav">
                    <div class="scmaintitle" style="padding: 0;height: 54px;">
                        <label for="option1" class="">
                            <?php
                            $icon = SPAMCLEAN_URL . "sc_assets/img/spamcleanlogohorizontal.jpg";
                            ?>
                            <img src="<?php echo $icon; ?>" style="width: 216px;height: 54px;padding: 0;">
                        </label>
                    </div>
                    <div class="scdashboardforspam scdashboardlabel scoverlaydash" style="cursor: pointer;">
                        <label for="option0" class="scspamlabel">Dashboard</label>
                    </div>
                    <div class="scaddforspam scfilterlabel scoverlayfil" style="cursor: pointer;">
                        <label for="option1" class="scspamlabel">Basic Filter</label>
                    </div>
                    <div class="scadvancedforspam scadvancedlabel scoverlayadv" style="cursor: pointer;">
                        <label for="option2" class="scspamlabel">Advanced Filter</label>
                    </div>
                    <div class="scblockforspam scblocklabel scoverlayblo" style="cursor: pointer;">
                        <label for="option2" class="scspamlabel">Block Filter</label>
                    </div>

                </div>  
                <form action="<?php echo admin_url('admin-post.php') ?>" method="post">
                    <input type="hidden" name="action" value="ias_save_changes" />
                    <?php wp_nonce_field('_spamclean__settings_nonce') ?>
                    <?php $options = self::spamclean_get_options() ?>
                    <section id="section0">
                        <article class="scdashboard">
                            <h2 class="sctitleback">Dashboard</h2>
                            <div class="ias-column ias-arrow">
                                <?php include "spamcleandashboard.php"; ?>
                            </div>
                        </article>
                    </section>
                    <section id="section1">                                                                       
                        <article class="scfilter">
                            <h2 class="sctitleback">Basic filter</h2>                    
                            <div class="ias-column ias-arrow">
                                <?php include "spamclean.php"; ?>
                            </div>    
                        </article>
                    </section>
                    <section id="section2">                 
                        <article class="scadvanced">
                            <h2 class="sctitleback">Advanced filter</h2>
                            <div class="ias-column ias-join">
                                <?php include 'spamcleanadvanced.php'; ?>
                            </div>
                        </article>
                    </section>
                    <section id="section3">                          
                        <article class="scmore">
                            <h2 class="sctitleback">More filter</h2>
                            <div class="ias-column ias-diff">
                                <?php include 'spamcleanmore.php'; ?>
                            </div>
                        </article>
                    </section>
                    <div class="scantibtn" style="float: right;margin-top: 10px;">
                        <input type="submit" class="button button-primary" value="<?php esc_html_e('Save Changes', 'spamclean'); ?>" />
                    </div>
                </form>
            </div>

        </div>

        <?php
    }

}
