<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source slug.
 */

namespace Survos\BaseBundle\Twig;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use function Symfony\Component\String\u;

/**
 * Multiple Twig extensions: filters and functions
 */
class Extensions extends AbstractExtension
{
    /**
     * {@inheritdoc}
     */
    public function getFilters(): array
    {

        return [
            new TwigFilter('indent', [$this, 'indent']),
            new TwigFilter('externalLink', [$this, 'formatExternalLink']),
//            new TwigFilter('duration', [$this, 'duration']),
//            new TwigFilter('money', [$this, 'money']),
//            new TwigFilter('currency', [$this, 'currency']),
            new TwigFilter('country', [$this, 'country']),
            new TwigFilter('route_alias', fn($str) => $str ), // so templates from adminlte bundle run
            new TwigFilter('clean', function($s) { return $s . "!!"; }),
        ];
    }

    public function indent($value): string
    {
        return class_exists('Gajus\Dindent\Indenter')
        // if there's a dump it's too big to indent
            ? strstr('sf-dump', $value) ? $value : (new \Gajus\Dindent\Indenter())->indent($value)
            : "<!-- composer req gajus/dindent to install indenter -->\n" . $value;
    }


        /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {

        return [
            new TwigFunction('get_class', fn($entity) => $entity::class),
            new TwigFunction('locales', [$this, 'getLocales']),
            new TwigFunction('breadcrumb', [$this, 'renderBreadcrumb'], ['is_safe' => ['html_attr']]),
            new TwigFunction('h', [$this, 'rstHeader']),
            new TwigFunction('optionsResolver', [$this, 'optionsResolver']),
            new TwigFunction('tourOptions', [$this, 'tourOptions']),
            new TwigFunction('adminLinks', [$this, 'adminLinks']),
            new TwigFunction('sortable_fields', [$this, 'sortableFields']),
        ];
    }

    public function sortableFields(string $class): array
    {
        $reflector = new \ReflectionClass($class);
        foreach ($reflector->getAttributes() as $attribute) {
            if (!u($attribute->getName())->endsWith('ApiFilter')) {
                continue;
            }
            $filter = $attribute->getArguments()[0];
            if (u($filter)->endsWith('OrderFilter')) {
                return $attribute->getArguments()['properties'];
            }
        }
        return [];
    }

//
//
//        $sortFields = [];
//        foreach ($classInfo->getAttributes() as $attribute) {
////            print_r($attribute->newInstance()->testArgument);
//            switch ($attribute->getName()) {
//                case OrderFilter::class:
//
//                    dd($attribute->getName(), $attribute->getArguments());
//                    break;
//                default:
//
//
//    }
//
    public function renderBreadcrumb(string $text, string $url = null)
    {

//        <li class="breadcrumb-item"><a href="#">Home</a></li>
//    <li class="breadcrumb-item active" aria-current="page">Library</li>
        return $url ? sprintf('<li class="breadcrumb-item"><a href="%s">%s</a></li>', $url, $text) : $text;
    }

    public function rstHeader($level, $text): string
    {
        $levels = [null, '-', '^', '='];
        return sprintf("%s\n%s\n\n", $text, str_repeat($levels[$level], mb_strlen($text)));
    }

    /**
     * add icon and sets target
     *
     */
    public function formatExternalLink(string $url, $class=''): string
    {
        return sprintf("<a target='_blank' href='%s'>%s <i class='fas fa-external-link'></i> </a>", $url, $url);
    }


    public function optionsResolver($defaults, array $options=[], $required = [])
    {
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
