<?php


namespace YumlPhp\Tests\Integration;


use Buzz\Browser;
use YumlPhp\Builder\BuilderInterface;
use YumlPhp\Builder\HttpBuilder;
use YumlPhp\Request\Http\ClassesRequest;
use YumlPhp\Request\Http\FileRequest;

class YumlApiTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider fileProvider
     */
    public function testYuml(BuilderInterface $builder, $fixture, $config)
    {
        $result = $builder
            ->configure($config)
            ->setPath($fixture)
            ->build();

        $this->assertInternalType('array', $result);
        $this->assertGreaterThan(0, count($result));

        $b = new Browser();
        foreach ($result as $message) {
            $url = explode(' ', $message);
            $response = $b->get($url[1]);
            $contentType = null;

            switch ($url[0]) {
                case '<info>PNG</info>' :
                    $contentType = 'image/png';
                    break;
                case '<info>PDF</info>' :
                    $contentType = 'application/pdf';
                    break;
                case '<info>URL</info>' :
                    $contentType = 'text/html; charset=utf-8';
                    break;
            }
            $this->assertEquals($contentType, $response->getHeader('Content-Type'));
        }
    }

    public function fileProvider()
    {
        return array(
            array(new HttpBuilder(new FileRequest(), new Browser(), 'activity'), __DIR__ . '/../YumlPhp/Tests/Fixtures/activity.txt', array(
                'url'   => 'http://yuml.me/diagram/plain;dir:TB/activity/',
                'debug' => false
            )),
            array(new HttpBuilder(new FileRequest(), new Browser(), 'usecase'), __DIR__ . '/../YumlPhp/Tests/Fixtures/use-case.txt',array(
                'url'   => 'http://yuml.me/diagram/plain;dir:TB/usecase/',
                'debug' => false
            )),
            array(new HttpBuilder(new ClassesRequest(), new Browser(), 'classes'), __DIR__ . '/../YumlPhp/Tests/Fixtures', array(
                'url'            => 'http://yuml.me/diagram/plain;dir:TB/class/',
                'debug' => false
            ))
        );
    }
}
 