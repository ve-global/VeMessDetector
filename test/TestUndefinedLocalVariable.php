<?php

class TestUndefinedLocalVariable
{
	public $whatever = [];

	public function unused($test)
	{
		isset($test);

		isset($adsf, $adsf);

		$adsf = 'what';

		foreach ($this->whatever as &$var)
		{
			isset($var);
		}

		foreach ($this->whatever as $var2)
		{
			isset($var2);
		}

		foreach ($var3 as $var4)
		{
			isset($var4);
		}

		isset($variable['test'], $variable['what']);

		try
		{

		}
		catch (Exception $exc)
		{
			echo $exc->getTraceAsString();
		}
	}
}
