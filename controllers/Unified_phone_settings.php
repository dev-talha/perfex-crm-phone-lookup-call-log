<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Unified_phone_settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('unified_phone/unified_phone');
        if (!unified_phone_can('settings')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
    }

    public function index()
    {
        redirect(admin_url('unified_phone/settings'));
    }

    public function add_outcome()
    {
        redirect(admin_url('unified_phone/add_outcome'));
    }

    public function chatwoot_test()
    {
        redirect(admin_url('unified_phone/chatwoot_test'));
    }
}
