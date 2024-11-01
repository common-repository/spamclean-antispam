<ul style="padding: 10px;">
    <li class="spamclean_left">
        <input type="checkbox" name="ias_flag_spam" id="ias_flag_spam" value="1" <?php checked($options['flag_spam'], 1) ?> />
        <label for="ias_flag_spam">
            <?php esc_html_e('Do not empty spam folder', 'spamclean'); ?>
            <span><?php esc_html_e('I will manually empty my spam folder.', 'spamclean'); ?></span>
        </label>
    </li>

    <li class="ias_flag_spam_child spamclean_right">
        <input type="checkbox" name="ias_email_notify" id="ias_email_notify" value="1" <?php checked($options['email_notify'], 1) ?> />
        <label for="ias_email_notify">
            <?php esc_html_e('E-mail Notification', 'spamclean'); ?>
            <span><?php esc_html_e('Send E-mail notification for incoming spam to admins', 'spamclean'); ?></span>
        </label>
    </li>

    <li class="ias_flag_spam_child spamclean_left">
        <input type="checkbox" name="ias_no_notice" id="ias_no_notice" value="1" <?php checked($options['no_notice'], 1) ?> />
        <label for="ias_no_notice">
            <?php esc_html_e('Do not save the spam reason', 'spamclean'); ?>
            <span><?php esc_html_e('We trust your decison. Do not save the spam reason column.', 'spamclean'); ?></span>
        </label>
    </li>

    <li class="ias_flag_spam_child spamclean_right">
        <input type="checkbox" name="ias_cronjob_enable" id="ias_cronjob_enable" value="1" <?php checked($options['cronjob_enable'], 1) ?> />
        <label for="ias_cronjob_enable">
            <?php
            echo sprintf(
                    esc_html__('Empty spam folder after %s days', 'spamclean'), '<input type="number" min="0" name="ias_cronjob_interval" value="' . esc_attr($options['cronjob_interval']) . '" class="ias-mini-field" />'
            )
            ?>
            <span><?php esc_html_e('Clear my spam folder', 'spamclean') ?></span>
        </label>
    </li>


    <li class="spamclean_left">
        <input type="checkbox" name="ias_ignore_pings" id="ias_ignore_pings" value="1" <?php checked($options['ignore_pings'], 1) ?> />
        <label for="ias_ignore_pings">
            <?php esc_html_e('Do not check trackbacks / pingbacks', 'spamclean'); ?>
            <span><?php esc_html_e('No spam check for link notifications', 'spamclean'); ?></span>
        </label>
    </li>
    <li class="ias_flag_spam_child" style="float: right;background: #f2f2f2;box-shadow: 1px 1px 1px 0px #d9d9d9;">
        <input type="checkbox" name="ias_reasons_enable" id="ias_reasons_enable" value="1" <?php checked($options['reasons_enable'], 1) ?> />
        <label for="ias_reasons_enable">
            <?php esc_html_e('Delete comments by spam reasons', 'spamclean'); ?>
            <span><?php esc_html_e('For multiple selections press Ctrl/CMD', 'spamclean'); ?></span>
        </label>
        <ul>
            <li>
                <select name="ias_ignore_reasons[]" id="ias_ignore_reasons" size="2" multiple>
                    <?php foreach (self::$spamclean_defaults['reasons'] as $k => $v) { ?>
                        <option <?php selected(in_array($k, $options['ignore_reasons']), true); ?> value="<?php echo $k ?>"><?php esc_html_e($v, 'spamclean') ?></option>
                    <?php } ?>
                </select>
                <label for="ias_ignore_reasons">
                    <?php esc_html_e('', 'spamclean'); ?>
                </label>
            </li>
        </ul>
    </li>
</ul>