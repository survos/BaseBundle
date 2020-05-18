<?= $helper->getHeadPrintCode($entity_class_name.' index'); ?>

<?php $entity_rp = $entity_twig_var_singular . '.rp'; ?>

{% block body %}
    <h1><?= $entity_class_name ?> index</h1>

    <table class="table">
        <thead>
            <tr>
                <th>actions</th>
<?php foreach ($entity_fields as $field): ?>
                <th><?= ucfirst($field['fieldName']) ?></th>
<?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
        {% for <?= $entity_twig_var_singular ?> in <?= $entity_twig_var_plural ?> %}
            <tr>
                <td>
                    <a href="{{ path('<?= $route_name ?>_show', <?= $entity_rp ?>) }}">
                        <span class="fas fa-file-invoice"></span></a>

                    </a>
                    <a href="{{ path('<?= $route_name ?>_edit', <?= $entity_rp ?>) }}">
                        <span class="fas fa-edit"></span></a>
                </td>

                <?php foreach ($entity_fields as $field): ?>
                <td>{{ <?= $helper->getEntityFieldPrintCode($entity_twig_var_singular, $field) ?> }}</td>
<?php endforeach; ?>
            </tr>
        {% else %}
            <tr>
                <td colspan="<?= (count($entity_fields) + 1) ?>">no records found</td>
            </tr>
        {% endfor %}
        </tbody>
    </table>

    <a href="{{ path('<?= $route_name ?>_new') }}">Create new</a>
{% endblock %}
