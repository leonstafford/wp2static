<?php

chdir( dirname(__FILE__) . '/../../plugin' );

$plugin_dir = getcwd();

require_once $plugin_dir . '/WP2Static/WP2Static.php';
require_once $plugin_dir . '/WP2Static/HTMLProcessor.php';
require_once $plugin_dir . '/URL2/URL2.php';

use PHPUnit\Framework\TestCase;

// Create basic helper for filters so code is testable
function apply_filters($name, $default) {
  return $default;
}

final class HTMLProcessorEntityPreservationTest extends TestCase {

    /**
     * @dataProvider entityProvider
     */
    public function testEntityPreservation(
        $test_HTML_content,
        $exp_result
        ) {

        $mockProcessor = $this->getMockBuilder( 'HTMLProcessor' )
            ->setMethods(
                [
                    'loadSettings',
                    'rewriteSiteURLsToPlaceholder',
                    'detectIfURLsShouldBeHarvested',
                    'writeDiscoveredURLs',
                ]
            )
            ->getMock();

        $page_URL = new Net_URL2(
            'http://mywpsite.com/category/photos/my-gallery/'
        );

        $mockProcessor->method( 'loadSettings' )->willReturn( null );

        $mockProcessor->method( 'rewriteSiteURLsToPlaceholder' )->willReturn(
            $test_HTML_content
        );

        $mockProcessor->method( 'detectIfURLsShouldBeHarvested' )->willReturn( null );
        $mockProcessor->method( 'writeDiscoveredURLs' )->willReturn( null );

        $mockProcessor->settings = array(
            'baseUrl' => 'http://baseurldomainfromsettings.com/',
        );

        $mockProcessor->processHTML( $test_HTML_content, $page_URL );

        $this->assertEquals(
            $exp_result,
            $mockProcessor->getHTML()
        );

    }

    public function entityProvider() {
        return [
           'HTML entities to be preserved in source' =>  [
                '<!DOCTYPE html><html lang="en-US"><head></head><body><p><code>/DescriptorPrefix/&lt;tableid&gt;</code>. Each row in the table is stored at key <code>/&lt;tableid&gt;/&lt;primarykey&gt;</code>.</p></body></html>',
                '<!DOCTYPE html>
<html lang="en-US"><head></head><body><p><code>/DescriptorPrefix/&lt;tableid&gt;</code>. Each row in the table is stored at key <code>/&lt;tableid&gt;/&lt;primarykey&gt;</code>.</p></body></html>
',
            ],
        ];
    }
}


