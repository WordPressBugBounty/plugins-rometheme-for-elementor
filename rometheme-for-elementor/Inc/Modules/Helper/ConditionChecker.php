<?php

namespace RTMKit\Modules\Helper;

class ConditionChecker
{

    protected $conditions;

    public function __construct($json)
    {
        $this->conditions = json_decode($json, true);
    }

    public function should_display()
    {
        if (empty($this->conditions['conditions'])) {
            return false;
        }

        $include = $this->conditions['conditions']['include'] ?? [];
        $exclude = $this->conditions['conditions']['exclude'] ?? [];

        // Jika ada exclude yang match, langsung false
        foreach ($exclude as $rule) {
            if ($this->match_rule($rule)) {
                return false;
            }
        }

        // Kalau ada include yang match, return true
        foreach ($include as $rule) {
            if ($this->match_rule($rule)) {
                return true;
            }
        }

        return false;
    }

    protected function match_rule($rule)
    {
        // Entire site
        if (isset($rule['entire_site']) && $rule['entire_site']) {
            return true;
        }

        // Archive
        if (isset($rule['archive'])) {
            return $this->check_archive($rule['archive']);
        }

        // Singular
        if (isset($rule['singular'])) {
            return $this->check_singular($rule['singular']);
        }

        // WooCommerce
        if (isset($rule['woocommerce'])) {
            return $this->check_woocommerce($rule['woocommerce']);
        }

        return false;
    }

    protected function check_archive($archive)
    {
        if (!is_archive()) return false;

        if (!empty($archive['all'])) return true;
        if (!empty($archive['author']) && is_author()) return true;
        if (!empty($archive['date']) && is_date()) return true;
        if (!empty($archive['search']) && is_search()) return true;
        if (!empty($archive['404']) && is_404()) return true;

        return false;
    }

    protected function check_singular($singular)
    {
        if (!is_singular()) return false;

        if (!empty($singular['all'])) return true;

        // Pages
        if (isset($singular['pages'])) {
            if (!empty($singular['pages']['all']) && is_page()) return true;
            if (!empty($singular['pages']['specific']) && is_page($singular['pages']['specific'])) return true;
        }

        // Posts
        if (isset($singular['posts'])) {
            if (!empty($singular['posts']['all']) && is_single()) return true;
            if (!empty($singular['posts']['specific']) && is_single($singular['posts']['specific'])) return true;
        }

        // Custom Post Types
        if (isset($singular['custom_post_types'])) {
            foreach ($singular['custom_post_types'] as $cpt => $args) {
                if (!empty($args['all']) && is_singular($cpt)) return true;
                if (!empty($args['specific']) && is_singular($cpt) && in_array(get_the_ID(), $args['specific'])) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function check_woocommerce($woo)
    {
        if (!function_exists('is_woocommerce')) {
            return false;
        }

        if (!empty($woo['shop']) && function_exists('is_shop') && is_shop()) return true;
        if (!empty($woo['product_archive']) && is_post_type_archive('product')) return true;
        if (!empty($woo['single_product']) && is_singular('product')) return true;

        if (!empty($woo['product_categories']) && is_product_category($woo['product_categories'])) return true;
        if (!empty($woo['product_tags']) && is_product_tag($woo['product_tags'])) return true;

        return false;
    }
}
