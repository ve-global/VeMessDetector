<?php

class TestUndefinedLocalVariable
{
	public $whatever = [];

	public function unused($test)
	{
		return function($functionVar1, $functionVar2) use ($test) {
			isset($functionVar3);
		};

		isset($functionVar3);

		isset($test);

		isset($functionVar1);

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
	}

	public function unused2()
	{
		list($page, $limit, $offset) = $this->getPaginationProperties();

		isset($page, $limit, $offset);

		try
		{

		}
		catch (Exception $exc)
		{
			echo $exc->getTraceAsString();
		}
	}
}
