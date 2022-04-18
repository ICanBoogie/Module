<?php

namespace Test\ICanBoogie\Module\ModulesTest\ModuleD;

use ICanBoogie\Module\Descriptor;

return [

	Descriptor::TITLE => 'D',
	Descriptor::NS => __NAMESPACE__,
	Descriptor::REQUIRES => [ 'b' ]

];
