<?php


namespace App\Twig;


use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class AppExtension extends AbstractExtension
{
    public function getFilters()
    {
        return [
            new TwigFilter('format_date', [$this, 'formatDate']),
        ];
    }

    public function formatDate(\DateTime $date)
    {
        $formated_date = date_format($date, 'd/m/Y');
        $formated_hour = date_format($date, 'H:i');

        return 'le ' . $formated_date . ' à ' . $formated_hour . '.';
    }
}