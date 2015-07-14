<?php

class TestUndefinedLocalVariable
{
	public $whatever = [];

	public function unused($test)
	{
		isset($test);

		isset($adsf, $adsf);

		$adsf = 'what';

		foreach ($this->whatever as $wth)
		{
			isset($wth);
		}

		isset($variable['test'], $variable['what']);
	}
}
