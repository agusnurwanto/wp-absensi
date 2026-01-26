<?php return array(
    'root' => array(
        'name' => 'htmlburger/carbon-fields-plugin',
        'pretty_version' => 'dev-master',
        'version' => 'dev-master',
        'reference' => 'e5f5c2ea2f1f84efa9ab5ac741fc2b3526094141',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => true,
    ),
    'versions' => array(
        'composer/installers' => array(
            'pretty_version' => 'v1.9.0',
            'version' => '1.9.0.0',
            'reference' => 'b93bcf0fa1fccb0b7d176b0967d969691cd74cca',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'htmlburger/carbon-fields' => array(
            'pretty_version' => 'v3.6.9',
            'version' => '3.6.9.0',
            'reference' => 'f82e80e3e3469d6e86cc17a8950b918ad448a059',
            'type' => 'library',
            'install_path' => __DIR__ . '/../htmlburger/carbon-fields',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'htmlburger/carbon-fields-plugin' => array(
            'pretty_version' => 'dev-master',
            'version' => 'dev-master',
            'reference' => 'e5f5c2ea2f1f84efa9ab5ac741fc2b3526094141',
            'type' => 'wordpress-plugin',
            'install_path' => __DIR__ . '/../../',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'roundcube/plugin-installer' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
        'shama/baton' => array(
            'dev_requirement' => false,
            'replaced' => array(
                0 => '*',
            ),
        ),
    ),
);
