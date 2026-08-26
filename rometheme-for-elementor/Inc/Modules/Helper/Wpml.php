<?php

namespace RTMKit\Modules\Helper;

if (!defined('ABSPATH')) exit;

/**
 * Language mapping for IDs stored in widget settings.
 *
 * Post and term IDs are picked in the editor and saved raw, so on a translated
 * page they still address the source-language objects: a curated post list
 * comes back empty, a category filter matches nothing.
 *
 * Every method is a no-op when WPML is absent -- nothing filters
 * `wpml_object_id` then, so the original value is returned.
 */
class Wpml
{
    /**
     * Map a single object ID to its counterpart in the active language.
     *
     * @param int|string $id
     * @param string     $type Post type or taxonomy name.
     * @return int
     */
    public static function object_id($id, $type)
    {
        $id = absint($id);

        if ($id === 0 || $type === '') {
            return $id;
        }

        $mapped = apply_filters('wpml_object_id', $id, $type, true);

        return $mapped ? absint($mapped) : $id;
    }

    /**
     * Map a list of object IDs, dropping anything that resolves to nothing.
     *
     * @param mixed  $ids  Array of IDs, or a single ID.
     * @param string $type Post type or taxonomy name.
     * @return int[]
     */
    public static function object_ids($ids, $type)
    {
        if (empty($ids)) {
            return [];
        }

        $mapped = [];

        foreach ((array) $ids as $id) {
            $translated = self::object_id($id, $type);
            if ($translated > 0) {
                $mapped[] = $translated;
            }
        }

        return array_values(array_unique($mapped));
    }

    /**
     * Resolve term slugs to term IDs in the active language.
     *
     * Querying a taxonomy by slug breaks under WPML because translated terms
     * carry their own slugs; resolving to an ID and mapping it does not.
     *
     * @param mixed  $slugs
     * @param string $taxonomy
     * @return int[]
     */
    public static function term_ids_from_slugs($slugs, $taxonomy)
    {
        if (empty($slugs) || $taxonomy === '') {
            return [];
        }

        $ids = [];

        foreach ((array) $slugs as $slug) {
            $term = get_term_by('slug', $slug, $taxonomy);

            if (!$term || is_wp_error($term)) {
                continue;
            }

            $ids[] = self::object_id($term->term_id, $taxonomy);
        }

        return array_values(array_unique(array_filter($ids)));
    }
}
