<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $entity_full_class_name ?>;
<?php if (isset($repository_full_class_name)): ?>
    use <?= $repository_full_class_name ?>;
<?php endif ?>

use Doctrine\Common\Persistence\ManagerRegistry;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class <?= $shortClassName ?> implements ParamConverterInterface
{

    private $registry;

    /**
     * @param ManagerRegistry $registry Manager registry
     */
    public function __construct(ManagerRegistry $registry = null)
    {
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     *
     * Check, if object supported by our converter
     */
    public function supports(ParamConverter $configuration)
    {
        return <?= $entity_class_name ?>::class == $configuration->getClass();
    }

    /**
     * {@inheritdoc}
     *
     * Applies converting
     *
     * @throws \InvalidArgumentException When route attributes are missing
     * @throws NotFoundHttpException     When object not found
     * @throws Exception
     */
    public function apply(Request $request, ParamConverter $configuration)
    {
        $params = $request->attributes->get('_route_params');

//        if (isset($params['<?= $entity_unique_name ?>']) && ($<?= $entity_unique_name ?> = $request->attributes->get('<?= $entity_unique_name ?>')))

        $<?= $entity_unique_name ?> = $request->attributes->get('<?= $entity_unique_name ?>');
        if ($<?= $entity_unique_name ?> === 'undefined') {
            throw new Exception("Invalid <?= $entity_unique_name ?> " . $<?= $entity_unique_name ?>);
        }

        // Check, if route attributes exists
        if (null === $<?= $entity_unique_name ?> ) {
            if (!isset($params['<?= $entity_unique_name ?>'])) {
                return; // no <?= $entity_unique_name ?> in the route, so leave.  Could throw an exception.
            }
        }

        // Get actual entity manager for class.  We can also pass it in, but that won't work for the doctrine tree extension.
        $em = $this->registry->getManagerForClass($configuration->getClass());
        $repository = $em->getRepository($configuration->getClass());

        // Try to find <?= $entity_var_name ?> by its Id
        $<?= $entity_var_name ?> = $repository->findOneBy(['id' => $<?= $entity_unique_name ?>]);

        if (null === $<?= $entity_var_name ?> || !($<?= $entity_var_name ?> instanceof <?= $entity_class_name ?>)) {
            throw new NotFoundHttpException(sprintf('%s %s object not found.', $<?= $entity_unique_name ?>, $configuration->getClass()));
        }

        // Map found <?= $entity_var_name ?> to the route's parameter
        $request->attributes->set($configuration->getName(), $<?= $entity_var_name ?>);
    }

}
