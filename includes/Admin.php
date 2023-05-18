<?php

namespace Wp\User\Frontend;

class Admin {
    function __construct() {
        wpuf()->add_to_container( 'menu', new Admin\Menu() );
    }
}
