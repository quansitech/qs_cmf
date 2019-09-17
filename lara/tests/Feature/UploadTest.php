<?php
namespace Lara\Tests\Feature;

use Illuminate\Http\UploadedFile;
use Testing\TestCase;

class UploadTest extends TestCase {

    public function testUploadImage(){

        $data = [
            'file' => UploadedFile::fake()->image('test.jpg', 100, 100)
        ];

        $content = $this->post('api/upload/uploadImage', $data);

        $content = json_decode($content, true);
        $this->assertTrue($content['Status'] == 1);
        $this->assertDatabaseHas('qs_file_pic', [
            'title' => 'test.jpg',
            'file' => $content['file']
        ]);
        $this->assertFileExists($this->laraPath() . '/../www/Uploads/' . $content['file']);
    }
}