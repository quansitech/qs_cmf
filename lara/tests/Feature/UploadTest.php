<?php
namespace Lara\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Lara\Tests\TestCase;

class UploadTest extends TestCase {

    public function testUploadImage(){

        $data = [
            'file' => UploadedFile::fake()->image('test.jpg', 100, 100)
        ];

        $query = http_build_query([
            'cate' => 'image',
            'title' => 'test.jpg',
        ]);
        $content = $this->post('api/upload/uploadFile?'.$query, $data);

        $content = json_decode($content, true);
        $this->assertTrue($content['status'] == 1);
        $this->assertDatabaseHas('qs_file_pic', [
            'id' => $content['file_id']
        ]);
        $this->assertFileExists($this->laraPath() . '/../www/' . $content['url']);
    }
}