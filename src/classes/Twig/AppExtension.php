<?php


namespace App\Twig;


use DateTime;
use InvalidArgumentException;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use function preg_match;
use function round;
use function time;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('ago', [$this, 'ago']),
        ];

    }

    public function ago($string) {
        $time1 = $string;
        if ($time1 instanceof DateTime) {
            $time1 = $time1->getTimestamp();
        } else {
            if (preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/", $time1)) {
                $time1 = DateTime::createFromFormat("Y-m-d", $time1)->getTimestamp();
            } elseif (preg_match("/^[0-9]{4}-[0-9]{2}-[0-9]{2} [0-9]{2}:[0-9]{2}:[0-9]{2}$/", $time1)) {
                $time1 = DateTime::createFromFormat("Y-m-d H:i:s", $time1)->getTimestamp();
            } elseif (preg_match("/^[0-9]+$/", $time1)) {
                $time1 = DateTime::createFromFormat("U", $time1)->getTimestamp();
            } else {
                throw new InvalidArgumentException("$time1 is not a valid time string!");
            }
        }

        $periods = array("Sekunde", "Minute", "Stunde", "Tag", "Woche", "Monat", "Jahr");
        $lengths = array("60", "60", "24", "7", "4.35", "12", "10");

        $now = time();

        $difference = $now - $time1;

        for ($j = 0; $difference >= $lengths[$j] && $j < count($lengths) - 1; $j++) {
            $difference /= $lengths[$j];
        }

        $difference = round($difference);

        if ($difference != 1) {
            switch ($j) {
                case 0:
                case 1:
                case 2:
                case 4:
                    $periods[$j] .= "n";
                    break;
                case 3:
                case 5:
                case 6:
                    $periods[$j] .= "en";
                    break;
            }
        }

        return "$difference $periods[$j]";
    }
}
