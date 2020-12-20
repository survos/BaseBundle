<?= $helper->getHeadPrintCode($entity_class_name.' index'); ?>

<?php $entity_rp = $entity_twig_var_singular . '.rp'; ?>

{% block body %}
    <h1><?= $entity_class_name ?> index</h1>

    {% include "_table.html.twig" %}


    <a href="{{ path('<?= $route_name ?>_new') }}">Create new</a>
{% endblock %}
