<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Unified_phone_api extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('unified_phone/unified_phone');
        if (!unified_phone_can('view')) {
            access_denied(UNIFIED_PHONE_MODULE_NAME);
        }
    }

    public function normalize()
    {
        $this->load->library('unified_phone/Phone_normalizer');
        $phone = $this->input->get('phone', true);
        $this->output
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode([
                'raw'        => $phone,
                'normalized' => $this->phone_normalizer->normalize_bd_phone($phone),
                'display'    => $this->phone_normalizer->display_bd_phone($phone),
                'keys'       => $this->phone_normalizer->search_keys($phone),
            ]));
    }
}
