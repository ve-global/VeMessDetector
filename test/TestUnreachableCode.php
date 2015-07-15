<?php

class TestUnreachableCode
{

	public function error()
	{
		return true;

		echo 'madeup';
	}

	public function pass($variable)
	{
		if (true) {
			return true;
		} elseif (false) {
			return true;
		} else {
			return true;
		}

		switch ($variable)
		{
			case 1:
				return true;
			default:
				return true;
				break;
		}

		return [
			'false',
			true
		];
	}

	public function trycatch()
	{
		try
		{
			return true;
		}
		catch (Exception $ex)
		{
			return false;
		}
		finally
		{
			return true;
		}
		
		return true;
	}

	public function anonymous($record, $columnOrder)
	{
		uksort(
			$record,
			function($a, $b) use ($columnOrder) {
				if (true) {
					return false;
				}

				return array_search($a, $columnOrder) > array_search($b, $columnOrder);
			}
		);
	}
}
