<?php

class Peda_Access
{
    private $controller;

    public function __construct(&$controller)
    {
        $this->controller = &$controller;
    }

    public function Access($call, $payload, &$params)
    {
        $granted = [
        ];
        
        $limited = [
  
        ];

        if (in_array($call, $granted))
        {
            return (true);
        }

        if (in_array($call, $limited))
        {
            return ($this->$call($payload, $params));
        }

        return (false);
    }

    // GET



    // POST



    // PUT


    // DELETE
}

?>