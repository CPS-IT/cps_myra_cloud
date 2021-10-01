<?php

namespace CPSIT\CpsMyraCloud\Traits;

trait DomainListParserTrait
{
    /**
     * @param string $list
     * @return array
     */
    protected function parseCommaList(string $list): array
    {
        $rawList = explode(',', str_replace(' ', '', $list));
        return array_unique(array_filter($rawList?:[]));
    }
}