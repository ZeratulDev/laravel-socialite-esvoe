<?php

namespace ZeroByteD\SocialiteEsvoe;

use Laravel\Socialite\Two\User;
use GuzzleHttp\Exception\GuzzleException;
use Laravel\Socialite\Two\AbstractProvider;

class SocialiteEsvoeProvider extends AbstractProvider
{
    /**
     * @var string[]
     */
    protected $scopes = [
        'openid',
        'profile',
        'aws.esvoe.signin.user.admin',
    ];

    /**
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * @return string
     */
    public function getEsvoeUrl()
    {
        return config('services.esvoe.base_uri') . '/oauth2';
    }

    /**
     * @param string $state
     *
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->getEsvoeUrl() . '/authorize', $state);
    }

    /**
     * @return string
     */
    protected function getTokenUrl()
    {
        return $this->getEsvoeUrl() . '/token';
    }

    /**
     * @param string $token
     *
     * @throws GuzzleException
     *
     * @return array|mixed
     */
    protected function getUserByToken($token)
    {
        $response = $this->getHttpClient()->post($this->getEsvoeUrl() . '/userInfo', [
            'headers' => [
                'cache-control' => 'no-cache',
                'Authorization' => 'Bearer ' . $token,
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        return json_decode($response->getBody()->getContents(), true);
    }

    /**
     * @return User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User())->setRaw($user)->map([
            'id' => $user['sub'],
            'email' => $user['email'],
            'username' => $user['username'],
            'email_verified' => $user['email_verified'],
            'family_name' => $user['family_name'],
        ]);
    }
}