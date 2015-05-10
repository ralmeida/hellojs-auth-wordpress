<?php

class HelloJSAuthView
{
    public function __construct( $type, $data )
    {
        header( "HTTP/1.0 200" );
        header( "Content-Type: application/json;charset=utf-8" );
        $method = "render";
        $this->$method( $data );
    }

    protected function render( $data )
    {
        $success = (isset($data['success'])) ? $data['success'] : false;
        echo json_encode($data);
    }
}

?>