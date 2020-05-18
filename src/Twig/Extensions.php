<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source slug.
 */

namespace Survos\BaseBundle\Twig;

use Symfony\Component\Intl\Countries;
use Symfony\Component\Intl\Currencies;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Multiple Twig extensions: filters and functions
 */
class Extensions extends AbstractExtension
{
    /**
     * @var string[]
     */
    private $locales;

    /**
     * @var Duration
     */
    protected $durationFormatter;

    /**
     * Extensions constructor.
     * @param string $locales
     */
    public function __construct($locales='')
    {
        $this->locales = explode('|', $locales);
        $this->durationFormatter = null; // new Duration();
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {

        return [
            new TwigFilter('externalLink', [$this, 'formatExternalLink']),
            new TwigFilter('duration', [$this, 'duration']),
            new TwigFilter('money', [$this, 'money']),
            new TwigFilter('currency', [$this, 'currency']),
            new TwigFilter('country', [$this, 'country']),
            new TwigFilter('clean', function($s) { return $s . "!!"; }),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {

        return [
            new TwigFunction('locales', [$this, 'getLocales']),
            new TwigFunction('h', [$this, 'rstHeader']),
            new TwigFunction('optionsResolver', [$this, 'optionsResolver']),
            new TwigFunction('tourOptions', [$this, 'tourOptions']),
            new TwigFunction('adminLinks', [$this, 'adminLinks']),
        ];
    }

    public function rstHeader($level, $text) {
        $levels = [null, '-', '^', '='];
        return sprintf("%s\n%s\n\n", $text, str_repeat($levels[$level], mb_strlen($text)));
    }
    /**
     * Transforms seconds into a duration string.
     *
     * @param $seconds
     * @param bool $includeSeconds
     * @return string
     */
    public function duration($seconds, $includeSeconds = false)
    {
        return $this->durationFormatter->format($seconds, $includeSeconds) . ' h';
    }

    /**
     * Transforms seconds into a duration string.
     *
     * @param $seconds
     * @param bool $includeSeconds
     * @return string
     */
    public function formatExternalLink($url, $class='')
    {
        return sprintf("<a target='_blank' href='%s'>%s <i class='fas fa-external-link'></i> </a>", $url, $url);
    }



    /**
     * @param string $currency
     * @return string
     */
    public function currency($currency): string
    {
        return Currencies::getSymbol($currency);
    }

    /**
     * @param string $country
     * @return string
     */
    public function country($country): string
    {
        return Countries::getName($country);
    }

    /**
     * @param float $amount
     * @param string $currency
     * @return string
     */
    public function money($amount, $currency = null)
    {
        $result = number_format(round($amount, 2), 2);
        if ($currency !== null) {
            $result .= ' ' . Intl::getCurrencyBundle()->getCurrencySymbol($currency);
        }
        return $result;
    }

    /**
     * Takes the list of codes of the locales (languages) enabled in the
     * application and returns an array with the name of each locale written
     * in its own language (e.g. English, Français, Español, etc.)
     *
     * @return array
     */
    public function getLocales()
    {
        $locales = [];
        foreach ($this->locales as $locale) {
            $locales[] = ['slug' => $locale, 'name' => Intl::getLocaleBundle()->getLocaleName($locale, $locale)];
        }

        return $locales;
    }

    public function optionsResolver($defaults, $options, $required = [])
    {
        if (empty($options)) {
            $options = [];
        }
        $resolver = new OptionsResolver();
        return $resolver->setDefaults($defaults)
            ->setRequired($required)
            ->resolve($options);
    }

    public function adminLinks($object, $root = null)
    {
        if (!$root) {
            $root = u( (new \ReflectionClass($object))->getShortName())->lower();
        }



    }

    public function tourOptions($id, $options, $required = [])
    {
        // $id = Utility::displayToCode($id);
        $resolver = new OptionsResolver();
        return $resolver->setDefaults([
            'tour' => $id,
            'content' => sprintf("tour.%s.content", $id),
            'title' => sprintf('tour.%s.title', $id),
            'placement' => 'bottom',
            'id' => $id
        ])
            ->setRequired(['tour'])
            ->resolve($options);
    }


}
