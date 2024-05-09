<?php
namespace Lara\Tests\Feature;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Lara\Tests\TestCase;

class QueueTest extends TestCase {

    public function testJob(){
        $content = $this->runJob("\Common\Job\TestJob", "");
        $this->assertNull($content);
    }

    public function testQueueJob(){
        $id = Str::uuid()->getHex();
        DB::table('qs_queue')->insert([
            [
                'id' => $id,
                'job' => '\Common\Job\TestJob',
                'args' => '',
                'description' => 'test job desc',
                'status' => 0,
                'create_date' => 1715086302,
                'queue' => 'test',
            ]
        ]);
        $queue = (array)DB::table('qs_queue')->first();

        $content = $this->runJob($queue['job'], $queue['args']);

        $this->assertNull($content);

    }
}