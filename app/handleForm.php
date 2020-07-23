<?php

require __DIR__ . '/vendor/autoload.php';

use \CloudConvert\CloudConvert;
use \CloudConvert\Models\Job;
use \CloudConvert\Models\Task;

if(!isset($_POST['ebook_file'])|| !isset($_POST['user_email']))
{
    header('index.php');
}

function convertFile()
{
    $cloudconvert = new CloudConvert([
        'api_key' => file_get_contents('API_KEY.txt'),
        'sandbox' => false
    ]);

    $job = getJob();
    convert($cloudconvert, $job);
}

/**
 * @param CloudConvert $cloudconvert
 * @param Job $job
 */
function convert(CloudConvert $cloudconvert, Job $job)
{
    try {
        $cloudconvert->jobs()->create($job);
        $uploadTask = $job->getTasks()->name('import_epub')[0];
        move_uploaded_file($_FILES['ebook_file']['tmp_name'], '/tmp/input/' . $_FILES['ebook_file']['name']);
        $cloudconvert->tasks()->upload($uploadTask, fopen($_FILES['ebook_file']['name'], 'r'));
        $cloudconvert->jobs()->wait($job); // Wait for job completion

        foreach ($job->getExportUrls() as $file) {

            $source = $cloudconvert->getHttpTransport()->download($file->url)->detach();
            $dest = fopen('/tmp/output/' . $file->filename, 'w');

            stream_copy_to_stream($source, $dest);

        }
    } catch (Exception $e) {
        echo $e->getMessage();
    }
}

/**
 * @return Job
 */
function getJob()
{
    $job = (new Job())
        ->addTask(
            (new Task('import/upload', 'import_epub'))
        )
        ->addTask(
            (new Task('convert', 'convert_epub_to_mobi'))
                ->set('input_format', 'epub')
                ->set('output_format', 'mobi')
                ->set('engine', 'calibre')
                ->set('input', ["import_epub"])
        )
        ->addTask(
            (new Task('export/url', 'export_mobi'))
                ->set('input', ["convert_epub_to_mobi"])
        );
    return $job;
}

function sendFileToKindle()
{

}

convertFile();
sendFileToKindle();
