<?php

namespace Translator\Import;

use Mockery as m;
use Translator\String;

class ProcessTest extends \PHPUnit_Framework_TestCase
{
    public function testIteratesOverAllStringsRegisteringThem()
    {
        $storage = m::mock();
        $storage->shouldReceive('setTranslationValue')
            ->with(equalTo(String::create('yes', 'Yes')))
            ->once();
        $storage->shouldReceive('setTranslationValue')
            ->with(equalTo(String::create('validator:notEmpty', 'Should be not empty', 'Validation error messages')))
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
