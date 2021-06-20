<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class UuidExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('uuid', [$this, 'generateUuid']),
        ];
    }

    public function generateUuid($length = 12)
    {
        $uuid = '';
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789#_-';
        $charactersLength = strlen($characters);

        for ($i = 0; $i < $length; ++$i) {
            $uuid .= substr($characters, rand(0, $charactersLength), 1);
        }

        return $uuid;
    }
}
