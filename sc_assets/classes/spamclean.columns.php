<?php

if (!defined('ABSPATH'))
    exit;

final class Spam_clean_Columns {

    public static function register_plugin_columns($columns) {
        return array_merge(
                $columns, array(
            'spam_clean_reason' => esc_html__('Spam Reason', 'spamclean')
                )
        );
    }

    public static function print_plugin_column($column, $comment_id) {

        if ($column !== 'spam_clean_reason') {
            return;
        }


        $spam_reason = get_comment_meta($comment_id, $column, true);
        $spam_reasons = Spam_clean::$spamclean_defaults['reasons'];


        if (empty($spam_reason) OR empty($spam_reasons[$spam_reason])) {
            return;
        }


        echo esc_html($spam_reasons[$spam_reason]);
    }

    public static function register_sortable_columns($columns) {
        $columns['spam_clean_reason'] = 'spam_clean_reason';

        return $columns;
    }

    public static function set_orderby_query($query) {

        $orderby = $query->get('orderby');

        if (empty($orderby) OR $orderby !== 'spam_clean_reason') {
            return;
        }


        $query->set('meta_key', 'spam_clean_reason');
        $query->set('orderby', 'meta_value');
    }

    public static function print_column_styles() {
        ?>
        <style>
            .column-spam_clean_reason {
                width: 10%;
            }
        </style>
        <?php

    }

}
