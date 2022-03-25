<?php

namespace App\Components;

use Phalcon\Escaper;

class Myescaper
{
    public function sanitize($variable)
    {
        $escaper = new Escaper();
        return $escaper->escapeHtml($escaper->escapeJs($escaper->escapeCss($variable)));
    }
}
