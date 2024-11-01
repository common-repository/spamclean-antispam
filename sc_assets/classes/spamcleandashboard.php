<?php
global $wpdb;

require_once (ABSPATH . 'wp-admin/includes/upgrade.php');

$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'comments';

$itsresult = $wpdb->get_results("SELECT * FROM $table_name WHERE comment_approved = 'spam' LIMIT 10");

$comments_count = wp_count_comments();
$pending_comments = $comments_count->moderated;
$aproved_comments = $comments_count->approved;
$spam_comments = $comments_count->spam;
$total_comments = $comments_count->total_comments;

$num_posts = wp_count_posts('post');
$num_posts = $num_posts->publish;
$num_posts = sprintf(__ngettext('%s Post', '%s Posts', $num_posts), number_format_i18n($num_posts));

$num_pages = wp_count_posts('page');
$num_pages = $num_pages->publish;
$num_pages = sprintf(__ngettext('%s Page', '%s Pages', $num_pages), number_format_i18n($num_pages));

$num_cats = wp_count_terms('category');
$num_tags = wp_count_terms('post_tag');

$page_url = admin_url() . "edit.php?post_type=page";
$post_url = admin_url() . "edit.php?post_type=post";
$category_url = admin_url() . "edit-tags.php?taxonomy=category";
$tag_url = admin_url() . "edit-tags.php?taxonomy=post_tag";
$all_url = admin_url() . "edit-comments.php?comment_status = all";
$approved_url = admin_url() . "edit-comments.php?comment_status=approved";
$moderated_url = admin_url() . "edit-comments.php?comment_status=moderated";
$spam_url = admin_url() . "edit-comments.php?comment_status=spam";
$trash_url = admin_url() . "edit-comments.php?comment_status=trash";


wp_enqueue_style('iadds_style', SPAMCLEAN_URL . 'sc_assets/classes/spamclean_flags/spamclean_flags.css', __FILE__);
?>

<ul style="padding: 10px;"> 
    <div class="scspam-row">
        <div class="scspam-col-xs-12">
            <div class="scspam-dashboard-item active">
                <div class="scspam-dashboard-item-inner">
                    <div class="scspam-dashboard-item-content">
                        <div class="scspam-dashboard-item-title">
                            <strong style="font-size: 20px;letter-spacing: 2px;">
                                <?php esc_html_e('Website Stats', 'spamclean'); ?>
                            </strong>
                        </div>
                    </div>
                </div>
                <div class="scspam-dashboard-item-extra">
                    <ul class="scspam-dashboard-item-list">
                        <li>
                            <div>
                                <div class="scspam-ips scspam-ips-7d scspam-hidden">
                                    <table class="scspam-table scspam-table-hover">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th><?php esc_html_e("Title"); ?></th>                                          
                                                <th><?php esc_html_e("Counts"); ?></th>  
                                            </tr>

                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td><a href="<?php echo $post_url; ?>"><?php esc_html_e("No Of Posts"); ?></a></td>
                                                <td><a href="<?php echo $post_url; ?>"><?php echo $num_posts; ?></a></td>
                                                </a>
                                            </tr>    
                                            <tr>
                                                <td>2</td>
                                                <td><a href="<?php echo $page_url; ?>"><?php esc_html_e("No Of Pages"); ?></a></td>
                                                <td><a href="<?php echo $page_url; ?>"><?php echo $num_pages; ?></a></td>
                                            </tr> 
                                            <tr>
                                                <td>3</td>
                                                <td><a href="<?php echo $category_url; ?>"><?php esc_html_e("No Of Categories"); ?></a></td>
                                                <td><a href="<?php echo $category_url; ?>"><?php echo $num_cats; ?></a></td>
                                            </tr> 
                                            <tr>
                                                <td>4</td>
                                                <td><a href="<?php echo $tag_url; ?>"><?php esc_html_e("No Of Tags"); ?></a></td>
                                                <td><a href="<?php echo $tag_url; ?>"><?php echo $num_tags; ?></a></td>
                                            </tr> 
                                            <tr>
                                                <td>5</td>
                                                <td><a href="<?php echo $approved_url; ?>"><?php esc_html_e("No Of Approved Comments"); ?></a></td>
                                                <td><a href="<?php echo $approved_url; ?>"><?php echo $aproved_comments; ?></a></td>
                                            </tr> 
                                            <tr>
                                                <td>6</td>
                                                <td><a href="<?php echo $moderated_url; ?>"><?php esc_html_e("No Of Pending Comments"); ?></a></td>
                                                <td><a href="<?php echo $moderated_url; ?>"><?php echo $pending_comments; ?></a></td>
                                            </tr>
                                            <tr>
                                                <td>7</td>
                                                <td><a href="<?php echo $spam_url; ?>"><?php esc_html_e("No Of Spam Comments"); ?></a></td>
                                                <td><a href="<?php echo $spam_url; ?>"><?php echo $spam_comments; ?></a></td>
                                            </tr>
                                            <tr>
                                                <td>8</td>
                                                <td><a href="<?php echo $all_url; ?>"><?php esc_html_e("No Of Total Comments"); ?></a></td>
                                                <td><a href="<?php echo $all_url; ?>"><?php echo $total_comments; ?></a></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>  
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <br>
    <div class="scspam-row">
        <div class="scspam-col-xs-12">
            <div class="scspam-dashboard-item active">
                <div class="scspam-dashboard-item-inner">
                    <div class="scspam-dashboard-item-content">
                        <div class="scspam-dashboard-item-title">
                            <strong style="font-size: 20px;letter-spacing: 2px;">
                                <?php esc_html_e('Top IPs Blocked', 'spamclean'); ?>
                            </strong>
                        </div>
                    </div>
                </div>
                <div class="scspam-dashboard-item-extra">
                    <ul class="scspam-dashboard-item-list">
                        <li>
                            <div>
                                <div class="scspam-ips scspam-ips-7d scspam-hidden">
                                    <table class="scspam-table scspam-table-hover">
                                        <thead>
                                            <tr>
                                                <th><?php esc_html_e("Author"); ?></th>
                                                <th><?php esc_html_e("E-mail"); ?></th> 
                                                <th><?php esc_html_e("Url"); ?></th>
                                                <th><?php esc_html_e("Ip"); ?></th>   
                                                <th><?php esc_html_e("Country"); ?></th>   
                                                <th><?php esc_html_e("Flag"); ?></th> 
                                                <th><?php esc_html_e("Comment Date"); ?></th>  
                                            </tr>
                                        </thead>
                                        <?php
                                        foreach ($itsresult as $itsspam) {
                                            $ip = $itsspam->comment_author_IP;
                                            if (function_exists('getCountryFromIP')) {
                                                $ip_iso = strtolower(getCountryFromIP($ip, "code"));
                                                $ip_name = getCountryFromIP($ip, "Name");
                                            }
                                            ?>
                                            <tbody>
                                                <tr>
                                                    <td><?php echo $itsspam->comment_author; ?></td>
                                                    <td><?php echo $itsspam->comment_author_email; ?></td>
                                                    <td><a href="<?php echo $itsspam->comment_author_url; ?>">
                                                            <?php echo $itsspam->comment_author_url; ?></a></td>
                                                    <td><?php echo $itsspam->comment_author_IP; ?></td>
                                                    <td><?php echo $ip_name ?></td>
                                                    <td><spam class="spamcleanflag spamcleanflag-<?php echo $ip_iso; ?>"></spam></td>
                                            <td><?php echo $itsspam->comment_date; ?></td>                                           
                                            </tr>                                                                                                                                                       
                                            </tbody>
                                        <?php } ?>
                                    </table>
                                </div>  
                            </div>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</ul>