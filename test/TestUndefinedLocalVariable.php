<?php

class TestUndefinedLocalVariable
{
	public function unused($test)
	{
		isset($test);

		isset($adsf, $adsf);

		$adsf = 'what';

		isset($variable['test'], $variable['what']);
	}
}
