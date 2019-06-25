<?php
namespace Lara\Tests\Feature;

use Home\Lib\Foo;
use Lara\Tests\TestCase;

class MockTest extends TestCase {

    public function testFoo(){

        $stub = $this->createMock(Foo::class);

        // 配置桩件。
        $stub->method('say')
            ->willReturn(1);

        app()->instance(Foo::class, $stub);
        $content = $this->get('/home/index/mock');

        $this->assertTrue($content == 1);
    }

    public function testFoo1(){
        // 为 SomeClass 类创建桩件。
        $stub = $this->createMock(Foo::class);

        // 配置桩件。
        $stub->method('say')
            ->willReturn(1);

        // 现在调用 $stub->doSomething() 将返回 'foo'。
        $this->assertEquals(1, $stub->say());
    }
}