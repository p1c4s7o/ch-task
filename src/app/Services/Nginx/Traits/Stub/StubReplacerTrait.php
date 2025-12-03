<?php

namespace App\Services\Nginx\Traits\Stub;

trait StubReplacerTrait
{
    /**
     * @param  array<string, mixed>  $vars
     */
    public function replaceAll(array $vars, string $content = ''): string
    {
        if (count($vars) < 1 || strlen(trim($content)) < 1) {
            return '';
        }

        foreach ($vars as $item => $v) {
            $content = str_replace("{{{$item}}}", $v, $content);
        }

        return $content;
    }
}
