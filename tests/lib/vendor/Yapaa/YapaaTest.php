<?php

/**
 * This file is part of the Yapaa library - Yet another PHP AOP approach
 *
 * Copyright (c) 2011 Tomáš Klapka (tomas@klapka.cz)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Yapaa;

require_once __DIR__ . '/test_data.php';
require_once __DIR__ . '/../../../../lib/vendor/Yapaa/Yapaa.php';
require_once __DIR__ . '/../../../../lib/vendor/Yapaa/Pointcut.php';
require_once __DIR__ . '/../../../../lib/vendor/Yapaa/JoinPoint.php';
require_once __DIR__ . '/../../../../lib/vendor/Yapaa/RunkitWeaver.php';

/**
 * Test class for Yapaa
 * Generated by PHPUnit on 2011-12-27 at 19:42:33.
 */
class YapaaTest extends \PHPUnit_Framework_TestCase {

    protected $testObj;
    protected $pointcut;

    protected function setUp() {
        $this->testObj = new \TestClass();
    }

    /**
     * @covers \Yapaa\Yapaa::Pointcut
     */
    public function testWeave() {
        $this->pointcut = \Yapaa\Yapaa::Pointcut(array(                 // create pointcut
                    'function(test_function)',                          // for function test_function()
                    'function(not_defined_function_yet)',               // for function not_defined_function_yet()
                    'method(TestClass,test_method)'                     // for test_method() from TestClass
                ));
        $this->pointcut
                ->addAdviceBefore('$return .= "before\n";')             // add before advice
                ->addAdviceAfter('$return .= "after\n";')               // add after advice
                ->addAdviceAround('$return .= "around before\n"; Yapaa::proceed(); $return .= "around after\n";')
                ->weave();                                              // add around advice and weave

        $method_expect = "before\naround before\n1around after\nafter\n";
        $function_expect = "before\naround before\n2around after\nafter\n";
        $this->assertEquals($method_expect, $this->testObj->test_method(1));
        $this->assertEquals($function_expect, test_function(2));
    }

    /**
     * @covers \Yapaa\Yapaa::weaveAllPointcuts
     */
    public function testWeaveAllPointcuts() {
        runkit_function_add('not_defined_function_yet', '$param', 'return $param;'); // add new function to be weaved
        $this->assertEquals("test\n", not_defined_function_yet("test\n"));           // is not weaved yet
        \Yapaa\Yapaa::weaveAllPointcuts();                                           // reweave all
        $function_expect = "before\naround before\ntest\naround after\nafter\n";
        $this->assertEquals($function_expect, not_defined_function_yet("test\n"));   // is weaved now
    }

}
