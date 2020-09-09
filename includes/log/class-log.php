<?php

class WPUF_Log {

    /**
     * wpdb query logger
     *
     * @var \WPUF_Log_WPDB_Query
     */
    public $wpdb_query;

    /**
     * Class constructor
     *
     * @since WPUF_SINCE
     */
    public function __construct() {
        $this->wpdb_query = new WPUF_Log_WPDB_Query();
    }
}
