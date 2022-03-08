<?php

namespace Survos\BaseBundle\Entity;

use Symfony\Component\PropertyAccess\Exception\InvalidArgumentException;
use Symfony\Component\PropertyAccess\Exception\NoSuchPropertyException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use function Symfony\Component\String\u;

abstract class SurvosBaseEntity implements BaseEntityInterface
{
    function getId() {}

    // hack for https://github.com/symfony/symfony/issues/35574
//    public function __sleep() { return []; }

    public function getRP(?array $addlParams=[]): array
    {
        return array_merge($this->getUniqueIdentifiers(), $addlParams);
    }

    public function __toString()
    {
        return join('-', array_values($this->getUniqueIdentifiers()));
    }


    public function populateFromOptions(array $options ): self
    {
        $propertyAccessor = PropertyAccess::createPropertyAccessor();
//        unset($options['_token']);
//        unset($options['_next_route']);
        foreach ($options as $var=>$val) {

            // isn't there a property accessor method?
            try {
                $propertyAccessor->setValue($this, $var, $val);
            } catch (NoSuchPropertyException $exception) {
                //
            } catch (InvalidArgumentException $exception) {
                // it might be a date string
                try {
                    $date = new \DateTimeImmutable($val);
                    $propertyAccessor->setValue($this, $var, $date);

                } catch (\Exception $e) {
                    dump($var, $val, $e); assert(false);
                }

            }

//            if (method_exists($this, $setter = 'set' . $var)) {
//                try {
//                    $this->{$setter}($val);
//                } catch (\Exception $exception) {
//                    dd($setter, $val);
//                }
//            } elseif (property_exists($this, $var)) {
//                $this->{$var} = $val;
////                dump($var, $val, $options, $this);
//            } else {
//                //not mapped.  @todo: check for source_date v sourceDate (and convert dates!)
////                dd($var, $val, $options, $this, __METHOD__);
//            }
        }
//        dd($options, $this, __METHOD__);
        return $this;
    }

    public function getRoutePrefix()
    {
        // this or self?
        $shortName = strtolower( (new \ReflectionClass($this))->getShortName() );
        return $shortName;
    }

    static public function getPrefix(string $class = null)
    {
        if (!$class) {
            $class = get_called_class();
        }
        $shortName = strtolower(u( $x = (new \ReflectionClass($class))->getShortName() )->snake()->lower()->ascii());

//        $shortName = strtolower( (new \ReflectionClass(get_called_class()))->getShortName() );
        return $shortName;
    }

    public function getNextRouteChoices(): array
    {
        $routes = [];
        foreach (['edit', 'show', 'index'] as $routeType ) {
            // @todo: security
            $routes[$this->getRoutePrefix() . '_' .  $routeType] = $this->getRoutePrefix() . $routeType; // @todo add translation
        }
        return $routes;
        // by default, list and show
    }

    // default, so this works with the default for ParamConverter
    function getUniqueIdentifiers(): array
    {
        return [$this->getRoutePrefix() . 'Id' => $this->getId()];
    }

}
