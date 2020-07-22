<?php

use \CloudConvert\CloudConvert;
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;

if(!isset($_POST['ebook_file'])|| !isset($_POST['user_email']))
{
    header('index.php');
}
var_dump($_FILES['ebook_file']);

//$cloudconvert = new CloudConvert([
//    'api_key' => file_get_contents('API_KEY.txt'),
//    'sandbox' => false
//]);
//
//
//$job = (new Job())
//    ->addTask(
//        (new Task('import/url', 'import_epub'))
//            ->set('url', '/path/to/file')
//    )
//    ->addTask(
//        (new Task('convert', 'convert_epub_to_mobi'))
//            ->set('input_format', 'epub')
//            ->set('output_format', 'mobi')
//            ->set('engine', 'calibre')
//            ->set('input', ["import_epub"])
//    )
//    ->addTask(
//        (new Task('export/url', 'export_mobi'))
//            ->set('input', ["convert_epub_to_mobi"])
//            ->set('inline', 'false')
//            ->set('archive_multiple_files', 'false')
//    );
//$cloudconvert->jobs()->create($job);