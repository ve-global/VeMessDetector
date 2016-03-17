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

    public function errorThrow()
    {
        throw new Exception('madeup');

        echo 'madeup';
    }

    public function passThrow($variable)
    {
        if (true) {
            throw new Exception('madeup');
        } elseif (false) {
            throw new Exception('madeup');
        } else {
            throw new Exception('madeup');
        }

        switch ($variable)
        {
            case 1:
                throw new Exception('madeup');
            default:
                throw new Exception('madeup');
        }

        throw new Exception(
            'madeup'
        );
    }

    public function trycatchThrow()
    {
        try
        {
            throw new Exception('madeup');
        }
        catch (Exception $ex)
        {
            throw new Exception('madeup');
        }
        finally
        {
            throw new Exception('madeup');
        }

        throw new Exception('madeup');
    }

    public function anonymousThrow($record)
    {
        uksort(
            $record,
            function() {
                if (true) {
                    throw new Exception('madeup');
                }

                throw new Exception('madeup');
            }
        );
    }
}
