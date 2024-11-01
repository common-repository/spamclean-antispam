<ul style="padding: 10px;">
    <li style="margin-bottom: 25px;float: left;background: #f2f2f2;box-shadow: 1px 1px 1px 0px #d9d9d9;">
        <input type="checkbox" name="ias_country_code" id="ias_country_code" value="1" <?php checked($options['country_code'], 1) ?> />
        <label for="ias_country_code">
            <?php esc_html_e('Block comments from specific countries', 'spamclean') ?>
            <span>
                <?php
                printf(
                        esc_html__('Filtering the requests depending on country.', 'spamclean')
                );
                ?>
            </span>
        </label>
        <ul>
            <?php
            $its_iso_code = sprintf(
                    '<a href="%s" target="_blank" rel="noopener noreferrer">', esc_url(__('http://www.nationsonline.org/oneworld/country_code_list.htm', 'spamclean'), 'https')
            );
            ?>
            <li>
                <textarea name="ias_country_black" id="ias_country_black" class="ias-medium-field code" placeholder="<?php esc_attr_e('e.g. BF, SG, YE', 'spamclean'); ?>"><?php echo esc_attr($options['country_black']); ?></textarea>
                <label for="ias_country_black">
                    <span><?php
                        printf(
                                esc_html__('Countries you want to block from comments.  %1$sISO Codes%2$s', 'spamclean'), $its_iso_code, '</a>');
                        ?></span>
                </label>
            </li>
            <li>
                <textarea name="ias_country_white" id="ias_country_white" class="ias-medium-field code" placeholder="<?php esc_attr_e('e.g. BF, SG, YE', 'spamclean'); ?>"><?php echo esc_attr($options['country_white']); ?></textarea>
                <label for="ias_country_white">
                    <span><?php
                        printf(
                                esc_html__('Only country you want to allow for comments.  %1$sISO Codes%2$s', 'spamclean'), $its_iso_code, '</a>');
                        ?></span>
                </label>
            </li>
        </ul>
    </li>
</ul>