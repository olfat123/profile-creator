<?php return array(
    'root' => array(
        'name' => 'olfat/profile-creator',
        'pretty_version' => 'dev-main',
        'version' => 'dev-main',
        'reference' => '84d4f2cae3aa1870c0a5c490dcdac06aecd3761d',
        'type' => 'wordpress-plugin',
        'install_path' => __DIR__ . '/../../',
        'aliases' => array(),
        'dev' => false,
    ),
    'versions' => array(
        'composer/installers' => array(
            'pretty_version' => 'v1.12.0',
            'version' => '1.12.0.0',
            'reference' => 'd20a64ed3c94748397ff5973488761b22f6d3f19',
            'type' => 'composer-plugin',
            'install_path' => __DIR__ . '/./installers',
            'aliases' => array(),
            'dev_requirement' => false,
        ),
        'olfat/profile-creator' => array(
            'pretty_version' => 'dev-main',
            'version' => 'dev-main',
            'reference' => '84d4f2cae3aa1870c0a5c490dcdac06aecd3761d',
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
