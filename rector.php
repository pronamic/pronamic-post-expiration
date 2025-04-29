<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withPaths(
		[
			__DIR__ . '/admin',
			__DIR__ . '/node_modules',
			__DIR__ . '/php',
		]
	)
	// uncomment to reach your current PHP version
	// ->withPhpSets()
	->withTypeCoverageLevel( 0 );
