<?php

use App\Domain\Installation\RequirementChecker;

test('requirements are typed and distinguish required from optional checks', function () {
    $requirements = (new RequirementChecker)->check();
    $names = array_map(fn ($result) => $result->name, $requirements);

    expect($names)->toContain('php', 'pdo_mysql', 'image', 'intl', 'Zend OPcache')
        ->and(collect($requirements)->firstWhere('name', 'intl')->required)->toBeFalse()
        ->and(collect($requirements)->firstWhere('name', 'image')->required)->toBeTrue();
});
