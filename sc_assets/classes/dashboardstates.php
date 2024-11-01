<?php
add_action('wp_dashboard_setup', 'spamclean_dashboard_states');

function spamclean_dashboard_states() {

    wp_add_dashboard_widget('custom_help_widget', 'Spam Clean', 'spamclean_dashboard_help');
    wp_enqueue_style('spamcleancss', SPAMCLEAN_URL . 'sc_assets/css/dashboardstats.css', __FILE__);
    wp_enqueue_style('iadds_style', SPAMCLEAN_URL . 'sc_assets/classes/spamclean_flags/spamclean_flags.css', __FILE__);
}

function spamclean_dashboard_help() {
    global $wpdb;
    require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
    $charset_collate = $wpdb->get_charset_collate();
    $table_name = $wpdb->prefix . 'comments';
    $itsresult2 = $wpdb->get_results("SELECT * FROM $table_name WHERE comment_approved = 'spam' LIMIT 5");
    ?>
    <div class="scdashtitle" style="text-align: center;border-bottom: 1px solid #eee;">
        <?php
        $spam_icon = SPAMCLEAN_URL . "sc_assets/img/sclogo.png";
        ?>
        <img src="<?php echo $spam_icon; ?>" style="width: 40%;">
    </div>
    <div class="sctopip">
        <h2>Top 5 IPs Blocked</h2>
    </div>
    <table class="sc-striped-table">
        <thead>
            <tr>
                <th>IP</th>
                <th>Country</th>
                <th>Flag</th>
            </tr>
        </thead>
        <?php
        foreach ($itsresult2 as $itsspam2) {

            $ip = $itsspam2->comment_author_IP;
            if (function_exists('getCountryFromIP')) {
                $ip_iso = strtolower(getCountryFromIP($ip, "code"));
                $ip_name = getCountryFromIP($ip, "Name");
            }
            ?>
            <tbody>
                <tr class="odd">
                    <td><code><?php echo $itsspam2->comment_author_IP; ?></code></td>
                    <td><?php echo $ip_name; ?></td>
                    <td><span class="spamcleanflag spamcleanflag-<?php echo $ip_iso; ?>" alt=""/></span></td>
                </tr>
            </tbody>
        <?php } ?>
    </table>
<?php } ?>