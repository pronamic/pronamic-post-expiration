<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

return RectorConfig::configure()
	->withPaths(
		[
			__DIR__ . '/admin',
			__DIR__ . '/php',
		]
	)
	->withPhpSets()
	->withTypeCoverageLevel( 0 );
