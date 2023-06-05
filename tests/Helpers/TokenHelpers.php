<?php

namespace Tests\Helpers;

trait TokenHelpers
{

    public function get_token()
    {
        return json_decode($this->post('/api/token')->content(), true)['token'];
    }

    public function authorization_header()
    {
        return [
            'Authorization' => 'Bearer ' . $this->get_token()
        ];
    }

}
