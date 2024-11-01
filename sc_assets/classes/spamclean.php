<ul style="padding: 10px;">   
    <li class="spamclean_left">
        <input type="checkbox" name="ias_already_commented" id="ias_already_commented" value="1" <?php checked($options['already_commented'], 1) ?> />
        <label for="ias_already_commented">
            <?php esc_html_e('Trust approved commenters', 'spamclean'); ?>
            <span><?php esc_html_e('Commenters Once approved , will never endup in your spam folder', 'spamclean'); ?></span>
        </label>
    </li>
    <li class="spamclean_right">
        <input type="checkbox" name="ias_gravatar_check" id="ias_gravatar_check" value="1" <?php checked($options['gravatar_check'], 1) ?> />
        <label for="ias_gravatar_check">
            <?php esc_html_e('Trust commenters with a Gravatar', 'spamclean'); ?>
            <span><?php
                printf(
                        esc_html__('If Commenter has a Gravatar , its not a spam.', 'spamclean')
                );
                ?></span>
        </label>
    </li>
    <li class="spamclean_left">
        <input type="checkbox" name="ias_bbcode_check" id="ias_bbcode_check" value="1" <?php checked($options['bbcode_check'], 1) ?> />
        <label for="ias_bbcode_check">
            <?php esc_html_e('BBCode is spam', 'spamclean'); ?>
            <span><?php esc_html_e('Comments with BBcode should be wanted spam.', 'spamclean'); ?></span>
        </label>
    </li>

    <li class="spamclean_right">
        <input type="checkbox" name="ias_advanced_check" id="ias_advanced_check" value="1" <?php checked($options['advanced_check'], 1) ?> />
        <label for="ias_advanced_check">
            <?php esc_html_e('Validate the ip address of commenters', 'spamclean'); ?>
            <span><?php esc_html_e('Comments from block ip address will be marked spam', 'spamclean'); ?></span>
        </label>
    </li>
    <li class="spamclean_left">
        <input type="checkbox" name="ias_regexp_check" id="ias_regexp_check" value="1" <?php checked($options['regexp_check'], 1) ?> />
        <label for="ias_regexp_check">
            <?php esc_html_e('Use regular expressions', 'spamclean'); ?>
            <span><?php esc_html_e('Predefined/custom spam patterns should be received for Spam', 'spamclean'); ?></span>
        </label>
    </li>
    <li class="spamclean_right">
        <input type="checkbox" name="ias_spam_ip" id="ias_spam_ip" value="1" <?php checked($options['spam_ip'], 1) ?> />
        <label for="ias_spam_ip">
            <?php esc_html_e('Look in the local spam database', 'spamclean'); ?>
            <span><?php esc_html_e('Check commenters against the spam data on your own blog', 'spamclean'); ?></span>
        </label>
    </li>
    <li class="spamclean_left">
        <input type="checkbox" name="ias_dnsbl_check" id="ias_dnsbl_check" value="1" <?php checked($options['dnsbl_check'], 1) ?> />
        <label for="ias_dnsbl_check">
            <?php esc_html_e('Use a public spam database', 'spamclean'); ?>
            <span><?php
                $link2 = sprintf(
                        '<a href="%s" target="_blank" rel="noopener noreferrer">', esc_url(__('', 'spamclean'), 'https')
                );
                printf(
                        esc_html__('Check Comments details with external database  %sSpam Databse Look up%2$s.', 'spamclean'), '<a href="http://www.dnsbl.info/" target="_blank" rel="noopener noreferrer">', '</a>', $link2, '</a>'
                );
                ?></span>
        </label>
    </li>
    <li style="float: right;background: #f2f2f2;box-shadow: 1px 1px 1px 0px #d9d9d9;">
        <input type="checkbox" name="ias_translate_api" id="ias_translate_api" value="1" <?php checked($options['translate_api'], 1) ?> />
        <label for="ias_translate_api">
            <?php esc_html_e('Allow comments only in certain language', 'spam_clean') ?>
            <span><?php
                printf(
                        esc_html__('Detect and approve only the specified language.', 'spamclean'));
                ?></span>
        </label>
        <ul>
            <li>
                <select name="ias_translate_lang">
                    <?php foreach (array('de' => 'German', 'en' => 'English', 'fr' => 'French', 'it' => 'Italian', 'es' => 'Spanish') as $k => $v) { ?>
                        <option <?php selected($options['translate_lang'], $k); ?> value="<?php echo esc_attr($k) ?>"><?php esc_html_e($v, 'spamclean') ?></option>
                    <?php } ?>
                </select>
                <label for="ias_translate_lang">
                    <?php esc_html_e($options['translate_lang'], 'spamclean') ?>
                </label>
            </li>
        </ul>
    </li>
</ul>