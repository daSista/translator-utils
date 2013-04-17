<?php

namespace Translator\Import;

use Mockery as m;
use Translator\String;

class ProcessTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratesOverAllStringsRegisteringThem()
    {
        $storage = m::mock();
        $storage->shouldReceive('registerString')
            ->with(equalTo(new String('yes', 'Yes')))
            ->once();
        $storage->shouldReceive('registerString')
            ->with(equalTo(new String('notEmpty', 'Should be not empty', 'validator', 'Validation error messages')))
            ->once();

        $process = new Process($storage);
        $process->run(
            array(
                'yes' => array('Yes'),
                'validator:notEmpty' => array('Should be not empty', 'Validation error messages')
            )
        );
    }
}
