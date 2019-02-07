<?php

namespace Xima\DepmonBundle\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class HighlightExtension extends AbstractExtension
{

    public function getFilters()
    {
        return [
            new TwigFilter('highlight', [$this, 'highlightSearchWord']),
        ];
    }

    /**
     * @param string $string
     * @param string $searchWord
     * @return string
     */
    public function highlightSearchWord(string $string, string $searchWord = '')
    {
        if ($searchWord != null) {
            if (intval(mb_strpos($string, $searchWord)) !== false) {
                $strings = [];
                array_push($strings, substr($string, 0, intval(mb_strpos($string, $searchWord))));
                array_push($strings, '<mark>' . $searchWord . '</mark>');
                array_push($strings, substr($string, intval(intval(mb_strpos($string, $searchWord))) + intval(mb_strlen($searchWord))));

                $string = implode('', $strings);
            }
        }

        return $string;
    }

    public function getName()
    {
        return 'highlight';
    }
}