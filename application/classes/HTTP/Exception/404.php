<?php defined('SYSPATH') OR die('No direct script access.');

class HTTP_Exception_404 extends Kohana_HTTP_Exception_404 {

    public function get_response()
    {
        $uri = $this->request()->uri();

        if (strpos($uri, 'survey/edit') === 0)
        {
            $response = Response::factory();
            $response->status(404);
            $response->body(View::factory('survey/admin_not_found'));

            return $response;
        }

        if (strpos($uri, 'survey/') === 0)
        {
            $response = Response::factory();
            $response->status(404);
            $response->body(View::factory('survey/not_found'));

            return $response;
        }

        return parent::get_response();
    }

}