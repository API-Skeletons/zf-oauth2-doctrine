<?php

namespace ZFTest\OAuth2\Doctrine\Orm;

use OAuth2\Encryption\Jwt;
use DateTime;

class JwtAccessTokenTest extends AbstractTest
{
    /** @dataProvider provideStorage */
    public function testJwtWithJti($storage)
    {
        $expires = new DateTime('today +1 day');
        $expires = $expires->format('U');

        $client_id   = 'oauth_test_client';
        $subject = 'jtisubject';
        $audience = 'http://unittest';
        $jti = 'jti';

        $this->assertTrue($storage->setJti($client_id, $subject, $audience, $expires, $jti));

        $storage->getJti($client_id, $subject, $audience, $expires, $jti);

        $this->assertFalse($storage->getJti($client_id, $subject, $audience, $expires, 'invlalid'));

        $this->assertTrue($storage->setJti('event_stop_propagation', '', '', '', ''));
        $this->assertTrue($storage->getJti('event_stop_propagation', '', '', '', ''));
    }
}
