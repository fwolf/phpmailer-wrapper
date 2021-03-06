<?php
namespace FwolfTest\Wrapper\PHPMailer;

use Fwolf\Wrapper\PHPMailer\PHPMailer;

/**
 * @copyright   Copyright 2013-2015 Fwolf
 * @license     http://opensource.org/licenses/MIT MIT
 */
class PHPMailerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @return \PHPUnit_Framework_MockObject_MockObject | PHPMailer
     */
    protected function buildMock()
    {
        /**
         * It can't mock parent::send() method, dig into parent::send() logic
         * and find it work by call preSend() and postSend(), so we mock these
         * 2 method to simulate parent::send() return true or false.
         */

        $mailer = $this->getMock(
            'Fwolf\Wrapper\PHPMailer\PHPMailer',
            ['preSend', 'postSend']
        );
        $mailer->expects($this->any())
            ->method('preSend')
            ->will($this->onConsecutiveCalls(true, false));
        $mailer->expects($this->any())
            ->method('postSend')
            ->will($this->returnValue(true));

        return $mailer;
    }


    public function testParseAddress()
    {
        $mailer = $this->buildMock();

        $x = 'A <a@a.com>; B <b@b.com>, 姓名<q@c.com>;';
        $y = [
            'a@a.com'   => 'A',
            'b@b.com'   => 'B',
            'q@c.com'   => '姓名',
        ];
        $this->assertEquals(
            var_export($y, true),
            var_export($mailer->parseAddress($x), true)
        );

        $x = 'a.a.com';
        $this->assertEmpty($mailer->parseAddress($x));
    }


    public function testSend()
    {
        $mailer = $this->buildMock();

        // Dummy setter
        $mailer->setCharset('utf-8');
        $mailer->setEncoding('base64');
        $mailer->setHost('smtp.domain.tld', 25);
        $mailer->setAuth('Username', 'Password');
        $mailer->setFrom('alien@domain.tld', 'Alien');
        $mailer->setTo('Somebody <address@domain.tld>');
        $mailer->setSubject('Hello from aliens');
        $mailer->setBody('This is only a test.');

        // No error counted
        $this->assertTrue($mailer->send());
        $this->assertEquals(0, $mailer->getErrorCount());
        $this->assertEmpty($mailer->getErrorMessage());

        // Error counter raised
        $this->assertFalse($mailer->send());
        $this->assertEquals(1, $mailer->getErrorCount());
    }
}
