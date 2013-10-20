<?php
namespace Avh\Db;

final class Db
{

    /**
     * Fetch MySQL Field Names
     *
     * @access public
     * @param string $table
     *            table name
     * @return array
     */
    public function getFieldNames($table = '')
    {
        global $wpdb;

        $return = wp_cache_get('field_names_' . $table, 'AvhDb');
        if (false === $return) {
            $sql = $this->getQueryShowColumns($table);

            $_result = $wpdb->get_results($sql, ARRAY_A);

            $return = array();
            foreach ($_result as $row) {
                if (isset($row['Field'])) {
                    $return[] = $row['Field'];
                }
            }
            wp_cache_set('field_names_' . $table, $return, 'AvhDb', 3600);
        }

        return $return;
    }

    /**
     * Determine if a particular field exists
     *
     * @access public
     * @param  string  $field_name
     * @param  string  $table_name
     * @return boolean
     */
    public function checkFieldExists($field_name, $table_name)
    {
        return (in_array($field_name, $this->getFieldNames($table_name)));
    }

    /**
     * Show column query
     *
     * Generates a platform-specific query string so that the column names can be fetched
     *
     * @access public
     * @param string $table
     *            The table name
     * @return string
     */
    private function getQueryShowColumns($table = '')
    {
        global $wpdb;

        return $wpdb->prepare('SHOW COLUMNS FROM ' . $table);
    }
}
